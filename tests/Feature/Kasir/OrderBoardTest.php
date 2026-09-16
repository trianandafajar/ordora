<?php

namespace Tests\Feature\Kasir;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class OrderBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_sees_active_orders_and_paid_orders_in_history(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $this->makeOrder('Active customer');
        $paid = $this->makeOrder('Paid customer');
        $paid->update(['status' => OrderStatus::Served, 'payment_method' => PaymentMethod::Cash, 'paid_at' => now()]);

        Livewire::actingAs($cashier)->test('kasir.order-board')
            ->assertSee('Active customer')
            ->assertDontSee('Paid customer')
            ->set('activeTab', 'history')
            ->assertSee('Paid customer')
            ->assertDontSee('Active customer');
    }

    public function test_search_and_status_filter_limit_the_order_board(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $pending = $this->makeOrder('Searchable customer');
        $preparing = $this->makeOrder('Other customer');
        $preparing->update(['status' => OrderStatus::Preparing]);

        Livewire::actingAs($cashier)->test('kasir.order-board')
            ->set('search', 'Searchable')
            ->assertSee('#'.$pending->id)
            ->assertDontSee('#'.$preparing->id)
            ->set('search', '')
            ->set('statusFilter', 'preparing')
            ->assertSee('#'.$preparing->id)
            ->assertDontSee('#'.$pending->id);
    }

    public function test_cashier_must_confirm_cash_payment_before_order_is_paid(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $order = $this->makeOrder();

        $component = Livewire::actingAs($cashier)->test('kasir.order-board')
            ->assertSee('Confirm Payment')
            ->assertDontSee('Bayar cash')
            ->assertDontSee('Bayar QRIS');

        $component
            ->call('openPaymentDialog', $order->id, PaymentMethod::Cash->value)
            ->assertSet('paymentDialogOpen', true)
            ->assertSet('paymentMethod', PaymentMethod::Cash->value)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Pending->value]);

        $component
            ->call('continuePayment')
            ->assertSet('paymentStep', 'confirmation')
            ->call('confirmPayment')
            ->assertSet('receiptDialogOpen', true)
            ->assertSet('receiptData.payment_method', PaymentMethod::Cash->value)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Pending->value, 'payment_method' => 'cash']);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertSame(TableStatus::Occupied, $order->table->fresh()->status);
    }

    public function test_cashier_must_confirm_qris_payment_before_order_is_paid(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $order = $this->makeOrder();

        $component = Livewire::actingAs($cashier)->test('kasir.order-board')
            ->call('openPaymentDialog', $order->id, PaymentMethod::Qris->value)
            ->call('continuePayment')
            ->call('confirmPayment')
            ->assertHasErrors('qrisConfirmed');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Pending->value,
            'payment_method' => null,
        ]);

        $component
            ->set('qrisConfirmed', true)
            ->call('confirmPayment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Pending->value,
            'payment_method' => PaymentMethod::Qris->value,
            'user_id' => $cashier->id,
        ]);
        $this->assertSame(TableStatus::Occupied, $order->table->fresh()->status);
    }

    public function test_canceling_payment_dialog_keeps_pending_order_unpaid(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $order = $this->makeOrder();

        Livewire::actingAs($cashier)->test('kasir.order-board')
            ->call('openPaymentDialog', $order->id, PaymentMethod::Cash->value)
            ->call('cancelPayment')
            ->assertSet('paymentDialogOpen', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Pending->value,
            'payment_method' => null,
        ]);
    }

    public function test_cashier_can_move_an_active_order_through_the_kitchen(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $cashier = User::factory()->create(['role' => UserRole::Kasir]);
        $order = $this->makeOrder();
        $order->update(['payment_method' => PaymentMethod::Cash, 'paid_at' => now()]);

        Livewire::actingAs($cashier)->test('kasir.order-board')
            ->call('moveOrder', $order->id, 'preparing')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'preparing']);
    }

    public function test_non_cashier_cannot_move_an_order(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = $this->makeOrder();

        Livewire::actingAs($admin)->test('kasir.order-board')
            ->call('moveOrder', $order->id, 'preparing')
            ->assertForbidden();
    }

    public function test_observer_dispatches_one_safe_event_for_a_relevant_change(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $order = $this->makeOrder();
        $order->update(['customer_name' => 'Updated name']);

        Event::assertDispatchedTimes(OrderStatusUpdated::class, 1);
        $captured = null;
        Event::assertDispatched(OrderStatusUpdated::class, function (OrderStatusUpdated $event) use (&$captured): bool {
            $captured = $event;

            return $event->changeType === 'created';
        });

        $payload = $captured->broadcastWith();
        $this->assertSame(['change_type', 'previous_status', 'order'], array_keys($payload));
        $this->assertSame(['id', 'order_token', 'customer_name', 'table_number', 'status', 'payment_method', 'total_price', 'created_at', 'items'], array_keys($payload['order']));
    }

    private function makeOrder(string $customerName = 'Test customer'): Order
    {
        Product::factory()->create(['name' => 'Nasi goreng']);
        $order = Order::factory()->create(['customer_name' => $customerName]);
        $order->table->update(['status' => TableStatus::Occupied]);

        return $order->fresh(['orderItems.product', 'table']);
    }
}
