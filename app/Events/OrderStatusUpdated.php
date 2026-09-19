<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public string $changeType = 'status_changed',
        public ?string $previousStatus = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('order.'.$this->order->order_token),
            new PrivateChannel('kasir-orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    /**
     * Return the small, public-safe payload consumed by the staff board and customer tracker.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $order = $this->order->fresh(['orderItems.product', 'table']);

        return [
            'change_type' => $this->changeType,
            'previous_status' => $this->previousStatus,
            'order' => [
                'id' => $order->id,
                'order_token' => $order->order_token,
                'customer_name' => $order->customer_name,
                'table_number' => $order->table?->number,
                'status' => $order->status->value,
                'paid_at' => $order->paid_at?->toIso8601String(),
                'payment_method' => $order->payment_method?->value,
                'total_price' => $order->total_price,
                'created_at' => $order->created_at?->toIso8601String(),
                'items' => $order->orderItems->map(fn ($item): array => [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ])->values()->all(),
            ],
        ];
    }
}
