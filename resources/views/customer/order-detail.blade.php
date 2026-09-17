<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Order Details #{{ $order->id }} - Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-muted text-foreground">
    <div class="mx-auto max-w-md min-h-screen bg-background shadow-xl border-x relative pb-12">
        <header class="border-b sticky top-0 bg-background/95 backdrop-blur z-20">
            <div class="flex h-14 items-center justify-between px-4">
                <a href="{{ route('table.tracking.show', $order->order_token) }}"
                    class="text-sm text-primary font-medium hover:underline flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Tracking
                </a>
                <p class="text-xs text-muted-foreground">Order #{{ $order->id }}</p>
            </div>
        </header>

        <main class="px-4 py-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight">Order Details</h1>
                    <p class="text-xs text-muted-foreground mt-0.5">{{ $order->created_at->format('d M Y, H:i') }}</p>
                </div>
                <span
                    class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 uppercase">
                    {{ $order->status->value }}
                </span>
            </div>

            <div class="rounded-2xl border bg-card shadow-sm p-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Customer</span>
                    <span class="font-medium">{{ $order->customer_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Table</span>
                    <span class="font-medium">{{ $order->table?->number ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Payment Method</span>
                    <span class="font-medium uppercase">{{ $order->payment_method?->value ?? '-' }}</span>
                </div>
            </div>

            <div class="rounded-2xl border bg-card shadow-sm p-4 divide-y">
                <div
                    class="pb-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground flex justify-between">
                    <span>Items</span>
                    <span>Subtotal</span>
                </div>
                @foreach($order->orderItems as $item)
                <div class="flex justify-between text-sm py-3 gap-3">
                    <span class="text-muted-foreground">{{ $item->quantity }} &times;</span>
                    <span class="flex-1">{{ $item->product->name }}</span>
                    <span class="font-medium shrink-0">$ {{ number_format($item->subtotal, 0, '.', ',') }}</span>
                </div>
                @endforeach
                <div class="pt-3 flex justify-between items-center font-bold text-base">
                    <span>Total</span>
                    <span>$ {{ number_format($order->orderItems->sum('subtotal'), 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="pt-2 space-y-3">
                <a href="{{ route('table.order.download', $order->order_token) }}"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-primary-foreground font-semibold h-12 hover:opacity-90 transition-all cursor-pointer">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download PDF Receipt
                </a>
                <a href="{{ route('table.menu', $order->table->qr_token) }}"
                    class="w-full inline-flex items-center justify-center rounded-xl border bg-background font-semibold h-12 hover:bg-muted transition-all cursor-pointer">
                    Order Again
                </a>
            </div>
        </main>
    </div>
</body>

</html>