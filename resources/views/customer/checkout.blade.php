<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout — Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground">
    <header class="border-b">
        <div class="mx-auto flex h-14 items-center px-4 max-w-5xl">
            <a href="{{ url()->previous() }}" class="text-sm text-muted-foreground hover:text-foreground">&larr; Back to menu</a>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-6 space-y-6">
        <h1 class="text-xl font-bold tracking-tight">Checkout</h1>

        @if(session('error'))
            <div class="rounded-md border border-destructive/30 bg-destructive/10 text-destructive p-3 text-sm">{{ session('error') }}</div>
        @endif

        <div class="rounded-xl border bg-card shadow-sm">
            @foreach($cart as $item)
                <div class="flex items-center justify-between p-4 border-b last:border-b-0">
                    <div>
                        <p class="font-medium text-sm">{{ $item['name'] }}</p>
                        <p class="text-xs text-muted-foreground">Rp {{ number_format($item['price'], 0, ',', '.') }} &times; {{ $item['quantity'] }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-sm">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                        <form action="{{ route('meja.cart.remove', [session('table_qr'), $item['product_id']]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-destructive hover:underline">Remove</button>
                        </form>
                    </div>
                </div>
            @endforeach
            @if(empty($cart))
                <div class="p-8 text-center text-sm text-muted-foreground">Your cart is empty.</div>
            @endif
        </div>

        @if(!empty($cart))
            <div class="flex justify-between items-center rounded-xl border bg-card shadow-sm p-4">
                <span class="text-sm">Total</span>
                <span class="font-bold text-lg">Rp {{ number_format(collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']), 0, ',', '.') }}</span>
            </div>

            <form action="{{ route('meja.checkout.store') }}" method="POST" class="rounded-xl border bg-card shadow-sm p-4 space-y-4">
                @csrf
                <div class="space-y-2">
                    <label for="customer_name" class="text-sm font-medium">Your name</label>
                    <input id="customer_name" name="customer_name" required maxlength="100" placeholder="e.g. Budi"
                           class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                </div>
                <button type="submit" class="w-full rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 hover:opacity-90 transition-opacity">
                    Place Order
                </button>
            </form>
        @endif
    </main>
</body>
</html>