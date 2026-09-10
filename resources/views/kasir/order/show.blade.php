<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order #{{ $order->id }} — Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground">
    <header class="border-b">
        <div class="mx-auto flex h-14 items-center justify-between px-4 max-w-3xl">
            <a href="{{ route('kasir.dashboard') }}" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to orders</a>
            <span class="text-xs text-muted-foreground">Order #{{ $order->id }}</span>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-6 space-y-6">
        <div class="rounded-xl border bg-card shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="font-medium text-sm">{{ $order->customer_name }}</p>
                <p class="text-xs text-muted-foreground">Table {{ $order->table->number ?? '-' }} &middot; Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full font-medium capitalize
                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $order->status === 'preparing' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $order->status === 'ready' ? 'bg-green-100 text-green-800' : '' }}
                {{ $order->status === 'served' ? 'bg-purple-100 text-purple-800' : '' }}">
                {{ $order->status }}
            </span>
        </div>

        <div class="rounded-xl border bg-card shadow-sm p-4">
            <h3 class="text-sm font-medium mb-3">Order Items</h3>
            <div class="space-y-2">
                @foreach($order->orderItems as $item)
                    <div class="flex justify-between text-sm">
                        <span>{{ $item->quantity }} &times; {{ $item->product->name }}</span>
                        <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border bg-card shadow-sm p-4">
            <h3 class="text-sm font-medium mb-3">Order History</h3>
            <div class="space-y-2">
                @foreach($order->orderStatusHistories as $hist)
                    <div class="flex justify-between text-xs">
                        <span class="capitalize">{{ $hist->status }}</span>
                        <span class="text-muted-foreground">{{ $hist->created_at->format('d M H:i') }} &middot; {{ $hist->changedBy->name ?? 'System' }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-3">
            @if($order->status !== \App\Enums\OrderStatus::Paid)
                @if($order->status === \App\Enums\OrderStatus::Pending)
                    <form method="POST" action="{{ route('kasir.order.status', $order->id) }}">
                        @csrf
                        <input type="hidden" name="status" value="preparing">
                        <button type="submit" class="w-full rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 hover:opacity-90 transition-opacity">
                            Mark as Preparing
                        </button>
                    </form>
                @elseif($order->status === \App\Enums\OrderStatus::Preparing)
                    <form method="POST" action="{{ route('kasir.order.status', $order->id) }}">
                        @csrf
                        <input type="hidden" name="status" value="ready">
                        <button type="submit" class="w-full rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 hover:opacity-90 transition-opacity">
                            Mark as Ready
                        </button>
                    </form>
                @elseif($order->status === \App\Enums\OrderStatus::Ready)
                    <form method="POST" action="{{ route('kasir.order.status', $order->id) }}">
                        @csrf
                        <input type="hidden" name="status" value="served">
                        <button type="submit" class="w-full rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 hover:opacity-90 transition-opacity">
                            Mark as Served
                        </button>
                    </form>
                @endif

                <div class="rounded-xl border bg-card shadow-sm p-4">
                    <h3 class="text-sm font-medium mb-3">Process Payment</h3>
                    <form method="POST" action="{{ route('kasir.order.pay', $order->id) }}" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <button type="submit" name="payment_method" value="cash" class="rounded-md border text-sm font-medium h-9 hover:bg-accent/50 transition-colors">
                                Cash
                            </button>
                            <button type="submit" name="payment_method" value="qris" class="rounded-md border text-sm font-medium h-9 hover:bg-accent/50 transition-colors">
                                QRIS
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="rounded-xl border bg-green-50 p-4 text-center text-sm text-green-800">
                    Payment processed. Table {{ $order->table->number }} is now available.
                </div>
            @endif
        </div>
    </main>
</body>
</html>