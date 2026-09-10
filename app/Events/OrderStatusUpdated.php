<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load(['orderItems.product', 'table', 'user']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.'.$this->order->order_token),
            new PrivateChannel('kasir-orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }
}
