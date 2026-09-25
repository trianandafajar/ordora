<?php

namespace App\Http\Controllers\Api\Kasir;

use App\Actions\PayOrderAction;
use App\Actions\UpdateOrderStatusAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function show(Order $order)
    {
        return new OrderResource($order->load(['orderItems.product', 'table', 'orderStatusHistories.changedBy']));
    }

    public function updateStatus(Request $request, Order $order, UpdateOrderStatusAction $action)
    {
        $request->validate(['status' => ['required', 'in:preparing,ready,served']]);

        $oldStatus = $order->status->value;
        $status = OrderStatus::from($request->status);
        $action->execute($order, $status, auth()->id());

        OrderStatusUpdated::dispatch($order, 'status_changed', $oldStatus);

        return response()->json(['message' => "Order status updated to {$status->value}."]);
    }

    public function pay(ProcessPaymentRequest $request, Order $order, PayOrderAction $action)
    {
        $method = PaymentMethod::from($request->validated()['payment_method']);
        $paidOrder = $action->execute($order, $method, auth()->id());

        return response()->json([
            'message' => 'Payment processed.',
            'data' => new OrderResource($paidOrder),
        ]);
    }
}
