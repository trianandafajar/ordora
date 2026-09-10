<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\TableStatus;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\OrderStatusHistory;

class PayOrderAction
{
    public function execute(Order $order, PaymentMethod $method, int $processedBy): void
    {
        $order->update([
            'status' => OrderStatus::Paid,
            'payment_method' => $method,
            'user_id' => $processedBy,
        ]);

        $order->table->update(['status' => TableStatus::Available]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'paid',
            'changed_by' => $processedBy,
        ]);

        event(new OrderStatusUpdated($order->fresh()));
    }
}
