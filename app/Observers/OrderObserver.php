<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Events\OrderStatusUpdated;
use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        OrderStatusUpdated::dispatch($order, 'created');
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged(['status', 'payment_method'])) {
            return;
        }

        $changeType = $order->status === OrderStatus::Paid ? 'paid' : 'status_changed';

        OrderStatusUpdated::dispatch(
            $order,
            $changeType,
            $order->getRawOriginal('status'),
        );
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
