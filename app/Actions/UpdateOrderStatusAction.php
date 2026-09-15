<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Table;

class UpdateOrderStatusAction
{
    public function execute(Order $order, OrderStatus $status, ?int $changedBy = null): void
    {
        $order->update(['status' => $status]);

        if ($status === OrderStatus::Served) {
            Table::query()
                ->whereKey($order->table_id)
                ->update(['status' => TableStatus::Available]);
        }

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $status->value,
            'changed_by' => $changedBy,
        ]);

    }
}
