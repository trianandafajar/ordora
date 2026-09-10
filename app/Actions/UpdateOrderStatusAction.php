<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\OrderStatusHistory;

class UpdateOrderStatusAction
{
    public function execute(Order $order, OrderStatus $status, ?int $changedBy = null): void
    {
        $order->update(['status' => $status]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $status->value,
            'changed_by' => $changedBy,
        ]);

        event(new OrderStatusUpdated($order->fresh()));
    }
}
