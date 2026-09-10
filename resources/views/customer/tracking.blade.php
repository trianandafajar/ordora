<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Tracking — Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-background text-foreground">
    <header class="border-b">
        <div class="mx-auto flex h-14 items-center justify-between px-4 max-w-3xl">
            <p class="font-bold">Ordora</p>
            <p class="text-xs text-muted-foreground">Order #{{ $order->id }}</p>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-8 space-y-6 text-center">
        <div class="mx-auto size-16 rounded-full bg-primary/10 flex items-center justify-center">
            <svg class="size-8 text-primary animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <div class="space-y-1">
            <h1 class="text-2xl font-bold tracking-tight">Thank you, {{ $order->customer_name }}!</h1>
            <p class="text-sm text-muted-foreground">Your order is being prepared. Track its live status below.</p>
        </div>

        <div class="rounded-xl border bg-card shadow-sm p-6">
            <ol class="flex items-center justify-between" id="statusList">
                @foreach(['pending', 'preparing', 'ready', 'served', 'paid'] as $i => $state)
                <li class="flex flex-1 items-center">
                    <div class="flex flex-col items-center gap-2" data-status="{{ $state }}">
                        <div
                            class="status-dot size-5 rounded-full {{ $order->status->value === $state ? 'bg-primary' : 'bg-muted' }}">
                        </div>
                        <span
                            class="text-xs capitalize {{ $order->status->value === $state ? 'font-medium text-foreground' : 'text-muted-foreground' }}">{{
                            $state }}</span>
                    </div>
                    @if(!$loop->last)
                    <div class="h-px flex-1 bg-border"></div>
                    @endif
                </li>
                @endforeach
            </ol>
        </div>

        <div class="rounded-xl border bg-card shadow-sm text-left p-4 space-y-3">
            @foreach($order->orderItems as $item)
            <div class="flex justify-between text-sm">
                <span>{{ $item->quantity }} &times; {{ $item->product->name }}</span>
                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
            @endforeach
            @if($order->payment_method)
            <div class="pt-2 border-t flex justify-between font-medium text-sm">
                <span>Payment</span>
                <span class="capitalize">{{ $order->payment_method->value }}</span>
            </div>
            @endif
        </div>
    </main>

    <script>
        setInterval(() => {
            window.location.reload();
        }, 5000);
    </script>
</body>

</html>