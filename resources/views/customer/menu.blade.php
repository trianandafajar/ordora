<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Menu - Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>

<body class="min-h-screen bg-muted text-foreground" x-data="{ 
    toast: {
        show: false,
        message: '',
        type: 'success'
    },
    showToast(msg, type = 'success') {
        this.toast.message = msg;
        this.toast.type = type;
        this.toast.show = true;
        setTimeout(() => this.toast.show = false, 4000);
    },
    init() {
        @if(session('success'))
            this.showToast('{{ session('success') }}', 'success');
        @endif
        @if(session('error'))
            this.showToast('{{ session('error') }}', 'error');
        @endif
    }
}">
    <div class="mx-auto max-w-md min-h-screen bg-background shadow-xl border-x relative">
        <header
            class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
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

        {{-- Toast --}}
        <div x-show="toast.show" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-md rounded-xl border p-4 shadow-lg flex items-center justify-between"
            :class="{
                'bg-white border-green-200 text-green-800': toast.type === 'success',
                'bg-white border-red-200 text-red-800': toast.type === 'error'
            }">
            <div class="flex items-center gap-3">
                <template x-if="toast.type === 'success'">
                    <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
                <span class="text-sm font-medium" x-text="toast.message"></span>
            </div>
            <button @click="toast.show = false" class="ml-4 text-muted-foreground hover:text-foreground cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <main class="px-4 py-4 space-y-8 pb-8">
            @foreach($categories as $category)
            <section>
                <h2 class="text-lg font-bold mb-3">{{ $category->name }}</h2>
                <div class="flex flex-col gap-3">
                    @foreach($category->products as $product)
                    <div class="rounded-2xl border bg-card p-4 flex gap-4">
                        <div
                            class="size-20 shrink-0 rounded-xl bg-accent flex items-center justify-center overflow-hidden">
                            @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                class="h-full w-full object-cover">
                            @else
                            <span class="text-muted-foreground font-bold text-xl">{{ $product->name[0] }}</span>
                            @endif
                        </div>
                        <div class="flex flex-col flex-1 min-w-0">
                            <h3 class="font-semibold text-sm truncate">{{ $product->name }}</h3>
                            <p class="text-xs text-muted-foreground line-clamp-2 mt-0.5">{{ $product->description }}</p>
                            <div class="flex items-center justify-between mt-auto pt-3">
                                <span class="font-bold text-sm">Rp {{ number_format($product->price, 0, ',', '.')
                                    }}</span>
                                <form action="{{ route('meja.cart.add', $table->qr_token) }}" method="POST"
                                    x-data="{ qty: 1 }" class="w-full flex items-center justify-end gap-2">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" x-model.number="qty">
                                    <div class="flex items-center gap-1 border rounded-lg overflow-hidden">
                                        <button type="button" @click.prevent="if(qty > 1) qty--"
                                            class="h-8 w-8 flex items-center justify-center bg-muted hover:bg-accent transition-colors cursor-pointer">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span x-text="qty" class="w-8 text-center text-sm font-medium"></span>
                                        <button type="button" @click.prevent="if(qty < 99) qty++"
                                            class="h-8 w-8 flex items-center justify-center bg-muted hover:bg-accent transition-colors cursor-pointer">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                    <button type="submit"
                                        class="h-8 rounded-lg bg-primary text-primary-foreground px-4 text-xs font-semibold active:scale-95 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">Add</button>
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
                    btn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Adding...
                    `;
                }
            });
        });
    </script>
</body>

</html>