<?php

namespace Database\Seeders;

use App\Actions\CreateOrderAction;
use App\Actions\PayOrderAction;
use App\Actions\UpdateOrderStatusAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DevelopmentOrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()->orderBy('id')->limit(10)->get();
        $kasirId = User::query()->where('role', 'kasir')->value('id');

        if ($products->count() < 10 || $kasirId === null) {
            throw new RuntimeException('Development orders require at least 10 products and one kasir user.');
        }

        $createOrderAction = new CreateOrderAction;
        $payOrderAction = new PayOrderAction;
        $updateOrderStatusAction = new UpdateOrderStatusAction;

        $orders = [
            [
                'customer_name' => 'DEV - Budi Santoso',
                'table_id' => 1,
                'items' => [
                    ['product_id' => $products[0]->id, 'quantity' => 2],
                    ['product_id' => $products[1]->id, 'quantity' => 1],
                ],
                'status' => OrderStatus::Pending,
            ],
            [
                'customer_name' => 'DEV - Sari Dewi',
                'table_id' => 2,
                'items' => [
                    ['product_id' => $products[2]->id, 'quantity' => 1],
                    ['product_id' => $products[3]->id, 'quantity' => 1],
                ],
                'status' => OrderStatus::Preparing,
            ],
            [
                'customer_name' => 'DEV - Andi Wijaya',
                'table_id' => 3,
                'items' => [
                    ['product_id' => $products[4]->id, 'quantity' => 2],
                ],
                'status' => OrderStatus::Ready,
            ],
            [
                'customer_name' => 'DEV - Rina Putri',
                'table_id' => 4,
                'items' => [
                    ['product_id' => $products[5]->id, 'quantity' => 1],
                    ['product_id' => $products[6]->id, 'quantity' => 2],
                ],
                'status' => OrderStatus::Served,
            ],
            [
                'customer_name' => 'DEV - Dimas Pratama',
                'table_id' => 5,
                'items' => [
                    ['product_id' => $products[7]->id, 'quantity' => 1],
                    ['product_id' => $products[8]->id, 'quantity' => 1],
                    ['product_id' => $products[9]->id, 'quantity' => 1],
                ],
                'status' => OrderStatus::Paid,
                'payment_method' => PaymentMethod::Cash,
            ],
        ];

        foreach ($orders as $orderData) {
            if (Order::query()
                ->where('customer_name', $orderData['customer_name'])
                ->where('table_id', $orderData['table_id'])
                ->exists()) {
                continue;
            }

            DB::transaction(function () use (
                $createOrderAction,
                $payOrderAction,
                $updateOrderStatusAction,
                $kasirId,
                $orderData,
            ): void {
                $order = $createOrderAction->execute(
                    $orderData['items'],
                    $orderData['customer_name'],
                    $orderData['table_id'],
                );

                if ($orderData['status'] === OrderStatus::Paid) {
                    $updateOrderStatusAction->execute($order, OrderStatus::Served, $kasirId);
                    $payOrderAction->execute($order, $orderData['payment_method'], $kasirId);

                    return;
                }

                if ($orderData['status'] !== OrderStatus::Pending) {
                    $updateOrderStatusAction->execute($order, $orderData['status'], $kasirId);
                }
            });
        }
    }
}
