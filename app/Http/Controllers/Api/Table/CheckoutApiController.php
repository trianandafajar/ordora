<?php

namespace App\Http\Controllers\Api\Table;

use App\Actions\CreateOrderAction;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\OrderResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CheckoutApiController extends Controller
{
    public function showMenu(string $qr_token)
    {
        $table = Table::where('qr_token', $qr_token)->firstOrFail();
        $categories = Category::with(['products' => fn ($q) => $q->where('is_available', true)])
            ->withCount(['products' => fn ($q) => $q->where('is_available', true)])
            ->get();

        return response()->json([
            'table' => $table,
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    public function placeOrder(Request $request, string $qr_token, CreateOrderAction $action)
    {
        $table = Table::where('qr_token', $qr_token)->firstOrFail();
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'in:cash,qris'],
            'items' => ['required', 'array', 'min:1'],
        ]);

        $order = $action->execute($validated['items'], $validated['customer_name'], $table->id, PaymentMethod::from($validated['payment_method']));

        return response()->json(['order_token' => $order->order_token], 201);
    }

    public function showTracking(string $order_token)
    {
        return new OrderResource(Order::with(['orderItems.product', 'table'])
            ->where('order_token', $order_token)
            ->firstOrFail());
    }

    public function cashQr(string $order_token)
    {
        $order = Order::where('order_token', $order_token)->firstOrFail();

        return response(QrCode::format('png')->size(300)->generate($order->order_token))
            ->header('Content-Type', 'image/png');
    }
}
