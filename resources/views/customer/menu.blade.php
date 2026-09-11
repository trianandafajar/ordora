<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu — Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-background text-foreground">
    <header class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur">
        <div class="mx-auto flex h-14 items-center justify-between px-4 max-w-5xl">
            <div>
                <p class="font-bold">Ordora</p>
                <p class="text-xs text-muted-foreground">Table {{ $table->number }}</p>
            </div>
            <a href="{{ route('meja.checkout') }}"
                class="relative rounded-md border px-3 py-1.5 text-sm bg-primary text-primary-foreground cursor-pointer">
                Cart
                @php($cart = session('cart', []))
                @if(count($cart) > 0)
                <span
                    class="absolute -top-2 -right-2 flex size-5 items-center justify-center rounded-full bg-destructive text-white text-xs">
                    {{ collect($cart)->sum('quantity') }}
                </span>
                @endif
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-6 space-y-6">
        @if(session('success'))
        <div class="rounded-md border border-green-300 bg-green-50 text-green-800 p-3 text-sm">{{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="rounded-md border border-destructive/30 bg-destructive/10 text-destructive p-3 text-sm">{{
            session('error') }}</div>
        @endif

        @foreach($categories as $category)
        <section>
            <h2 class="text-lg font-bold mb-3">{{ $category->name }}</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($category->products as $product)
                <div class="rounded-xl border bg-card shadow-sm p-4 flex flex-col gap-2">
                    <div
                        class="aspect-square w-full rounded-lg bg-accent flex items-center justify-center text-muted-foreground text-sm">
                        {{ $product->name[0] }}
                    </div>
                    <div>
                        <h3 class="font-medium text-sm">{{ $product->name }}</h3>
                        <p class="text-xs text-muted-foreground line-clamp-2">{{ $product->description }}</p>
                    </div>
                    <div class="flex items-center justify-between mt-auto pt-2">
                        <span class="font-bold text-sm">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        <form action="{{ route('meja.cart.add', $table->qr_token) }}" method="POST"
                            class="flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <select name="quantity"
                                class="h-8 w-14 rounded-md border border-input bg-transparent px-1 text-sm">
                                @for($i = 1; $i <= 10; $i++) <option value="{{ $i }}" {{ $i===1 ? 'selected' : '' }}>{{
                                    $i }}</option>
                                    @endfor
                            </select>
                            <button type="submit"
                                class="rounded-md bg-primary text-primary-foreground px-3 py-1.5 text-xs font-medium hover:opacity-90 transition-opacity cursor-pointer">Add</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endforeach
    </main>
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