<?php

namespace App\Http\Controllers;

use App\Actions\CreateOrderAction;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function showMenu(Table $table)
    {
        session(['table_id' => $table->id, 'table_qr' => $table->qr_token]);

        $categories = Category::with(['products' => fn ($q) => $q->where('is_available', true)])->get();

        return view('customer.menu', compact('table', 'categories'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::where('is_available', true)->find($request->product_id);
        if (! $product) {
            return back()->with('error', 'Product not available.');
        }

        $cart = session('cart', []);
        $existing = collect($cart)->firstWhere('product_id', $product->id);
        if ($existing) {
            $cart = collect($cart)->map(function ($item) use ($product, $request) {
                if ($item['product_id'] === $product->id) {
                    $item['quantity'] += $request->quantity;
                }

                return $item;
            })->all();
        } else {
            $cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $request->quantity,
            ];
        }

        session(['cart' => $cart]);

        return back()->with('success', $product->name.' added to cart.');
    }

    public function removeFromCart(Request $request, string $qr_token)
    {
        $productId = $request->route('product_id');
        $cart = collect(session('cart', []))->reject(fn ($i) => $i['product_id'] == $productId)->values()->all();
        session(['cart' => $cart]);

        return back()->with('success', 'Item removed from cart.');
    }

    public function showCheckout()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $tableId = session('table_id');
        if (! $tableId) {
            return redirect('/')->with('error', 'Please scan the QR code first.');
        }

        return view('customer.checkout', compact('cart'));
    }

    public function storeCheckout(Request $request, CreateOrderAction $action)
    {
        $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty.');
        }

        $tableId = session('table_id');
        if (! $tableId) {
            return redirect('/')->with('error', 'Please scan the QR code first.');
        }

        $order = $action->execute($cart, $request->customer_name, $tableId);
        session()->forget('cart');

        return redirect()->route('meja.tracking.show', $order->order_token);
    }

    public function showTracking(string $order_token)
    {
        $order = Order::with(['orderItems.product', 'table'])
            ->where('order_token', $order_token)
            ->firstOrFail();

        return view('customer.tracking', compact('order'));
    }
}
