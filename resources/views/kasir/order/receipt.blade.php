<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt #{{ $order->id }} — Ordora</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-muted/30 px-4 py-8 text-foreground">
    @php($paymentLabel = $order->payment_method?->value === 'qris' ? 'QRIS (manual confirmation)' : 'Cash')

    <main class="mx-auto max-w-lg rounded-2xl border bg-card p-6 shadow-sm print:border-0 print:shadow-none">
        @if(session('success'))
        <div class="no-print mb-4 rounded-md border border-green-300 bg-green-50 p-3 text-sm text-green-800">{{
            session('success') }}</div>
        @endif

        <div class="mb-6 flex items-start justify-between gap-4 border-b pb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Ordora</p>
                <h1 class="mt-1 text-2xl font-bold">Payment Receipt</h1>
            </div>
            <a href="{{ route('kasir.order.show', $order) }}"
                class="no-print text-sm text-muted-foreground hover:text-foreground">Back</a>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-3 text-sm">
            <div>
                <p class="text-xs text-muted-foreground">Order</p>
                <p class="font-semibold">#{{ $order->id }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-muted-foreground">Paid at</p>
                <p class="font-semibold">{{ $order->updated_at?->format('d M Y, H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-muted-foreground">Customer</p>
                <p class="font-medium">{{ $order->customer_name }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-muted-foreground">Table</p>
                <p class="font-medium">{{ $order->table?->number ?? '-' }}</p>
            </div>
        </div>

        <div class="mb-6 border-y py-4">
            <div class="mb-3 flex justify-between text-xs uppercase tracking-wide text-muted-foreground">
                <span>Items</span>
                <span>Subtotal</span>
            </div>
            <div class="space-y-3">
                @foreach($order->orderItems as $item)
                <div class="flex justify-between gap-4 text-sm">
                    <span>{{ $item->quantity }} × {{ $item->product->name }}</span>
                    <span class="shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-muted-foreground">Payment method</span>
                <span class="font-medium">{{ $paymentLabel }}</span>
            </div>
            <div class="flex justify-between border-t pt-3 text-base font-bold">
                <span>Total</span>
                <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="mt-6 border-t pt-4 text-xs text-muted-foreground">
            <p>Processed by: {{ $order->user?->name ?? 'Cashier' }}</p>
            <p class="mt-1 break-all">Order token: {{ $order->order_token }}</p>
        </div>

        <button type="button" onclick="window.print()"
            class="no-print mt-6 w-full rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground hover:opacity-90 cursor-pointer">
            Print Receipt
        </button>
    </main>
</body>

</html>