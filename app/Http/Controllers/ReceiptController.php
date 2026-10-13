<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function show(Order $order): View
    {
        $order->load(['orderItems.product', 'table']);

        return view('receipt', compact('order'));
    }
}
