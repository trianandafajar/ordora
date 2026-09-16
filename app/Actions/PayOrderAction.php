<?php

namespace App\Actions;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\OrderStatusHistory;
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

            if ($lockedOrder->paid_at !== null) {
                throw ValidationException::withMessages([
                    'payment' => 'This order has already been paid.',
                ]);
            }

            $lockedOrder->update([
                'payment_method' => $method,
                'user_id' => $processedBy,
                'paid_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'status' => 'paid',
                'changed_by' => $processedBy,
            ]);

            return $lockedOrder->fresh(['orderItems.product', 'table', 'user']);
        });
    }
}
