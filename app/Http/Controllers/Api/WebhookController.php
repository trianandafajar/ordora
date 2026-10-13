<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $token = $request->header('x-callback-token');
        if ($token !== config('services.xendit.callback_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->all();
        if ($data['status'] === 'PAID') {
            $orderToken = str_replace('ordora_', '', $data['external_id']);
            $order = Order::where('order_token', $orderToken)->first();

            if ($order && !$order->paid_at) {
                $order->update([
                    'status' => OrderStatus::Paid,
                    'paid_at' => now(),
                ]);

                OrderStatusUpdated::dispatch($order, 'payment_received');
            }
        }

        return response()->json(['message' => 'OK']);
    }
}
