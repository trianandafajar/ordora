<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Order Tracking - Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>

<body class="min-h-screen bg-muted text-foreground">
    <div class="mx-auto max-w-md min-h-screen bg-background shadow-xl border-x relative">
        <header class="border-b">
            <div class="flex h-14 items-center justify-between px-4">
                <p class="font-bold">Ordora</p>
                <div class="flex items-center gap-3">
                    <a href="{{ route('table.history') }}"
                        class="text-xs text-primary font-medium hover:underline">Order History</a>
                    <p class="text-xs text-muted-foreground">Order #{{ $order->id }}</p>
                </div>
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
                    @foreach(['pending', 'preparing', 'ready', 'served'] as $i => $state)
                    <li class="flex flex-1 items-center">
                        <div class="flex flex-col items-center gap-2" data-status="{{ $state }}">
                            <div
                                class="status-dot size-6 rounded-full bg-muted border-2 border-background shadow-sm flex items-center justify-center">
                                <svg class="size-3 text-primary-foreground hidden" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7" />
                                </svg>
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
                    <span class="font-medium shrink-0">$ {{ number_format($item->subtotal, 0, '.', ',') }}</span>
                </div>
                @endforeach
                <div class="pt-3 flex justify-between items-center">
                    <span class="text-sm font-medium">Total</span>
                    <span class="font-bold text-lg">$ {{ number_format($order->orderItems->sum('subtotal'), 0, ',',
                        '.') }}</span>
                </div>
                @if($order->payment_method)
                <div class="pt-3 flex justify-between text-sm">
                    <span class="text-muted-foreground">Payment</span>
                    <span class="font-medium capitalize">{{ $order->payment_method->value }}</span>
                </div>
                @endif
            </div>

            <div id="reorderSection" class="pt-2 space-y-3"
                style="{{ $order->status->value === 'served' ? '' : 'display: none;' }}">
                <a href="{{ route('table.order.detail', $order->order_token) }}"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl border bg-background font-semibold h-12 hover:bg-muted transition-all cursor-pointer">
                    <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    View Details
                </a>
                <a href="{{ route('table.menu', $order->table->qr_token) }}"
                    class="w-full inline-flex items-center justify-center rounded-xl bg-primary text-primary-foreground font-semibold h-12 hover:opacity-90 transition-all cursor-pointer">
                    Order Again
                </a>
            </div>

        </main>
    </div>

    <script>
        const orderToken = {{ Js::from($order->order_token) }};
        const orderStatuses = ['pending', 'preparing', 'ready', 'served'];

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

            const reorderSection = document.getElementById('reorderSection');
            if (reorderSection) {
                reorderSection.style.display = status === 'served' ? 'block' : 'none';
            }
        }

        renderStatus({{ Js::from($order->status->value) }});

        // Save order to localStorage for history
        const saveOrderToHistory = () => {
            const orderData = {
                id: {{ $order->id }},
                token: orderToken,
                status: '{{ $order->status->value }}',
                customer_name: '{{ $order->customer_name }}',
                table: {{ Js::from($order->table->number) }},
                total: {{ $order->orderItems->sum('subtotal') }},
                items: {{ Js::from($order->orderItems->map(fn($item) => [
                    'name' => $item->product->name,
                    'qty' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ])->values()->toArray()) }},
                created_at: '{{ $order->created_at->format("Y-m-d H:i") }}'
            };

            const history = JSON.parse(localStorage.getItem('ordora-orders') || '[]');
            const existingIndex = history.findIndex(o => o.id === orderData.id);
            
            if (existingIndex >= 0) {
                history[existingIndex] = orderData;
            } else {
                history.unshift(orderData);
            }

            // Keep only last 20 orders
            if (history.length > 20) history.pop();
            
            localStorage.setItem('ordora-orders', JSON.stringify(history));
        };

        saveOrderToHistory();

        const subscribeToOrderChannel = () => {
            if (window.__ordoraTrackingChannel || !window.Echo) return;

            const channel = window.Echo.channel(`order.${orderToken}`);
            window.__ordoraTrackingChannel = channel;
            channel.listen('.order.status.updated', (payload) => {
                if (payload?.order?.status) {
                    renderStatus(payload.order.status);

                    // update status in localStorage history
                    const history = JSON.parse(localStorage.getItem('ordora-orders') || '[]');
                    const idx = history.findIndex(o => o.id === {{ $order->id }});
                    if (idx >= 0) {
                        history[idx].status = payload.order.status;
                        localStorage.setItem('ordora-orders', JSON.stringify(history));
                    }
                }
            });
        };

        subscribeToOrderChannel();
        window.addEventListener('ordora-echo-ready', subscribeToOrderChannel);
    </script>
</body>

</html>