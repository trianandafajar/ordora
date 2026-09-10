<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('order.{orderToken}', function ($user, $orderToken) {
    // Customers without auth can track their order by token
    return Order::where('order_token', $orderToken)->exists();
});

Broadcast::channel('kasir-orders', function ($user) {
    return $user && ($user->role->value === 'admin' || $user->role->value === 'kasir');
});
