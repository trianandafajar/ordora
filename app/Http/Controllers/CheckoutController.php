<?php

namespace App\Http\Controllers;

use App\Actions\CreateOrderAction;
use App\Enums\PaymentMethod;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CheckoutController extends Controller
{
    public function showMenu(string $qr_token)
    {
        $table = Table::where('qr_token', $qr_token)->firstOrFail();
        session(['table_id' => $table->id, 'table_qr' => $table->qr_token]);

        $categories = Category::with(['products' => fn($q) => $q->where('is_available', true)])->get();

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

        return back()->with('success', $product->name . ' added to cart.');
    }

    public function removeFromCart(Request $request, string $qr_token)
    {
        $productId = $request->route('product_id');
        $cart = collect(session('cart', []))->reject(fn($i) => $i['product_id'] == $productId)->values()->all();
        session(['cart' => $cart]);

        return back()->with('success', 'Item removed from cart.');
    }

    public function showCheckout()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        $tableQr = session('table_qr');
        if (! $tableQr) {
            return redirect()->route('login')->with('error', 'Please scan the QR code first.');
        }

        return view('customer.checkout', compact('cart'));
    }

    public function storeCheckout(Request $request, CreateOrderAction $action)
    {
        $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'in:cash,qris'],
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty.');
        }

        $tableId = session('table_id');
        if (! $tableId) {
            return redirect()->route('login')->with('error', 'Please scan the QR code first.');
        }

        $order = $action->execute(
            $cart,
            $request->customer_name,
            $tableId,
            PaymentMethod::from($request->payment_method),
        );
        session()->forget('cart');

        return redirect()->route('table.tracking.show', $order->order_token)->with('order_id', $order->id);
    }

    public function placeOrder(Request $request, string $qr_token, CreateOrderAction $action): JsonResponse
    {
        $table = Table::where('qr_token', $qr_token)->firstOrFail();
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'in:cash,qris'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = array_map(fn($item) => [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
        ], $validated['items']);

        $order = $action->execute($cart, $validated['customer_name'], $table->id, PaymentMethod::from($validated['payment_method']));

        return response()->json([
            'order_id' => $order->id,
            'order_token' => $order->order_token,
        ]);
    }

    public function showTracking(string $order_token)
    {
        $order = Order::with(['orderItems.product', 'table'])
            ->where('order_token', $order_token)
            ->firstOrFail();

        return view('customer.tracking', compact('order'));
    }

    public function showOrderDetail(string $order_token)
    {
        $order = Order::with(['orderItems.product', 'table'])
            ->where('order_token', $order_token)
            ->firstOrFail();

        return view('customer.order-detail', compact('order'));
    }

    public function downloadReceipt(string $order_token)
    {
        $order = Order::with(['orderItems.product', 'table'])
            ->where('order_token', $order_token)
            ->firstOrFail();

        $pdf = Pdf::loadView('customer.order-receipt', compact('order'));

        return $pdf->download('Receipt-Order-#' . $order->id . '.pdf');
    }

    public function qrisQr(string $order_token)
    {
        $order = Order::where('order_token', $order_token)->firstOrFail();

        // Dummy QRIS EMVCo structure
        // 00: Payload Format Indicator
        // 01: Point of Initiation Method (11: Static, 12: Dynamic)
        // 26: Merchant Account Information
        // 52: Merchant Category Code
        // 53: Transaction Currency (360: IDR)
        // 54: Transaction Amount
        // 58: Country Code (ID)
        // 59: Merchant Name
        // 60: Merchant City
        // 63: CRC
        $data = '000201010212265000012ID.CO.ORDORA.WWW0118936000000000000002520458415303360';
        $amount = number_format($order->total_price, 2, '.', '');
        $data .= '54' . sprintf('%02d', strlen($amount)) . $amount;
        $data .= '5802ID5906ORDORA6005ADMIN6304';
        $data .= $this->crc16($data);

        return response(QrCode::format('png')->size(300)->generate($data))
            ->header('Content-Type', 'image/png');
    }

    private function crc16(string $data): string
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }

        return strtoupper(sprintf('%04x', $crc));
    }

    public function history()
    {
        return view('customer.history');
    }
}
