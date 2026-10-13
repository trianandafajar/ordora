<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $token = $request->header('x-callback-token');
        if ($token !== config('services.xendit.callback_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $status = $request->input('status');
        $externalId = (string) $request->input('external_id', '');

        if (! in_array($status, ['PAID', 'SETTLED'], true)) {
            return response()->json(['message' => 'OK']);
        }

        $orderToken = str_replace('ordora_', '', $externalId);
        $paidOrder = null;

        DB::transaction(function () use ($orderToken, &$paidOrder): void {
            $order = Order::where('order_token', $orderToken)
                ->lockForUpdate()
                ->first();

            if (! $order || $order->paid_at !== null) {
                return;
            }

            $order->update([
                'status' => OrderStatus::Pending,
                'paid_at' => now(),
                'payment_method' => PaymentMethod::Xendit,
            ]);

            $paidOrder = $order;
        });

        if ($paidOrder) {
            OrderStatusUpdated::dispatch($paidOrder->fresh(), 'payment_received');
        }

        return response()->json(['message' => 'OK']);
    }
}