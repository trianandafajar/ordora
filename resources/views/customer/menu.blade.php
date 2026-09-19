<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Menu - Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-muted text-foreground" x-data="{ 
    products: {{ Js::from($categories->flatMap(fn ($c) => $c->products)) }},
    selected: {},
    checkoutOpen: false,
    qrisOpen: false,
    cashOpen: false,
    successOpen: false,
    orderToken: null,
    customerName: '',
    paymentMethod: '',
    loading: false,

    get total() {
        return Object.entries(this.selected).reduce((sum, [id, qty]) => {
            const p = this.products.find(p => p.id == id);
            return sum + (p ? p.price * qty : 0);
        }, 0);
    },
    get itemCount() {
        return Object.values(this.selected).reduce((a, b) => a + b, 0);
    }
}">
    <div class="mx-auto max-w-md min-h-screen bg-background shadow-xl border-x relative pb-24">
        <x-header title="Table {{ $table->number }}">
            <x-slot:right>
                <div class="flex items-center gap-3">
                    <a href="{{ route('table.history') }}"
                        class="text-xs text-primary font-medium hover:underline">History</a>
                </div>
            </x-slot:right>
        </x-header>

        <main class="px-4 py-4 space-y-8">
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
                            <p class="text-xs text-muted-foreground line-clamp-2 mt-0.5">{{ $product->description }}
                            </p>
                            <div class="flex items-center justify-between mt-auto pt-3">
                                <span class="font-bold text-sm">$ {{ number_format($product->price, 0, '.', ',')
                                    }}</span>
                                <div class="flex items-center gap-1 border rounded-lg overflow-hidden">
                                    <button type="button"
                                        @click="if((selected[{{$product->id}}] || 0) > 0) selected[{{$product->id}}]--"
                                        class="h-8 w-8 flex items-center justify-center bg-muted hover:bg-accent transition-colors cursor-pointer">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <span x-text="selected[{{$product->id}}] || 0"
                                        class="w-8 text-center text-sm font-medium"></span>
                                    <button type="button"
                                        @click="selected[{{$product->id}}] = (selected[{{$product->id}}] || 0) + 1"
                                        class="h-8 w-8 flex items-center justify-center bg-muted hover:bg-accent transition-colors cursor-pointer">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endforeach
        </main>

        <div x-show="itemCount > 0" x-cloak
            class="fixed bottom-0 inset-x-0 z-40 border-t bg-background/95 backdrop-blur">
            <div class="mx-auto max-w-md p-4">
                <button @click="checkoutOpen = true"
                    class="w-full h-12 rounded-xl bg-primary text-primary-foreground font-semibold active:scale-[0.98] transition-transform cursor-pointer">
                    Order Now (<span x-text="itemCount"></span>) — $ <span x-text="total.toLocaleString()"></span>
                </button>
            </div>
        </div>

        {{-- Checkout dialog --}}
        <div x-show="checkoutOpen" x-cloak @click.outside="checkoutOpen = false"
            class="fixed inset-0 z-50 flex items-end bg-black/50" style="display:none">
            <div class="relative w-full max-w-md mx-auto rounded-t-2xl bg-card p-5 pb-8 shadow-[0_-4px_24px_rgba(0,0,0,0.15)] max-h-[85vh] overflow-y-auto"
                @click.stop>
                <div class="flex justify-center mb-3">
                    <div class="w-10 h-1 rounded-full bg-muted-foreground/30"></div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Order Confirmation</h3>
                    <button @click="checkoutOpen = false"
                        class="size-8 flex items-center justify-center rounded-full hover:bg-muted cursor-pointer"><svg
                            class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg></button>
                </div>
                <div class="space-y-2 max-h-40 overflow-y-auto divide-y">
                    <template x-for="[id, qty] in Object.entries(selected)" :key="id">
                        <template x-if="qty > 0">
                            <div x-data="{ name: products.find(p => p.id == id)?.name, price: products.find(p => p.id == id)?.price }"
                                class="flex justify-between text-sm py-2">
                                <span x-text="qty + ' x ' + name"></span>
                                <span x-text="'$ ' + (price * qty).toLocaleString()"></span>
                            </div>
                        </template>
                    </template>
                </div>
                <div class="border-t pt-2 mt-2 flex justify-between font-bold">
                    <span>Total</span>
                    <span x-text="'$ ' + total.toLocaleString()"></span>
                </div>
                <input x-model="customerName" required placeholder="Your name"
                    class="mt-3 w-full h-11 rounded-xl border px-4 text-sm">
                <div class="flex mt-3">
                    <label class="border p-3 rounded-xl cursor-pointer text-sm text-center w-full"
                        :class="paymentMethod === 'cash' ? 'bg-primary/10 border-primary' : ''">
                        <input type="radio" value="cash" x-model="paymentMethod" class="sr-only"> Cash
                    </label>
                    {{-- <label class="border p-3 rounded-xl cursor-pointer text-sm text-center"
                        :class="paymentMethod === 'qris' ? 'bg-primary/10 border-primary' : ''">
                        <input type="radio" value="qris" x-model="paymentMethod" class="sr-only"> QRIS
                    </label> --}}
                </div>
                <button
                    @click="if (loading || !paymentMethod || !customerName) return; loading = true; fetch('{{ route('table.placeOrder', $table->qr_token) }}', {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: JSON.stringify({customer_name: customerName, payment_method: paymentMethod, items: Object.entries(selected).filter(([id, qty]) => qty > 0).map(([id, qty]) => ({product_id: id, quantity: qty}))})}).then(r => r.json()).then(d => { orderToken = d.order_token; checkoutOpen = false; if(paymentMethod === 'qris') qrisOpen = true; else cashOpen = true; }).catch(e => { console.error(e); }).finally(() => { loading = false; })"
                    class="w-full h-12 mt-4 bg-primary text-primary-foreground rounded-xl font-bold active:scale-[0.98] transition-transform disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="loading || !paymentMethod || !customerName">
                    <span x-show="!loading">Submit</span>
                    <span x-show="loading">Processing...</span>
                </button>
            </div>
        </div>

        {{-- QRIS dialog --}}
        <div x-show="qrisOpen" x-cloak @click.outside="qrisOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" style="display:none">
            <div class="relative w-full max-w-sm rounded-xl bg-card p-6 shadow-lg" @click.stop>
                <button @click="qrisOpen = false"
                    class="absolute top-3 right-3 size-8 flex items-center justify-center rounded-full hover:bg-muted cursor-pointer"><svg
                        class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg></button>
                <h3 class="text-lg font-semibold mb-4 text-center">Scan QRIS</h3>
                <div class="size-48 bg-white mx-auto flex items-center justify-center rounded-lg overflow-hidden">
                    <img :src="`/table/order/${orderToken}/qris-qr`" alt="QRIS" class="size-48">
                </div>
                <p class="text-sm text-center mt-4 text-muted-foreground">Scan the code above with your payment app
                </p>
                <button @click="if (qrisOpen) { qrisOpen = false; successOpen = true; }"
                    class="w-full h-12 mt-4 bg-primary text-primary-foreground rounded-xl font-bold active:scale-[0.98] transition-transform cursor-pointer">I've
                    Paid</button>
            </div>
        </div>

        {{-- Cash dialog --}}
        <div x-show="cashOpen" x-cloak @click.outside="cashOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" style="display:none">
            <div class="relative w-full max-w-sm rounded-xl bg-card p-6 shadow-lg text-center" @click.stop>
                <button @click="cashOpen = false"
                    class="absolute top-3 right-3 size-8 flex items-center justify-center rounded-full hover:bg-muted cursor-pointer"><svg
                        class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg></button>
                <h3 class="text-lg font-semibold mb-2">Cashier Confirmation (Cash)</h3>
                <div class="size-48 bg-white mx-auto flex items-center justify-center rounded-lg overflow-hidden my-4">
                    <img :src="`/table/order/${orderToken}/cash-qr`" alt="Cash QR" class="size-48">
                </div>
                <p class="text-sm text-muted-foreground mb-4">Show this QR code to the cashier to confirm the cash
                    payment.</p>
                {{-- Button removed: cashier will scan QR to confirm automatically --}}
            </div>
        </div>

        {{-- Success dialog --}}
        <div x-show="successOpen" x-cloak @click.outside="successOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" style="display:none">
            <div class="relative w-full max-w-sm rounded-xl bg-card p-6 shadow-lg text-center" @click.stop>
                <div
                    class="size-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-green-600 font-bold text-2xl">
                    ✓</div>
                <h3 class="text-lg font-semibold mb-2">Order Successful</h3>
                <p class="text-sm text-muted-foreground mb-4">Your order has been received. Track its status below.
                </p>
                <button @click="successOpen = false; window.location = '/table/tracking/' + orderToken"
                    class="w-full h-12 bg-primary text-primary-foreground rounded-xl font-bold active:scale-[0.98] transition-transform">View
                    Status</button>
            </div>
        </div>
    </div>
</body>

</html>