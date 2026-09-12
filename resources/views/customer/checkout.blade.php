<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Checkout - Ordora</title>
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
    <div class="mx-auto max-w-md min-h-screen bg-background shadow-xl border-x relative pb-48">
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

        <header
            class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
            <div class="flex h-14 items-center px-4">
                <a href="{{ route('table.menu', session('table_qr', '')) }}"
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
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.store('toast').showToast('{{ session('error') }}', 'error');
                });
            </script>
            @endif

            @if(empty($cart))
            <div class="rounded-2xl border bg-card p-12 text-center text-sm text-muted-foreground">Your cart is empty.
            </div>
            @else
            <div class="rounded-2xl border bg-card shadow-sm divide-y">
                @foreach($cart as $item)
                <div class="flex items-center justify-between p-4 gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm truncate">{{ $item['name'] }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5">$ {{ number_format($item['price'], 0, '.', ',')
                            }}
                            &times; {{ $item['quantity'] }}</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="font-bold text-sm">$ {{ number_format($item['price'] * $item['quantity'], 0, ',',
                            '.')
                            }}</span>
                        <form action="{{ route('table.cart.remove', [session('table_qr'), $item['product_id']]) }}"
                            method="POST" class="remove-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="size-8 flex items-center justify-center rounded-full hover:bg-destructive/10 text-destructive transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
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
                <span class="font-bold text-xl">$ {{ number_format(collect($cart)->sum(fn($i) => $i['price'] *
                    $i['quantity']), 0, ',', '.') }}</span>
            </div>

            <form action="{{ route('table.checkout.store') }}" method="POST" class="space-y-3">
                @csrf
                <input id="customer_name" name="customer_name" required maxlength="100" placeholder="Your name"
                    class="flex h-11 w-full rounded-xl border border-input bg-transparent px-4 text-sm shadow-xs outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                <button type="submit"
                    class="w-full h-12 rounded-xl bg-primary text-primary-foreground text-sm font-semibold active:scale-[0.98] transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
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
                    btn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    `;
                }
            });
        });
    </script>
</body>

</html>