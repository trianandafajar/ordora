<?php

namespace App\Http\Controllers;

use App\Actions\PayOrderAction;
use App\Actions\UpdateOrderStatusAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KasirOrderController extends Controller
{
    public function show(Order $order): View
    {
        $order->load(['orderItems.product', 'table', 'orderStatusHistories.changedBy']);

        return view('kasir.order.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order, UpdateOrderStatusAction $action): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:preparing,ready,served'],
        ]);

        $status = OrderStatus::from($request->status);
        $action->execute($order, $status, auth()->id());

        return back()->with('success', "Order status updated to {$status->value}.");
    }

    public function pay(ProcessPaymentRequest $request, Order $order, PayOrderAction $action): RedirectResponse
    {
        $method = PaymentMethod::from($request->validated()['payment_method']);
        $paidOrder = $action->execute($order, $method, auth()->id());

        return redirect()
            ->route('cashier.order.receipt', $paidOrder)
            ->with('success', 'Payment processed. Table is now available.');
    }

    public function receipt(Order $order): View
    {
        abort_unless($order->paid_at !== null, 404);

        $order->load(['orderItems.product', 'table', 'user']);

        return view('kasir.order.receipt', compact('order'));
    }
}
