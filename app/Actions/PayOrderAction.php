<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayOrderAction
{
    public function execute(Order $order, PaymentMethod $method, int $processedBy): Order
    {
        return DB::transaction(function () use ($order, $method, $processedBy): Order {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($lockedOrder->status === OrderStatus::Paid) {
                throw ValidationException::withMessages([
                    'payment' => 'This order has already been paid.',
                ]);
            }

            if ($lockedOrder->status !== OrderStatus::Served) {
                throw ValidationException::withMessages([
                    'payment' => 'An order can only be paid after it reaches served status.',
                ]);
            }

            $table = Table::query()
                ->lockForUpdate()
                ->findOrFail($lockedOrder->table_id);

            $lockedOrder->update([
                'status' => OrderStatus::Paid,
                'payment_method' => $method,
                'user_id' => $processedBy,
            ]);

            $table->update(['status' => TableStatus::Available]);

            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'status' => OrderStatus::Paid->value,
                'changed_by' => $processedBy,
            ]);

            return $lockedOrder->fresh(['orderItems.product', 'table', 'user']);
        });
    }
}
