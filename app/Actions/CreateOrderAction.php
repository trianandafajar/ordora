<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrderAction
{
    public function execute(array $items, string $customerName, int $tableId, PaymentMethod $paymentMethod): Order
    {
        return DB::transaction(function () use ($items, $customerName, $tableId, $paymentMethod): Order {
            $table = Table::lockForUpdate()->findOrFail($tableId);

            $order = Order::create([
                'table_id' => $tableId,
                'user_id' => null,
                'order_token' => Str::random(32),
                'customer_name' => $customerName,
                'total_price' => 0,
                'status' => OrderStatus::Pending,
                'payment_method' => $paymentMethod,
            ]);

            $subtotal = 0;
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $price = $product->price;
                $qty = $item['quantity'];
                $line = $price * $qty;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $line,
                ]);
                $subtotal += $line;
            }

            $order->update(['total_price' => $subtotal]);
            $table->update(['status' => TableStatus::Occupied]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'changed_by' => null,
            ]);

            return $order->load(['orderItems.product', 'table']);
        });
    }
}
