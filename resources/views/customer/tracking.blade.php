<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Order Tracking - Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { -webkit-tap-highlight-color: transparent; }
    </style>
</head>

<body class="min-h-screen bg-muted text-foreground">
    <div class="mx-auto max-w-md min-h-screen bg-background shadow-xl border-x relative">
        <header class="border-b">
            <div class="flex h-14 items-center justify-between px-4">
                <p class="font-bold">Ordora</p>
                <p class="text-xs text-muted-foreground">Order #{{ $order->id }}</p>
            </div>
        </header>

        <main class="px-4 py-8 space-y-8 text-center pb-8">
            <div class="mx-auto size-16 rounded-full bg-primary/10 flex items-center justify-center">
                <svg class="size-8 text-primary animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">Thank you, {{ $order->customer_name }}!</h1>
                <p class="text-sm text-muted-foreground">Your order is being prepared. Track its live status below.</p>
            </div>

            <div class="rounded-2xl border bg-card shadow-sm p-6">
                <p id="liveStatus" class="text-sm font-medium text-primary mb-6">Status: {{ $order->status->value }}</p>
                <ol class="flex items-center justify-between" id="statusList">
                    @foreach(['pending', 'preparing', 'ready', 'served', 'paid'] as $i => $state)
                    <li class="flex flex-1 items-center">
                        <div class="flex flex-col items-center gap-2" data-status="{{ $state }}">
                            <div class="status-dot size-6 rounded-full bg-muted border-2 border-background shadow-sm flex items-center justify-center">
                                @if($i < 4)
                                <svg class="size-3 text-primary-foreground hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                                @else
                                <svg class="size-3.5 text-primary-foreground hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                @endif
                            </div>
                            <span class="status-label text-xs capitalize text-muted-foreground">{{ $state }}</span>
                        </div>
                        @if(!$loop->last)
                        <div class="h-0.5 flex-1 rounded-full bg-border -mt-6"></div>
                        @endif
                    </li>
                    @endforeach
                </ol>
            </div>

            <div class="rounded-2xl border bg-card shadow-sm text-left p-4 divide-y">
                @foreach($order->orderItems as $item)
                <div class="flex justify-between text-sm py-2 gap-3">
                    <span class="text-muted-foreground">{{ $item->quantity }} &times;</span>
                    <span class="flex-1">{{ $item->product->name }}</span>
                    <span class="font-medium shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                @endforeach
                <div class="pt-3 flex justify-between items-center">
                    <span class="text-sm font-medium">Total</span>
                    <span class="font-bold text-lg">Rp {{ number_format($order->orderItems->sum('subtotal'), 0, ',', '.') }}</span>
                </div>
                @if($order->payment_method)
                <div class="pt-3 flex justify-between text-sm">
                    <span class="text-muted-foreground">Payment</span>
                    <span class="font-medium capitalize">{{ $order->payment_method->value }}</span>
                </div>
                @endif
            </div>
        </main>
    </div>

    <script>
        const orderToken = {{ Js::from($order->order_token) }};
        const orderStatuses = ['pending', 'preparing', 'ready', 'served', 'paid'];

        function renderStatus(status) {
            const currentIndex = orderStatuses.indexOf(status);

            document.querySelectorAll('[data-status]').forEach((node) => {
                const nodeIndex = orderStatuses.indexOf(node.dataset.status);
                const dot = node.querySelector('.status-dot');
                const label = node.querySelector('.status-label');
                const check = dot.querySelector('svg');
                const active = nodeIndex <= currentIndex;

                dot.classList.toggle('bg-primary', active);
                dot.classList.toggle('bg-muted', !active);
                label.classList.toggle('font-medium', nodeIndex === currentIndex);
                label.classList.toggle('text-foreground', active);
                label.classList.toggle('text-muted-foreground', !active);
                if (check) check.classList.toggle('hidden', !(active && nodeIndex === currentIndex));
                check?.classList.toggle('bg-white0', false);
            });

            document.getElementById('liveStatus').textContent = `Status: ${status}`;
        }

        renderStatus({{ Js::from($order->status->value) }});

        const subscribeToOrderChannel = () => {
            if (window.__ordoraTrackingChannel || !window.Echo) return;

            const channel = window.Echo.channel(`order.${orderToken}`);
            window.__ordoraTrackingChannel = channel;
            channel.listen('.order.status.updated', (payload) => {
                if (payload?.order?.status) renderStatus(payload.order.status);
            });
        };

        subscribeToOrderChannel();
        window.addEventListener('ordora-echo-ready', subscribeToOrderChannel);
    </script>
</body>

</html>