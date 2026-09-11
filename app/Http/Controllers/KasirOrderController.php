<?php

namespace App\Http\Controllers;

use App\Actions\PayOrderAction;
use App\Actions\UpdateOrderStatusAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use Illuminate\Http\Request;

class KasirOrderController extends Controller
{
    public function show(Order $order)
    {
        $order->load(['orderItems.product', 'table', 'orderStatusHistories.changedBy']);

        return view('kasir.order.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order, UpdateOrderStatusAction $action)
    {
        $request->validate([
            'status' => ['required', 'in:preparing,ready,served'],
        ]);

        $status = OrderStatus::from($request->status);
        $action->execute($order, $status, auth()->id());

        return back()->with('success', "Order status updated to {$status->value}.");
    }

    public function pay(Request $request, Order $order, PayOrderAction $action)
    {
        $request->validate([
            'payment_method' => ['required', 'in:cash,qris'],
        ]);

        $method = PaymentMethod::from($request->payment_method);
        $action->execute($order, $method, auth()->id());

        return back()->with('success', 'Payment processed. Table is now available.');
    }
}
