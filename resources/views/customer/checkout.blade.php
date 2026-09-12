<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Checkout — Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { -webkit-tap-highlight-color: transparent; }
    </style>
</head>

<body class="min-h-screen bg-muted text-foreground">
    <div class="mx-auto max-w-md min-h-screen bg-background shadow-xl border-x relative pb-48">
        <header class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
            <div class="flex h-14 items-center px-4">
                <a href="{{ route('meja.menu', session('table_qr', '')) }}"
                    class="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Menu
                </a>
            </div>
        </header>

        <main class="px-4 py-4 space-y-4">
            <h1 class="text-xl font-bold tracking-tight">Checkout</h1>

            @if(session('error'))
            <div class="rounded-xl border border-destructive/30 bg-destructive/10 text-destructive p-3 text-sm">{{
                session('error') }}</div>
            @endif

            @if(empty($cart))
            <div class="rounded-2xl border bg-card p-12 text-center text-sm text-muted-foreground">Your cart is empty.</div>
            @else
            <div class="rounded-2xl border bg-card shadow-sm divide-y">
                @foreach($cart as $item)
                <div class="flex items-center justify-between p-4 gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm truncate">{{ $item['name'] }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5">Rp {{ number_format($item['price'], 0, ',', '.') }}
                            &times; {{ $item['quantity'] }}</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="font-bold text-sm">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.')
                            }}</span>
                        <form action="{{ route('meja.cart.remove', [session('table_qr'), $item['product_id']]) }}"
                            method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="size-8 flex items-center justify-center rounded-full hover:bg-destructive/10 text-destructive transition-colors cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </main>

        @if(!empty($cart))
        <div class="absolute bottom-0 left-0 right-0 bg-background border-t p-4 space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-muted-foreground">Total</span>
                <span class="font-bold text-xl">Rp {{ number_format(collect($cart)->sum(fn($i) => $i['price'] *
                    $i['quantity']), 0, ',', '.') }}</span>
            </div>

            <form action="{{ route('meja.checkout.store') }}" method="POST" class="space-y-3">
                @csrf
                <input id="customer_name" name="customer_name" required maxlength="100" placeholder="Your name"
                    class="flex h-11 w-full rounded-xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                <button type="submit"
                    class="w-full h-12 rounded-xl bg-primary text-primary-foreground text-sm font-semibold active:scale-[0.98] transition-transform cursor-pointer">
                    Place Order
                </button>
            </form>
        </div>
        @endif
    </div>

    <script>
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                var btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Processing...';
                }
            });
        });
    </script>
</body>

</html>
