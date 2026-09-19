<?php

use App\Actions\PayOrderAction;
use App\Actions\UpdateOrderStatusAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    private const ACTIVE_STATUS_VALUES = ['pending', 'preparing', 'ready'];

    #[Url]
    public string $activeTab = 'orders';

    public string $search = '';
    public string $statusFilter = 'all';
    public string $historyFrom = '';
    public string $historyTo = '';

    public bool $paymentDialogOpen = false;
    public string $paymentStep = 'method';
    public ?int $paymentOrderId = null;
    public string $paymentMethod = PaymentMethod::Cash->value;
    public bool $qrisConfirmed = false;
    public array $paymentData = [];
    public string $paymentError = '';

    public bool $receiptDialogOpen = false;
    public array $receiptData = [];

    public function activeStatuses(): array
    {
        return [OrderStatus::Pending, OrderStatus::Preparing, OrderStatus::Ready];
    }

    public function statusMeta(): array
    {
        return [
            'pending' => ['label' => 'Pending', 'description' => 'New orders', 'dot' => 'bg-amber-500', 'surface' => 'bg-amber-500/10', 'text' => 'text-amber-700 dark:text-amber-400', 'line' => 'border-amber-500/20', 'topBar' => 'bg-amber-500'],
            'preparing' => ['label' => 'Preparing', 'description' => 'In progress', 'dot' => 'bg-blue-500', 'surface' => 'bg-blue-500/10', 'text' => 'text-blue-700 dark:text-blue-400', 'line' => 'border-blue-500/20', 'topBar' => 'bg-blue-500'],
            'ready' => ['label' => 'Ready', 'description' => 'Ready to serve', 'dot' => 'bg-emerald-500', 'surface' => 'bg-emerald-500/10', 'text' => 'text-emerald-700 dark:text-emerald-400', 'line' => 'border-emerald-500/20', 'topBar' => 'bg-emerald-500'],
            'served' => ['label' => 'Served', 'description' => 'Completed', 'dot' => 'bg-violet-500', 'surface' => 'bg-violet-500/10', 'text' => 'text-violet-700 dark:text-violet-400', 'line' => 'border-violet-500/20', 'topBar' => 'bg-violet-500'],
        ];
    }

    #[Computed]
    public function orderColumns(): array
    {
        $query = Order::query()
            ->with(['table:id,number', 'orderItems.product:id,name'])
            ->whereIn('status', self::ACTIVE_STATUS_VALUES)
            ->latest('created_at')->latest('id');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $this->applySearch($query);

        return $query->get()->groupBy(fn (Order $order): string => $order->status->value)->all();
    }

    #[Computed]
    public function historyOrders()
    {
        $query = Order::query()
            ->with(['table:id,number', 'orderItems.product:id,name'])
            ->whereNotNull('paid_at')
            ->latest('paid_at')->latest('id');

        $this->applySearch($query);

        if ($this->historyFrom !== '') {
            $query->whereDate('paid_at', '>=', $this->historyFrom);
        }

        if ($this->historyTo !== '') {
            $query->whereDate('paid_at', '<=', $this->historyTo);
        }

        return $query->limit(100)->get();
    }

    #[Computed]
    public function kpis(): array
    {
        return [
            'active_orders' => Order::whereIn('status', self::ACTIVE_STATUS_VALUES)->count(),
            'occupied_tables' => DB::table('tables')->where('status', TableStatus::Occupied->value)->count(),
            'pending_orders' => Order::where('status', OrderStatus::Pending)->count(),
            'today_revenue' => Order::whereNotNull('paid_at')->whereDate('paid_at', today())->sum('total_price'),
        ];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['orders', 'history'], true) ? $tab : 'orders';
        $this->refreshBoard();
    }

    public function refreshBoard(): void
    {
        unset($this->orderColumns, $this->historyOrders, $this->kpis);
    }

    #[On('order-board-refresh')]
    public function refreshFromRealtime(): void
    {
        $this->refreshBoard();
    }

    public function moveOrder(int $orderId, string $targetStatus): void
    {
        $user = auth()->user();
        abort_unless($user?->role === UserRole::Kasir, 403);

        $target = OrderStatus::tryFrom($targetStatus);
        if (! $target) {
            throw ValidationException::withMessages(['status' => 'The target status is invalid.']);
        }

        DB::transaction(function () use ($orderId, $target, $user): void {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);

            $allowedTargets = [...$this->activeStatuses(), OrderStatus::Served];

            if (! in_array($target, $allowedTargets, true)) {
                throw ValidationException::withMessages(['status' => 'The target status is invalid for this order.']);
            }

            (new UpdateOrderStatusAction())->execute($order, $target, $user->id);
        });

        $this->dispatch('order-board-toast', message: 'Order status updated successfully.');
    }

    public function openPaymentDialog(int $orderId, ?string $method = null): void
    {
        abort_unless(auth()->user()?->role === UserRole::Kasir, 403);

        $order = Order::query()
            ->with(['table:id,number', 'orderItems.product:id,name'])
            ->findOrFail($orderId);

        $selectedMethod = PaymentMethod::tryFrom($method ?? $order->payment_method?->value ?? PaymentMethod::Cash->value);
        if (! $selectedMethod) {
            $this->paymentError = 'The payment method is invalid.';

            return;
        }

        if ($order->paid_at !== null) {
            $this->paymentError = 'This order has already been paid.';
            $this->refreshBoard();

            return;
        }

        $this->resetErrorBag();
        $this->paymentError = '';
        $this->paymentDialogOpen = true;
        $this->paymentOrderId = $order->id;
        $this->paymentMethod = $selectedMethod->value;
        $this->qrisConfirmed = false;
        $this->paymentData = $this->serializeOrder($order);

        if ($order->payment_method !== null) {
            $this->paymentStep = 'confirmation';
        } else {
            $this->paymentStep = 'method';
        }
    }

    public function selectPaymentMethod(string $method): void
    {
        $selectedMethod = PaymentMethod::tryFrom($method);
        if (! $selectedMethod) {
            $this->addError('paymentMethod', 'The payment method is invalid.');

            return;
        }

        $this->resetErrorBag();
        $this->paymentMethod = $selectedMethod->value;
        $this->qrisConfirmed = false;
    }

    public function continuePayment(): void
    {
        if (! PaymentMethod::tryFrom($this->paymentMethod)) {
            $this->addError('paymentMethod', 'Select a payment method first.');

            return;
        }

        if ($this->paymentOrderId === null) {
            $this->paymentError = 'The payment order could not be found.';

            return;
        }

        $order = Order::query()->find($this->paymentOrderId);
        if (! $order || $order->paid_at !== null) {
            $this->paymentError = 'This order has changed and cannot be paid from this dialog.';
            $this->paymentDialogOpen = false;
            $this->refreshBoard();

            return;
        }

        $this->resetErrorBag();
        $this->paymentError = '';
        $this->paymentStep = 'confirmation';
    }

    public function confirmPayment(): void
    {
        abort_unless(auth()->user()?->role === UserRole::Kasir, 403);

        $this->resetErrorBag();
        $method = PaymentMethod::tryFrom($this->paymentMethod);
        if (! $method) {
            $this->addError('paymentMethod', 'The payment method is invalid.');

            return;
        }

        if (($method === PaymentMethod::Qris || $method === PaymentMethod::Cash) && ! $this->qrisConfirmed) {
            $this->addError('qrisConfirmed', 'Confirm the payment first.');

            return;
        }

        if ($this->paymentOrderId === null) {
            $this->paymentError = 'The payment order could not be found.';

            return;
        }

        try {
            $paidOrder = (new PayOrderAction())->execute(
                Order::query()->findOrFail($this->paymentOrderId),
                $method,
                auth()->id(),
            );
        } catch (ValidationException $exception) {
            $this->paymentError = $exception->validator->errors()->first() ?? 'The payment could not be processed.';
            $this->refreshBoard();

            return;
        } catch (ModelNotFoundException) {
            $this->paymentError = 'The order could not be found.';
            $this->refreshBoard();

            return;
        }

        $this->receiptData = [
            ...$this->serializeOrder($paidOrder),
            'payment_method' => $paidOrder->payment_method?->value,
            'cashier_name' => $paidOrder->user?->name ?? auth()->user()?->name ?? 'Cashier',
            'paid_at' => $paidOrder->paid_at?->format('d M Y, H:i'),
            'receipt_url' => route('cashier.order.receipt', $paidOrder),
        ];

        $this->paymentDialogOpen = false;
        $this->receiptDialogOpen = true;
        $this->resetPaymentState();
        $this->refreshBoard();
        $this->dispatch('order-board-toast', message: 'Payment processed successfully.');
    }

    public function cancelPayment(): void
    {
        $this->paymentDialogOpen = false;
        $this->resetPaymentState();
        $this->refreshBoard();
    }

    public function closeReceiptDialog(): void
    {
        $this->receiptDialogOpen = false;
        $this->receiptData = [];
    }

    private function nextStatus(OrderStatus $status): ?OrderStatus
    {
        return match ($status) {
            OrderStatus::Pending => OrderStatus::Preparing,
            OrderStatus::Preparing => OrderStatus::Ready,
            OrderStatus::Ready => OrderStatus::Served,
            default => null,
        };
    }

    private function applySearch(Builder $query): void
    {
        $search = trim($this->search);
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($search): void {
            $builder->where('customer_name', 'like', "%{$search}%")
                ->orWhere('id', 'like', "%{$search}%")
                ->orWhereHas('table', fn (Builder $table): Builder => $table->where('number', 'like', "%{$search}%"));
        });
    }

    /**
     * @return array{id: int, customer_name: string, table_number: string|int|null, total_price: string, items: array<int, array{id: int, quantity: int, product_name: string, price: string, subtotal: string}>}
     */
    private function serializeOrder(Order $order): array
    {
        $order->loadMissing(['table:id,number', 'orderItems.product:id,name']);

        return [
            'id' => $order->id,
            'customer_name' => $order->customer_name,
            'table_number' => $order->table?->number,
            'total_price' => (string) $order->total_price,
            'items' => $order->orderItems->map(fn ($item): array => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'product_name' => $item->product->name,
                'price' => (string) $item->price,
                'subtotal' => (string) $item->subtotal,
            ])->values()->all(),
        ];
    }

    private function resetPaymentState(): void
    {
        $this->paymentStep = 'method';
        $this->paymentOrderId = null;
        $this->paymentMethod = PaymentMethod::Cash->value;
        $this->qrisConfirmed = false;
        $this->paymentData = [];
        $this->paymentError = '';
        $this->resetErrorBag();
    }
};
?>

<div data-order-board x-data="orderBoardRealtimeState" class="space-y-6">
    <div x-show="toastMessage" x-cloak x-transition
        class="fixed right-4 top-4 z-50 flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-card py-3 pe-4 ps-3 text-sm shadow-lg"
        role="status"><span
            class="flex size-7 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600"><svg
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg></span><span class="font-medium" x-text="toastMessage"></span></div>

    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-1">
            <h1 class="text-3xl font-semibold tracking-tight text-foreground"> Live order board </h1>
            <p class="max-w-xl text-sm leading-6 text-muted-foreground">
                Monitor and process customer orders in realtime.
            </p>
        </div>
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <label class="relative min-w-0 md:w-72"> <span class="sr-only">Search orders</span> <svg
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor"
                    class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />
                </svg> <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search orders..."
                    class="h-11 w-full rounded-xl border bg-card pl-10 pr-4 text-sm outline-none transition placeholder:text-muted-foreground/70 focus:border-primary/40 focus:ring-4 focus:ring-primary/10">
            </label>
            <div class="relative"> <select wire:model.live="statusFilter"
                    class="h-11 w-full appearance-none rounded-xl border bg-card px-4 pr-10 text-sm font-medium outline-none transition focus:border-primary/40 focus:ring-4 focus:ring-primary/10 md:w-40">
                    <option value="all">All statuses</option> @foreach($this->activeStatuses() as $status) <option
                        value="{{ $status->value }}"> {{ $this->statusMeta()[$status->value]['label'] }} </option>
                    @endforeach
                </select> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor"
                    class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </div>
            <div class="flex h-11 items-center justify-between gap-3 rounded-xl border bg-card px-3.5">
                <div class="flex items-center gap-2.5"> <span class="relative flex size-2.5"> <span
                            class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex size-2.5 rounded-full bg-emerald-500"></span> </span> <span
                        class="whitespace-nowrap text-sm font-medium tabular-nums" x-text="formattedNow()"></span>
                </div>
                <div class="h-5 w-px bg-border">

                </div>
                <button type="button" @click="toggleSound()"
                    :aria-label="soundEnabled ? 'Turn sound off' : 'Turn sound on'"
                    :title="soundEnabled ? 'Sound on' : 'Sound off'"
                    class="inline-flex size-8 cursor-pointer items-center justify-center rounded-lg text-muted-foreground transition hover:bg-muted hover:text-foreground">
                    <svg x-show="soundEnabled" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.8" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
                    </svg> {{-- Sound Off --}} <svg x-show="!soundEnabled" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m9.143 17.082a24.248 24.248 0 0 0 3.844.148m-3.844-.148a23.856 23.856 0 0 1-5.455-1.31 8.964 8.964 0 0 0 2.3-5.542m3.155 6.852a3 3 0 0 0 5.667 1.97m1.965-2.277L21 21m-4.225-4.225a23.81 23.81 0 0 0 3.536-1.003A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6.53 6.53m10.245 10.245L6.53 6.53M3 3l3.53 3.53" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div wire:loading wire:target="moveOrder,confirmPayment"
        class="rounded-lg border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-primary">Updating order...</div>
    @error('status')<div
        class="rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive">{{ $message
        }}</div>@enderror
    @if($paymentError !== '')<div
        class="rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive">{{
        $paymentError }}</div>@endif

    @if($activeTab === 'orders')
    @php
    $kpiCards = [
    ['label' => 'Active orders', 'value' => $this->kpis['active_orders'], 'icon' => 'shopping-bag', 'accent' =>
    'bg-primary/10 text-primary'],
    ['label' => 'Occupied tables', 'value' => $this->kpis['occupied_tables'], 'icon' => 'table', 'accent' =>
    'bg-blue-500/10 text-blue-600 dark:text-blue-400'],
    ['label' => 'Pending orders', 'value' => $this->kpis['pending_orders'], 'icon' => 'clock', 'accent' =>
    'bg-amber-500/10 text-amber-600 dark:text-amber-400'],
    ['label' => "Today's revenue", 'value' => '$ ' . number_format($this->kpis['today_revenue'], 0, '.', ','), 'icon'
    => 'banknotes', 'accent' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'],
    ];
    @endphp

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($kpiCards as $card)
        <div class="group rounded-2xl border bg-card p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center justify-between text-sm text-muted-foreground">
                <span>{{ $card['label'] }}</span>
                <div
                    class="flex size-11 items-center justify-center rounded-xl transition-transform group-hover:scale-105 {{ $card['accent'] }}">
                    @switch($card['icon'])
                    @case('table')
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <rect x="5.25" y="5.25" width="13.5" height="13.5" rx="2.25" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 18.75v1.5m7.5-1.5v1.5M8.25 3.75v1.5m7.5-1.5v1.5" />
                        <circle cx="12" cy="12" r="2.25" />
                    </svg>
                    @break
                    @case('shopping-bag')
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>

                    @break
                    @case('clock')
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>

                    @break
                    @case('banknotes')
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>

                    @break
                    @endswitch
                </div>
            </div>
            <p class="mt-4 text-3xl font-bold tracking-tight">{{ $card['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        @foreach($this->activeStatuses() as $status)
        @php($meta = $this->statusMeta()[$status->value]) @php($columnOrders = $this->orderColumns[$status->value] ??
        collect())
        <section
            class="relative min-h-[30rem] overflow-hidden rounded-2xl border {{ $meta['line'] }} bg-card p-3 shadow-sm transition-shadow">
            <div class="mb-3 flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2"><span class="size-2.5 rounded-full {{ $meta['dot'] }}"></span>
                        <h2 class="font-semibold tracking-tight">{{ $meta['label'] }}</h2>
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">{{ $meta['description'] }}</p>
                </div><span
                    class="rounded-full {{ $meta['surface'] }} {{ $meta['text'] }} px-2.5 py-1 text-xs font-semibold">{{
                    $columnOrders->count() }}</span>
            </div>
            <div class="space-y-3" data-order-column="{{ $status->value }}">
                @forelse($columnOrders as $order)
                <article data-order-id="{{ $order->id }}"
                    class="group cursor-grab rounded-xl border bg-card p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-primary/25 hover:shadow-md active:cursor-grabbing"
                    wire:key="order-{{ $order->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <span
                                class="shrink-0 rounded-md bg-muted px-1.5 py-0.5 text-xs font-semibold text-muted-foreground">#{{
                                $order->id }}</span>
                            <span class="truncate font-semibold">{{ $order->customer_name }}</span>
                        </div>
                        <span class="shrink-0 rounded-md border bg-muted/50 px-2 py-1 text-xs font-medium">Table {{
                            $order->table?->number ?? '-' }}</span>
                    </div>
                    <p class="mt-3 flex items-center gap-1.5 text-xs text-muted-foreground"><svg
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg><span>{{ $order->created_at?->format('d M Y, H:i') }}</span></p>
                    <div class="mt-3 divide-y divide-border/60 border-y py-1.5 text-sm">
                        @foreach($order->orderItems as $item)
                        <div class="flex items-center justify-between gap-3 py-1.5"><span
                                class="flex min-w-0 items-center gap-2"><span
                                    class="shrink-0 rounded bg-muted px-1.5 py-0.5 text-[11px] font-semibold text-muted-foreground">{{
                                    $item->quantity }}&times;</span><span class="truncate">{{ $item->product->name
                                    }}</span></span><span class="shrink-0 text-muted-foreground">$ {{
                                number_format($item->subtotal, 0, '.', ',') }}</span></div>
                        @endforeach
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2"><span
                            class="text-base font-bold">$ {{ number_format($order->total_price, 0, '.', ',')
                            }}</span>
                        <div class="flex flex-wrap items-center gap-1.5">
                            @if($order->paid_at === null)
                            <button type="button" wire:click="openPaymentDialog({{ $order->id }})"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-600 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                </svg>
                                Confirm Payment
                            </button>
                            @endif
                            @if($next = $this->nextStatus($status))
                            <button type="button" wire:click="moveOrder({{ $order->id }}, '{{ $next->value }}')"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-primary-foreground shadow-sm transition hover:opacity-90 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50">{{
                                $this->statusMeta()[$next->value]['label']
                                }}<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg></button>
                            @endif
                        </div>
                    </div>
                </article>
                @empty
                <div
                    class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed p-8 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-7 text-muted-foreground/40">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                    </svg>
                    <p class="text-xs font-medium text-muted-foreground/60">No orders yet.</p>
                </div>
                @endforelse
            </div>
        </section>
        @endforeach
    </div>
    @else
    <div class="rounded-2xl border bg-card p-4 shadow-sm">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end"><label
                class="flex-1 text-xs font-medium">From<input wire:model.live="historyFrom" type="date"
                    class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm"></label><label
                class="flex-1 text-xs font-medium">To<input wire:model.live="historyTo" type="date"
                    class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm"></label></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[42rem] text-left text-sm">
                <thead class="border-b text-xs uppercase text-muted-foreground">
                    <tr>
                        <th class="px-3 py-3">Order</th>
                        <th class="px-3 py-3">Customer</th>
                        <th class="px-3 py-3">Table</th>
                        <th class="px-3 py-3">Items</th>
                        <th class="px-3 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y">@forelse($this->historyOrders as $order)<tr>
                        <td class="px-3 py-3 font-semibold">#{{ $order->id }}<div
                                class="text-xs font-normal text-muted-foreground">{{ $order->paid_at?->format('d M Y,
                                H:i') }}</div>
                        </td>
                        <td class="px-3 py-3">{{ $order->customer_name }}</td>
                        <td class="px-3 py-3">{{ $order->table?->number ?? '-' }}</td>
                        <td class="px-3 py-3">{{ $order->orderItems->sum('quantity') }} item(s)</td>
                        <td class="px-3 py-3 text-right font-semibold">$ {{ number_format($order->total_price, 0, ',',
                            '.')
                            }}</td>
                    </tr>@empty<tr>
                        <td colspan="5" class="px-3 py-12 text-center text-muted-foreground">No payment history yet.
                        </td>
                    </tr>@endforelse</tbody>
            </table>
        </div>
    </div>
    @endif

    @if($paymentDialogOpen)
    <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4" wire:click.self="cancelPayment"
        wire:keydown.escape="cancelPayment">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-card p-6 shadow-2xl" role="dialog"
            aria-modal="true" aria-labelledby="payment-dialog-title">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-primary">Order #{{ $paymentData['id'] ??
                        $paymentOrderId }}</p>
                    <h2 id="payment-dialog-title" class="mt-1 text-xl font-semibold">{{ $paymentStep === 'method' ?
                        'Choose
                        payment method' : 'Confirm payment' }}</h2>
                </div>
                <button type="button" wire:click="cancelPayment"
                    class="rounded-lg p-2 text-muted-foreground hover:bg-muted cursor-pointer"
                    aria-label="Close">&times;</button>
            </div>
            @if($paymentData !== [])
            <div class="mt-5 rounded-xl border bg-card p-4">
                <div class="flex justify-between gap-3 text-sm"><span>{{ $paymentData['customer_name']
                        }}</span><span>Table
                        {{ $paymentData['table_number'] ?? '-' }}</span></div>
                <div class="mt-3 space-y-1 border-t pt-3 text-sm">
                    @foreach($paymentData['items'] as $item)
                    <div class="flex justify-between gap-3"><span>{{ $item['quantity'] }}&times; {{
                            $item['product_name']
                            }}</span><span>$ {{ number_format((float) $item['subtotal'], 0, ',', '.') }}</span></div>
                    @endforeach
                </div>
                <div class="mt-3 flex justify-between border-t pt-3 text-base font-semibold"><span>Total</span><span>$
                        {{
                        number_format((float) $paymentData['total_price'], 0, ',', '.') }}</span></div>
            </div>
            @endif
            @if($paymentStep === 'method')
            <p class="mt-5 text-sm text-muted-foreground">Select the payment method received from the customer.</p>
            <div class="mt-3 grid grid-cols-2 gap-3"><button type="button" wire:click="selectPaymentMethod('cash')"
                    class="rounded-xl border p-4 text-left cursor-pointer {{ $paymentMethod === 'cash' ? 'border-primary bg-primary/10 ring-2 ring-primary/20' : 'hover:bg-muted' }}"><span
                        class="block font-semibold">Cash</span><span
                        class="mt-1 block text-xs text-muted-foreground">Cash
                        payment</span></button><button type="button" wire:click="selectPaymentMethod('qris')"
                    class="rounded-xl border p-4 text-left cursor-pointer {{ $paymentMethod === 'qris' ? 'border-primary bg-primary/10 ring-2 ring-primary/20' : 'hover:bg-muted' }}"><span
                        class="block font-semibold">QRIS</span><span
                        class="mt-1 block text-xs text-muted-foreground">Manual
                        confirmation</span></button></div>
            @error('paymentMethod')<p class="mt-2 text-sm text-destructive">{{ $message }}</p>@enderror
            <div class="mt-6 flex justify-end gap-3"><button type="button" wire:click="cancelPayment"
                    class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted cursor-pointer">Cancel</button><button
                    type="button" wire:click="continuePayment"
                    class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:opacity-90 cursor-pointer">Continue</button>
            </div>
            @else
            <div class="mt-5 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                <p>This order will be marked as <strong>paid</strong> using <strong>{{ strtoupper($paymentMethod)
                        }}</strong>.</p>
                <label class="mt-4 flex items-start gap-3"><input wire:model.live="qrisConfirmed" type="checkbox"
                        class="mt-0.5 rounded border-amber-500 text-primary focus:ring-primary"><span>I have received
                        and verified the payment.</span></label>
            </div>
            @error('qrisConfirmed')<p class="mt-2 text-sm text-destructive">{{ $message }}</p>@enderror
            @if($paymentError !== '')<p class="mt-2 text-sm text-destructive">{{ $paymentError }}</p>@endif
            <div class="mt-6 flex justify-between gap-3"><button type="button"
                    wire:click="$set('paymentStep', 'method')"
                    class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted cursor-pointer">Back</button>
                <div class="flex gap-3"><button type="button" wire:click="cancelPayment"
                        class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted cursor-pointer">Cancel</button><button
                        type="button" wire:click="confirmPayment" wire:loading.attr="disabled"
                        wire:target="confirmPayment" @disabled((($paymentMethod==='qris' || $paymentMethod==='cash' ) &&
                        ! $qrisConfirmed))
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:opacity-90 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"><span
                            wire:loading.remove wire:target="confirmPayment">Confirm Payment</span><span wire:loading
                            wire:target="confirmPayment">Processing...</span></button></div>
            </div>
            @endif
        </div>
    </div>
    @endif

    @if($receiptDialogOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        wire:click.self="closeReceiptDialog" wire:keydown.escape="closeReceiptDialog">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-card p-6 shadow-2xl" role="dialog"
            aria-modal="true" aria-labelledby="receipt-dialog-title">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">Payment successful</p>
                    <h2 id="receipt-dialog-title" class="mt-1 text-xl font-semibold">Receipt for Order #{{
                        $receiptData['id'] ?? '' }}</h2>
                </div><button type="button" wire:click="closeReceiptDialog"
                    class="rounded-lg p-2 text-muted-foreground hover:bg-muted cursor-pointer"
                    aria-label="Close">&times;</button>
            </div>
            <div class="mt-5 space-y-3 rounded-xl border bg-card p-4 text-sm">
                <div class="flex justify-between gap-3"><span>Customer</span><span class="font-medium">{{
                        $receiptData['customer_name'] ?? '-' }}</span></div>
                <div class="flex justify-between gap-3"><span>Table</span><span class="font-medium">{{
                        $receiptData['table_number'] ?? '-' }}</span></div>
                <div class="flex justify-between gap-3"><span>Payment method</span><span
                        class="font-medium uppercase">{{
                        $receiptData['payment_method'] ?? '-' }}</span></div>
                <div class="flex justify-between gap-3"><span>Cashier</span><span class="font-medium">{{
                        $receiptData['cashier_name'] ?? '-' }}</span></div>
                <div class="flex justify-between gap-3 border-t pt-3"><span>Total</span><span
                        class="text-base font-semibold">$ {{ number_format((float) ($receiptData['total_price'] ?? 0),
                        0,
                        ',', '.') }}</span></div>
            </div>
            <div class="mt-4 space-y-1 text-sm">
                @foreach($receiptData['items'] ?? [] as $item)
                <div class="flex justify-between gap-3"><span>{{ $item['quantity'] }}&times; {{ $item['product_name']
                        }}</span><span>$ {{ number_format((float) $item['subtotal'], 0, ',', '.') }}</span></div>
                @endforeach
            </div>
            <div class="mt-6 flex justify-end gap-3"><button type="button" wire:click="closeReceiptDialog"
                    class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted cursor-pointer">Close</button><a
                    href="{{ $receiptData['receipt_url'] ?? '#' }}" target="_blank" rel="noopener"
                    class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:opacity-90 cursor-pointer">Print
                    / Open Receipt</a></div>
        </div>
    </div>
    @endif
</div>

@script
<script>
    const subscribeToKasirOrders = () => {
        if (window.__ordoraKasirOrdersChannel || !window.Echo) return;

        const channel = window.Echo.private('kasir-orders');
        window.__ordoraKasirOrdersChannel = channel;

        channel.listen('.order.status.updated', (payload) => {
            $wire.$dispatch('order-board-refresh');
            window.dispatchEvent(new CustomEvent('order-realtime', { detail: payload }));
        });
    };

    subscribeToKasirOrders();
    window.addEventListener('ordora-echo-ready', subscribeToKasirOrders);
</script>
@endscript