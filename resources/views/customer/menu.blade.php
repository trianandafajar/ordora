<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Menu - Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { -webkit-tap-highlight-color: transparent; }
    </style>
</head>

<body class="min-h-screen bg-muted text-foreground">
    <div class="mx-auto max-w-md min-h-screen bg-background shadow-xl border-x relative">
        <header class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
            <div class="flex h-14 items-center justify-between px-4">
                <div>
                    <p class="font-bold">Ordora</p>
                    <p class="text-xs text-muted-foreground">Table {{ $table->number }}</p>
                </div>
                <a href="{{ route('meja.checkout') }}"
                    class="relative flex items-center gap-1.5 rounded-full border px-4 py-2 text-sm font-medium bg-primary text-primary-foreground cursor-pointer active:scale-95 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                    </svg>
                    Cart
                    @php($cart = session('cart', []))
                    @if(count($cart) > 0)
                    <span
                        class="absolute -top-1.5 -right-1.5 flex min-w-5 h-5 items-center justify-center rounded-full bg-destructive text-white text-[10px] font-bold px-1">
                        {{ collect($cart)->sum('quantity') }}
                    </span>
                    @endif
                </a>
            </div>
        </header>

        @if(session('success'))
        <div class="mx-4 mt-4 rounded-xl border border-green-300 bg-green-50 text-green-800 p-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="mx-4 mt-4 rounded-xl border border-destructive/30 bg-destructive/10 text-destructive p-3 text-sm">{{ session('error') }}</div>
        @endif

        <main class="px-4 py-4 space-y-8 pb-8">
            @foreach($categories as $category)
            <section>
                <h2 class="text-lg font-bold mb-3">{{ $category->name }}</h2>
                <div class="flex flex-col gap-3">
                    @foreach($category->products as $product)
                    <div class="rounded-2xl border bg-card p-4 flex gap-4">
                        <div
                            class="size-20 shrink-0 rounded-xl bg-accent flex items-center justify-center text-muted-foreground font-bold text-xl">
                            {{ $product->name[0] }}
                        </div>
                        <div class="flex flex-col flex-1 min-w-0">
                            <h3 class="font-semibold text-sm truncate">{{ $product->name }}</h3>
                            <p class="text-xs text-muted-foreground line-clamp-2 mt-0.5">{{ $product->description }}</p>
                            <div class="flex items-center justify-between mt-auto pt-3">
                                <span class="font-bold text-sm">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                <form action="{{ route('meja.cart.add', $table->qr_token) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <div class="flex items-center gap-2">
                                        <select name="quantity"
                                            class="h-8 w-12 rounded-lg border border-input bg-transparent px-1 text-center text-sm">
                                            @for($i = 1; $i <= 10; $i++) <option value="{{ $i }}" {{ $i===1 ? 'selected' : '' }}>{{
                                                $i }}</option>
                                                @endfor
                                        </select>
                                        <button type="submit"
                                            class="h-8 rounded-lg bg-primary text-primary-foreground px-3 text-xs font-semibold active:scale-95 transition-transform cursor-pointer">Add</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endforeach
        </main>
    </div>

    <script>
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                var btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Adding...';
                }
            });
        });
    </script>
</body>

</html>
