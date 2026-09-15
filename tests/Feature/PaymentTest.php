<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_payment_completes_the_order_and_redirects_to_receipt(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $order = $this->makeOrder(OrderStatus::Pending);

        $response = $this->actingAs($cashier)->post(route('cashier.order.pay', $order), [
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('cashier.order.receipt', $order));
        $this->assertPaymentCompleted($order, $cashier, PaymentMethod::Cash);
    }

    public function test_qris_payment_completes_the_order(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $order = $this->makeOrder(OrderStatus::Pending);

        $this->actingAs($cashier)->post(route('cashier.order.pay', $order), [
            'payment_method' => 'qris',
        ])->assertRedirect(route('cashier.order.receipt', $order));

        $this->assertPaymentCompleted($order, $cashier, PaymentMethod::Qris);
    }

    public function test_payment_is_rejected_for_an_order_that_is_not_pending(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);

        foreach ([OrderStatus::Preparing, OrderStatus::Ready, OrderStatus::Served] as $status) {
            $order = $this->makeOrder($status);

            $response = $this->actingAs($cashier)->post(route('cashier.order.pay', $order), [
                'payment_method' => 'cash',
            ]);

            $response->assertSessionHasErrors('payment');
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => $status->value,
                'payment_method' => null,
            ]);
        }
    }

    public function test_paid_order_cannot_be_paid_twice(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $order = $this->makeOrder(OrderStatus::Pending);

        $this->actingAs($cashier)->post(route('cashier.order.pay', $order), [
            'payment_method' => 'cash',
        ])->assertRedirect(route('cashier.order.receipt', $order));

        $response = $this->actingAs($cashier)->post(route('cashier.order.pay', $order), [
            'payment_method' => 'qris',
        ]);

        $response->assertSessionHasErrors('payment');
        $this->assertSame(1, OrderStatusHistory::query()
            ->where('order_id', $order->id)
            ->where('status', OrderStatus::Paid->value)
            ->count());
    }

    public function test_payment_method_is_validated(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $order = $this->makeOrder(OrderStatus::Pending);

        $response = $this->actingAs($cashier)->post(route('cashier.order.pay', $order), [
            'payment_method' => 'card',
        ]);

        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Pending->value,
            'payment_method' => null,
            'paid_at' => null,
        ]);
    }

    public function test_only_a_cashier_can_process_payment(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = $this->makeOrder(OrderStatus::Pending);

        $this->actingAs($admin)->post(route('cashier.order.pay', $order), [
            'payment_method' => 'cash',
        ])->assertForbidden();

        $this->post(route('logout'));

        $this->post(route('cashier.order.pay', $order), [
            'payment_method' => 'cash',
        ])->assertRedirectToRoute('login');
    }

    public function test_paid_receipt_is_available_but_unpaid_receipt_is_not(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $unpaidOrder = $this->makeOrder(OrderStatus::Pending);

        $this->actingAs($cashier)
            ->get(route('cashier.order.receipt', $unpaidOrder))
            ->assertNotFound();

        $this->actingAs($cashier)->post(route('cashier.order.pay', $unpaidOrder), [
            'payment_method' => 'cash',
        ]);

        $this->actingAs($cashier)
            ->get(route('cashier.order.receipt', $unpaidOrder))
            ->assertOk()
            ->assertSee('Payment Receipt')
            ->assertSee($unpaidOrder->customer_name);
    }

    public function test_pending_order_renders_payment_confirmation_dialog(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $order = $this->makeOrder(OrderStatus::Pending);

        $this->actingAs($cashier)
            ->get(route('cashier.order.show', $order))
            ->assertOk()
            ->assertSee('payment-confirmation-dialog')
            ->assertSee('Confirm payment')
            ->assertSee('I have received and manually verified the QRIS payment.');
    }

    public function test_payment_broadcast_contains_paid_payload_once(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $order = $this->makeOrder(OrderStatus::Pending);
        Event::fake([OrderStatusUpdated::class]);

        $this->actingAs($cashier)->post(route('cashier.order.pay', $order), [
            'payment_method' => 'qris',
        ]);

        Event::assertDispatchedTimes(OrderStatusUpdated::class, 1);
        Event::assertDispatched(OrderStatusUpdated::class, function (OrderStatusUpdated $event) use ($order): bool {
            $payload = $event->broadcastWith();

            return $event->changeType === 'paid'
                && $event->previousStatus === OrderStatus::Pending->value
                && $payload['order']['id'] === $order->id
                && $payload['order']['status'] === OrderStatus::Paid->value
                && $payload['order']['payment_method'] === PaymentMethod::Qris->value;
        });
    }

    private function makeOrder(OrderStatus $status): Order
    {
        Product::factory()->create([
            'name' => 'Payment test product',
            'price' => 25000,
        ]);

        $order = Order::factory()->create(['status' => $status]);
        $order->table->update(['status' => TableStatus::Occupied]);

        return $order->fresh(['orderItems.product', 'table']);
    }

    private function assertPaymentCompleted(Order $order, User $cashier, PaymentMethod $method): void
    {
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Paid->value,
            'payment_method' => $method->value,
            'user_id' => $cashier->id,
        ]);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => OrderStatus::Paid->value,
            'changed_by' => $cashier->id,
        ]);
        $this->assertSame(TableStatus::Occupied, $order->table->fresh()->status);
    }
}
