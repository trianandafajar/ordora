<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order #{{ $order->id }} — Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-background text-foreground">
    <header class="border-b">
        <div class="mx-auto flex h-14 items-center justify-between px-4 max-w-3xl">
            <a href="{{ route('kasir.dashboard') }}" class="text-sm text-muted-foreground hover:text-foreground">&larr;
                Back to orders</a>
            <span class="text-xs text-muted-foreground">Order #{{ $order->id }}</span>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-6 space-y-6">
        @if(session('success'))
        <div class="rounded-md border border-green-300 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div class="rounded-md border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive">
            @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div class="rounded-xl border bg-card shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="font-medium text-sm">{{ $order->customer_name }}</p>
                <p class="text-xs text-muted-foreground">Table {{ $order->table->number ?? '-' }} &middot; Total: Rp {{
                    number_format($order->total_price, 0, ',', '.') }}</p>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full font-medium capitalize
                {{ $order->status === \App\Enums\OrderStatus::Pending ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $order->status === \App\Enums\OrderStatus::Preparing ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $order->status === \App\Enums\OrderStatus::Ready ? 'bg-green-100 text-green-800' : '' }}
                {{ $order->status === \App\Enums\OrderStatus::Served ? 'bg-purple-100 text-purple-800' : '' }}
                {{ $order->status === \App\Enums\OrderStatus::Paid ? 'bg-emerald-100 text-emerald-800' : '' }}">
                {{ $order->status }}
            </span>
        </div>

        <div class="rounded-xl border bg-card shadow-sm p-4">
            <h3 class="text-sm font-medium mb-3">Order items</h3>
            <div class="space-y-2">
                @foreach($order->orderItems as $item)
                <div class="flex justify-between text-sm">
                    <span>{{ $item->quantity }} &times; {{ $item->product->name }}</span>
                    <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border bg-card shadow-sm p-4">
            <h3 class="text-sm font-medium mb-3">Order history</h3>
            <div class="space-y-2">
                @foreach($order->orderStatusHistories as $hist)
                <div class="flex justify-between text-xs">
                    <span class="capitalize">{{ $hist->status }}</span>
                    <span class="text-muted-foreground">{{ $hist->created_at->format('d M H:i') }} &middot; {{
                        $hist->changedBy->name ?? 'System' }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-3">
            @if($order->status !== \App\Enums\OrderStatus::Paid)
            @if($order->status === \App\Enums\OrderStatus::Pending)
            <form method="POST" action="{{ route('kasir.order.status', $order->id) }}">
                @csrf
                <input type="hidden" name="status" value="preparing">
                <button type="submit"
                    class="w-full rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 hover:opacity-90 transition-opacity cursor-pointer">
                    Move to Preparing
                </button>
            </form>
            @elseif($order->status === \App\Enums\OrderStatus::Preparing)
            <form method="POST" action="{{ route('kasir.order.status', $order->id) }}">
                @csrf
                <input type="hidden" name="status" value="ready">
                <button type="submit"
                    class="w-full rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 hover:opacity-90 transition-opacity cursor-pointer">
                    Move to Ready
                </button>
            </form>
            @elseif($order->status === \App\Enums\OrderStatus::Ready)
            <form method="POST" action="{{ route('kasir.order.status', $order->id) }}">
                @csrf
                <input type="hidden" name="status" value="served">
                <button type="submit"
                    class="w-full rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 hover:opacity-90 transition-opacity cursor-pointer">
                    Move to Served
                </button>
            </form>
            @endif

            @if($order->status === \App\Enums\OrderStatus::Served)
            <div class="rounded-xl border bg-card shadow-sm p-4">
                <h3 class="text-sm font-medium mb-1">Process payment</h3>
                <p class="mb-3 text-xs text-muted-foreground">Choose Cash or QRIS in the confirmation dialog.</p>
                <div>
                    <button type="button" data-open-payment="cash"
                        class="w-full rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 hover:opacity-90 transition-opacity cursor-pointer">
                        Pay
                    </button>
                </div>
                <form id="payment-submit-form" method="POST" action="{{ route('kasir.order.pay', $order->id) }}"
                    class="hidden">
                    @csrf
                    <input type="hidden" name="payment_method" id="payment-method-input">
                </form>
            </div>
            @elseif($order->status !== \App\Enums\OrderStatus::Paid)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-center text-sm text-amber-800">
                Payment is available after the order is served.
            </div>
            @endif
            @else
            <div class="rounded-xl border bg-green-50 p-4 text-center text-sm text-green-800 space-y-2">
                <p>Payment processed using <span class="font-semibold uppercase">{{ $order->payment_method?->value
                        }}</span>. Table {{ $order->table->number }} is now available.</p>
                <a href="{{ route('kasir.order.receipt', $order) }}"
                    class="inline-flex rounded-md bg-green-700 px-3 py-2 text-xs font-semibold text-white hover:bg-green-800 cursor-pointer">
                    View / Print receipt
                </a>
            </div>
            @endif
        </div>
    </main>

    @if($order->status === \App\Enums\OrderStatus::Served)
    <dialog id="payment-confirmation-dialog"
        class="w-full max-w-md rounded-2xl border bg-card p-0 shadow-2xl backdrop:bg-black/50">
        <div class="p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-primary">Order #{{ $order->id }}</p>
                    <h2 class="mt-1 text-xl font-semibold">Confirm payment</h2>
                </div>
                <button type="button" data-close-payment
                    class="rounded-lg p-2 text-muted-foreground hover:bg-muted cursor-pointer"
                    aria-label="Tutup">&times;</button>
            </div>

            <div class="mt-5 rounded-xl border bg-muted/30 p-4 text-sm">
                <div class="flex justify-between gap-3"><span>{{ $order->customer_name }}</span><span>Table {{
                        $order->table->number ?? '-' }}</span></div>
                <div class="mt-3 flex justify-between border-t pt-3 font-semibold"><span>Total</span><span>Rp {{
                        number_format($order->total_price, 0, ',', '.') }}</span></div>
            </div>

            <div data-payment-step="method">
                <p class="mt-5 text-sm text-muted-foreground">Select the payment method received.</p>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <button type="button" data-select-payment="cash"
                        class="rounded-xl border p-4 text-left text-sm hover:bg-muted cursor-pointer"><span
                            class="block font-semibold">Cash</span><span
                            class="mt-1 block text-xs text-muted-foreground">Cash payment</span></button>
                    <button type="button" data-select-payment="qris"
                        class="rounded-xl border p-4 text-left text-sm hover:bg-muted cursor-pointer"><span
                            class="block font-semibold">QRIS</span><span
                            class="mt-1 block text-xs text-muted-foreground">Manual confirmation</span></button>
                </div>
                <p data-selected-payment class="mt-3 text-sm text-muted-foreground">Method: Cash</p>
                <div class="mt-6 flex justify-end gap-3"><button type="button" data-close-payment
                        class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted cursor-pointer">Cancel</button><button
                        type="button" data-continue-payment
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:opacity-90 cursor-pointer">Continue</button>
                </div>
            </div>

            <div data-payment-step="confirmation" hidden>
                <div class="mt-5 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                    <p>This order will be marked as <strong>paid</strong> using <strong
                            data-confirm-payment-method>Cash</strong>.</p><label data-qris-confirmation
                        class="mt-4 hidden items-start gap-3"><input type="checkbox" data-qris-checkbox
                            class="mt-0.5 rounded border-amber-500 text-primary focus:ring-primary"><span>I have
                            received and manually verified the QRIS payment.</span></label>
                </div>
                <div class="mt-6 flex justify-between gap-3"><button type="button" data-back-payment
                        class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted cursor-pointer">Back</button><button
                        type="button" data-submit-payment
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:opacity-90 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50">Confirm
                        payment</button></div>
            </div>
        </div>
    </dialog>

    <script>
        (() => {
                const dialog = document.getElementById('payment-confirmation-dialog');
                const form = document.getElementById('payment-submit-form');
                const methodInput = document.getElementById('payment-method-input');
                const methodStep = dialog?.querySelector('[data-payment-step="method"]');
                const confirmationStep = dialog?.querySelector('[data-payment-step="confirmation"]');
                const selectedPayment = dialog?.querySelector('[data-selected-payment]');
                const confirmPaymentMethod = dialog?.querySelector('[data-confirm-payment-method]');
                const qrisConfirmation = dialog?.querySelector('[data-qris-confirmation]');
                const qrisCheckbox = dialog?.querySelector('[data-qris-checkbox]');
                const submitButton = dialog?.querySelector('[data-submit-payment]');
                let paymentMethod = 'cash';

                const setPaymentMethod = (method) => {
                    paymentMethod = method;
                    methodInput.value = method;
                    selectedPayment.textContent = `Method: ${method.toUpperCase()}`;
                    confirmPaymentMethod.textContent = method.toUpperCase();
                    qrisConfirmation.classList.toggle('hidden', method !== 'qris');
                    qrisConfirmation.classList.toggle('flex', method === 'qris');
                    qrisCheckbox.checked = false;
                    submitButton.disabled = method === 'qris';
                };

                document.querySelectorAll('[data-open-payment]').forEach((button) => {
                    button.addEventListener('click', () => {
                        setPaymentMethod(button.dataset.openPayment);
                        methodStep.hidden = false;
                        confirmationStep.hidden = true;
                        dialog.showModal();
                    });
                });

                dialog?.querySelectorAll('[data-select-payment]').forEach((button) => {
                    button.addEventListener('click', () => setPaymentMethod(button.dataset.selectPayment));
                });

                dialog?.querySelector('[data-continue-payment]')?.addEventListener('click', () => {
                    methodStep.hidden = true;
                    confirmationStep.hidden = false;
                });

                dialog?.querySelector('[data-back-payment]')?.addEventListener('click', () => {
                    methodStep.hidden = false;
                    confirmationStep.hidden = true;
                });

                qrisCheckbox?.addEventListener('change', () => {
                    submitButton.disabled = paymentMethod === 'qris' && !qrisCheckbox.checked;
                });

                dialog?.querySelectorAll('[data-close-payment]').forEach((button) => button.addEventListener('click', () => dialog.close()));
                submitButton?.addEventListener('click', () => {
                    if (paymentMethod === 'qris' && !qrisCheckbox.checked) return;
                    form.submit();
                });

                setPaymentMethod('cash');
            })();
    </script>
    @endif
</body>
<script>
    document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                var btn = form.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Processing...';
                }
            });
        });
</script>