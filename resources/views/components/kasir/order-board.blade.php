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
    private const ACTIVE_STATUS_VALUES = ['pending', 'preparing', 'ready', 'served'];

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
        return [OrderStatus::Pending, OrderStatus::Preparing, OrderStatus::Ready, OrderStatus::Served];
    }

    public function statusMeta(): array
    {
        return [
            'pending' => ['label' => 'Pending', 'description' => 'New orders', 'dot' => 'bg-amber-500', 'surface' => 'bg-amber-500/10', 'line' => 'border-amber-500/30'],
            'preparing' => ['label' => 'Preparing', 'description' => 'In progress', 'dot' => 'bg-blue-500', 'surface' => 'bg-blue-500/10', 'line' => 'border-blue-500/30'],
            'ready' => ['label' => 'Ready', 'description' => 'Ready to serve', 'dot' => 'bg-emerald-500', 'surface' => 'bg-emerald-500/10', 'line' => 'border-emerald-500/30'],
            'served' => ['label' => 'Served', 'description' => 'Awaiting payment', 'dot' => 'bg-violet-500', 'surface' => 'bg-violet-500/10', 'line' => 'border-violet-500/30'],
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
            ->where('status', OrderStatus::Paid)
            ->latest('updated_at')->latest('id');

        $this->applySearch($query);

        if ($this->historyFrom !== '') {
            $query->whereDate('updated_at', '>=', $this->historyFrom);
        }

        if ($this->historyTo !== '') {
            $query->whereDate('updated_at', '<=', $this->historyTo);
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
            'today_revenue' => Order::where('status', OrderStatus::Paid)->whereDate('updated_at', today())->sum('total_price'),
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

        if ($target === OrderStatus::Paid) {
            $this->openPaymentDialog($orderId, PaymentMethod::Cash->value);

            return;
        }

        DB::transaction(function () use ($orderId, $target, $user): void {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);

            if ($order->status === OrderStatus::Paid || ! in_array($target, $this->activeStatuses(), true)) {
                throw ValidationException::withMessages(['status' => 'The target status is invalid for this order.']);
            }

            (new UpdateOrderStatusAction())->execute($order, $target, $user->id);
        });

        $this->refreshBoard();
        $this->dispatch('order-board-toast', message: 'Order status updated successfully.');
    }

    public function openPaymentDialog(int $orderId, ?string $method = null): void
    {
        abort_unless(auth()->user()?->role === UserRole::Kasir, 403);

        $selectedMethod = PaymentMethod::tryFrom($method ?? PaymentMethod::Cash->value);
        if (! $selectedMethod) {
            $this->paymentError = 'The payment method is invalid.';

            return;
        }

        $order = Order::query()
            ->with(['table:id,number', 'orderItems.product:id,name'])
            ->findOrFail($orderId);

        if ($order->status !== OrderStatus::Served) {
            $this->paymentError = 'An order can only be paid after it reaches served status.';
            $this->refreshBoard();

            return;
        }

        $this->resetErrorBag();
        $this->paymentError = '';
        $this->paymentDialogOpen = true;
        $this->paymentStep = 'method';
        $this->paymentOrderId = $order->id;
        $this->paymentMethod = $selectedMethod->value;
        $this->qrisConfirmed = false;
        $this->paymentData = $this->serializeOrder($order);
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
        if (! $order || $order->status !== OrderStatus::Served) {
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

        if ($method === PaymentMethod::Qris && ! $this->qrisConfirmed) {
            $this->addError('qrisConfirmed', 'Confirm the QRIS payment first.');

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
            'paid_at' => $paidOrder->updated_at?->format('d M Y, H:i'),
            'receipt_url' => route('kasir.order.receipt', $paidOrder),
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
            OrderStatus::Served => OrderStatus::Paid,
            OrderStatus::Paid => null,
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
        class="fixed right-4 top-4 z-50 rounded-xl border bg-card px-4 py-3 text-sm shadow-lg" role="status"><span
            x-text="toastMessage"></span></div>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-primary">Cashier workspace</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight">Live order board</h1>
            <p class="mt-2 text-sm text-muted-foreground">Monitor and process customer orders in real time.</p>
        </div>
        <div class="flex items-center gap-3 rounded-xl border bg-card px-4 py-3 text-sm">
            <span class="size-2 rounded-full bg-emerald-500"></span>
            <span class="font-medium" x-text="formattedNow()"></span>
            <button type="button"
                class="ml-2 inline-flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-medium hover:bg-muted"
                @click="toggleSound()">
                <svg x-show="soundEnabled" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
                </svg>
                <svg x-show="!soundEnabled" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.143 17.082a24.248 24.248 0 0 0 3.844.148m-3.844-.148a23.856 23.856 0 0 1-5.455-1.31 8.964 8.964 0 0 0 2.3-5.542m3.155 6.852a3 3 0 0 0 5.667 1.97m1.965-2.277L21 21m-4.225-4.225a23.81 23.81 0 0 0 3.536-1.003A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6.53 6.53m10.245 10.245L6.53 6.53M3 3l3.53 3.53" />
                </svg>

                <span x-text="soundEnabled ? 'Sound On' : 'Sound Off'"></span>
            </button>
        </div>
    </div>

    <div class="flex gap-1 border-b"><button type="button" wire:click="setTab('orders')"
            class="border-b-2 px-4 py-3 text-sm font-medium cursor-pointer {{ $activeTab === 'orders' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground' }}">Orders</button><button
            type="button" wire:click="setTab('history')"
            class="border-b-2 px-4 py-3 text-sm font-medium cursor-pointer {{ $activeTab === 'history' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground' }}">History</button>
    </div>

    <div class="flex flex-col gap-3 md:flex-row"><label class="relative flex-1"><span class="sr-only">Search
                orders</span><input wire:model.live.debounce.300ms="search" type="search"
                placeholder="Search orders, customers, or tables..."
                class="w-full rounded-xl border bg-card px-4 py-3 text-sm outline-none ring-primary/30 focus:ring-4"></label><select
            wire:model.live="statusFilter"
            class="rounded-xl border bg-card px-4 py-3 text-sm outline-none focus:ring-4 focus:ring-primary/30">
            <option value="all">All statuses</option>@foreach($this->activeStatuses() as $status)<option
                value="{{ $status->value }}">{{ $this->statusMeta()[$status->value]['label'] }}</option>@endforeach
        </select></div>
    <div wire:loading wire:target="moveOrder,confirmPayment"
        class="rounded-lg border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-primary">Updating order...</div>
    @error('status')<div
        class="rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive">{{ $message
        }}</div>@enderror
    @if($paymentError !== '')<div
        class="rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive">{{
        $paymentError }}</div>@endif

    @if($activeTab === 'orders')
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @if(false)
        @php($kpiCards = [['label' => 'Active orders', 'value' => $this->kpis['active_orders'], 'icon' => '↗'], ['label'
        => 'Meja occupied', 'value' => $this->kpis['occupied_tables'], 'icon' => '⌂'], ['label' => 'Pending orders',
        'value' => $this->kpis['pending_orders'], 'icon' => '◷'], ['label' => 'Revenue hari ini', 'value' => 'Rp
        '.number_format($this->kpis['today_revenue'], 0, ',', '.'), 'icon' => 'Rp']])
        @foreach($kpiCards as $card)<div class="rounded-2xl border bg-card p-4 shadow-sm">
            <div class="flex items-center justify-between text-sm text-muted-foreground"><span>{{ $card['label']
                    }}</span><span>{{ $card['icon'] }}</span></div>
            <p class="mt-3 text-2xl font-semibold tracking-tight">{{ $card['value'] }}</p>
        </div>@endforeach
    </div>
    @endif
</div>
@php($kpiCards = [['label' => 'Active orders', 'value' => $this->kpis['active_orders'], 'icon' => '↗'], ['label' =>
'Occupied tables', 'value' => $this->kpis['occupied_tables'], 'icon' => '⌂'], ['label' => 'Pending orders', 'value' =>
$this->kpis['pending_orders'], 'icon' => '◷'], ['label' => "Today's revenue", 'value' => 'Rp
'.number_format($this->kpis['today_revenue'], 0, ',', '.'), 'icon' => 'Rp']])
<div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    @foreach($kpiCards as $card)
    <div class="rounded-2xl border bg-card p-4 shadow-sm">
        <div class="flex items-center justify-between text-sm text-muted-foreground"><span>{{ $card['label']
                }}</span><span>{{ $card['icon'] }}</span></div>
        <p class="mt-3 text-2xl font-semibold tracking-tight">{{ $card['value'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid gap-4 xl:grid-cols-4">
    @foreach($this->activeStatuses() as $status)
    @php($meta = $this->statusMeta()[$status->value]) @php($columnOrders = $this->orderColumns[$status->value] ??
    collect())
    <section class="min-h-[26rem] rounded-2xl border {{ $meta['line'] }} bg-muted/30 p-3">
        <div class="mb-3 flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2"><span class="size-2.5 rounded-full {{ $meta['dot'] }}"></span>
                    <h2 class="font-semibold">{{ $meta['label'] }}</h2>
                </div>
                <p class="mt-1 text-xs text-muted-foreground">{{ $meta['description'] }}</p>
            </div><span class="rounded-full {{ $meta['surface'] }} px-2.5 py-1 text-xs font-semibold">{{
                $columnOrders->count() }}</span>
        </div>
        <div class="space-y-3" data-order-column="{{ $status->value }}">
            @forelse($columnOrders as $order)
            <article data-order-id="{{ $order->id }}"
                class="cursor-grab rounded-xl border bg-card p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md active:cursor-grabbing"
                wire:key="order-{{ $order->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold">#{{ $order->id }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">{{ $order->customer_name }}</p>
                    </div><span class="rounded-md bg-muted px-2 py-1 text-xs font-medium">Table {{
                        $order->table?->number ?? '-' }}</span>
                </div>
                <p class="mt-3 text-xs text-muted-foreground">{{ $order->created_at?->format('d M Y, H:i') }}</p>
                <div class="mt-3 space-y-1 border-y py-3 text-sm">@foreach($order->orderItems as $item)<div
                        class="flex justify-between gap-3"><span class="truncate">{{ $item->quantity }}&times; {{
                            $item->product->name }}</span><span class="shrink-0 text-muted-foreground">Rp {{
                            number_format($item->subtotal, 0, ',', '.') }}</span></div>@endforeach</div>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-2"><span
                        class="text-sm font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.')
                        }}</span>@if($status !== App\Enums\OrderStatus::Served)<button type="button"
                        wire:click="moveOrder({{ $order->id }}, '{{ $this->nextStatus($status)->value }}')"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-primary-foreground hover:opacity-90 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50">{{
                        $this->statusMeta()[$this->nextStatus($status)->value]['label'] }}</button>@else<div
                        class="flex flex-wrap justify-end gap-2"><button type="button"
                            wire:click="openPaymentDialog({{ $order->id }})" wire:loading.attr="disabled"
                            class="rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground hover:opacity-90 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50">Pay</button>
                    </div>@endif</div>
            </article>
            @empty
            <div class="rounded-xl border border-dashed p-6 text-center text-xs text-muted-foreground">No orders yet.
            </div>
            @endforelse
        </div>
    </section>
    @endforeach
</div>
@else
@if(false)
<div class="rounded-2xl border bg-card p-4 shadow-sm">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end"><label class="flex-1 text-xs font-medium">Dari<input
                wire:model.live="historyFrom" type="date"
                class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm"></label><label
            class="flex-1 text-xs font-medium">Sampai<input wire:model.live="historyTo" type="date"
                class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm"></label></div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[42rem] text-left text-sm">
            <thead class="border-b text-xs uppercase text-muted-foreground">
                <tr>
                    <th class="px-3 py-3">Order</th>
                    <th class="px-3 py-3">Customer</th>
                    <th class="px-3 py-3">Meja</th>
                    <th class="px-3 py-3">Items</th>
                    <th class="px-3 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y">@forelse($this->historyOrders as $order)<tr>
                    <td class="px-3 py-3 font-semibold">#{{ $order->id }}<div
                            class="text-xs font-normal text-muted-foreground">{{ $order->updated_at?->format('d M Y,
                            H:i') }}</div>
                    </td>
                    <td class="px-3 py-3">{{ $order->customer_name }}</td>
                    <td class="px-3 py-3">{{ $order->table?->number ?? '-' }}</td>
                    <td class="px-3 py-3">{{ $order->orderItems->sum('quantity') }} item</td>
                    <td class="px-3 py-3 text-right font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.')
                        }}</td>
                </tr>@empty<tr>
                    <td colspan="5" class="px-3 py-12 text-center text-muted-foreground">Belum ada history pembayaran.
                    </td>
                </tr>@endforelse</tbody>
        </table>
    </div>
</div>
@endif
<div class="rounded-2xl border bg-card p-4 shadow-sm">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end"><label class="flex-1 text-xs font-medium">From<input
                wire:model.live="historyFrom" type="date"
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
                            class="text-xs font-normal text-muted-foreground">{{ $order->updated_at?->format('d M Y,
                            H:i') }}</div>
                    </td>
                    <td class="px-3 py-3">{{ $order->customer_name }}</td>
                    <td class="px-3 py-3">{{ $order->table?->number ?? '-' }}</td>
                    <td class="px-3 py-3">{{ $order->orderItems->sum('quantity') }} item(s)</td>
                    <td class="px-3 py-3 text-right font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.')
                        }}</td>
                </tr>@empty<tr>
                    <td colspan="5" class="px-3 py-12 text-center text-muted-foreground">No payment history yet.</td>
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
                <h2 id="payment-dialog-title" class="mt-1 text-xl font-semibold">{{ $paymentStep === 'method' ? 'Choose
                    payment method' : 'Confirm payment' }}</h2>
            </div>
            <button type="button" wire:click="cancelPayment"
                class="rounded-lg p-2 text-muted-foreground hover:bg-muted cursor-pointer"
                aria-label="Close">&times;</button>
        </div>
        @if($paymentData !== [])
        <div class="mt-5 rounded-xl border bg-muted/30 p-4">
            <div class="flex justify-between gap-3 text-sm"><span>{{ $paymentData['customer_name'] }}</span><span>Table
                    {{ $paymentData['table_number'] ?? '-' }}</span></div>
            <div class="mt-3 space-y-1 border-t pt-3 text-sm">@foreach($paymentData['items'] as $item)<div
                    class="flex justify-between gap-3"><span>{{ $item['quantity'] }}&times; {{ $item['product_name']
                        }}</span><span>Rp {{ number_format((float) $item['subtotal'], 0, ',', '.') }}</span></div>
                @endforeach</div>
            <div class="mt-3 flex justify-between border-t pt-3 text-base font-semibold"><span>Total</span><span>Rp {{
                    number_format((float) $paymentData['total_price'], 0, ',', '.') }}</span></div>
        </div>
        @endif
        @if($paymentStep === 'method')
        <p class="mt-5 text-sm text-muted-foreground">Select the payment method received from the customer.</p>
        <div class="mt-3 grid grid-cols-2 gap-3"><button type="button" wire:click="selectPaymentMethod('cash')"
                class="rounded-xl border p-4 text-left cursor-pointer {{ $paymentMethod === 'cash' ? 'border-primary bg-primary/10 ring-2 ring-primary/20' : 'hover:bg-muted' }}"><span
                    class="block font-semibold">Cash</span><span class="mt-1 block text-xs text-muted-foreground">Cash
                    payment</span></button><button type="button" wire:click="selectPaymentMethod('qris')"
                class="rounded-xl border p-4 text-left cursor-pointer {{ $paymentMethod === 'qris' ? 'border-primary bg-primary/10 ring-2 ring-primary/20' : 'hover:bg-muted' }}"><span
                    class="block font-semibold">QRIS</span><span class="mt-1 block text-xs text-muted-foreground">Manual
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
                    }}</strong>.</p>@if($paymentMethod === 'qris')<label class="mt-4 flex items-start gap-3"><input
                    wire:model.live="qrisConfirmed" type="checkbox"
                    class="mt-0.5 rounded border-amber-500 text-primary focus:ring-primary"><span>I have received and
                    manually verified the QRIS payment.</span></label>@endif
        </div>
        @error('qrisConfirmed')<p class="mt-2 text-sm text-destructive">{{ $message }}</p>@enderror
        @if($paymentError !== '')<p class="mt-2 text-sm text-destructive">{{ $paymentError }}</p>@endif
        <div class="mt-6 flex justify-between gap-3"><button type="button" wire:click="$set('paymentStep', 'method')"
                class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted cursor-pointer">Back</button>
            <div class="flex gap-3"><button type="button" wire:click="cancelPayment"
                    class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted cursor-pointer">Cancel</button><button
                    type="button" wire:click="confirmPayment" wire:loading.attr="disabled" wire:target="confirmPayment"
                    @disabled($paymentMethod==='qris' && ! $qrisConfirmed)
                    class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:opacity-90 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50"><span
                        wire:loading.remove wire:target="confirmPayment">Confirm Payment</span><span wire:loading
                        wire:target="confirmPayment">Processing...</span></button></div>
        </div>
        @endif
    </div>
</div>
@endif

@if(false && $paymentDialogOpen)
@if($paymentData !== [])<div class="mt-5 rounded-xl border bg-muted/30 p-4">
    <div class="flex justify-between gap-3 text-sm"><span>{{ $paymentData['customer_name'] }}</span><span>Meja {{
            $paymentData['table_number'] ?? '-' }}</span></div>
    <div class="mt-3 space-y-1 border-t pt-3 text-sm">@foreach($paymentData['items'] as $item)<div
            class="flex justify-between gap-3"><span>{{ $item['quantity'] }}&times; {{ $item['product_name']
                }}</span><span>Rp {{ number_format((float) $item['subtotal'], 0, ',', '.') }}</span></div>@endforeach
    </div>
    <div class="mt-3 flex justify-between border-t pt-3 text-base font-semibold"><span>Total</span><span>Rp {{
            number_format((float) $paymentData['total_price'], 0, ',', '.') }}</span></div>
</div>@endif
@if($paymentStep === 'method')<p class="mt-5 text-sm text-muted-foreground">Pilih metode yang diterima dari customer.
</p>
<div class="mt-3 grid grid-cols-2 gap-3"><button type="button" wire:click="selectPaymentMethod('cash')"
        class="rounded-xl border p-4 text-left {{ $paymentMethod === 'cash' ? 'border-primary bg-primary/10 ring-2 ring-primary/20' : 'hover:bg-muted' }}"><span
            class="block font-semibold">Cash</span><span class="mt-1 block text-xs text-muted-foreground">Pembayaran
            tunai</span></button><button type="button" wire:click="selectPaymentMethod('qris')"
        class="rounded-xl border p-4 text-left {{ $paymentMethod === 'qris' ? 'border-primary bg-primary/10 ring-2 ring-primary/20' : 'hover:bg-muted' }}"><span
            class="block font-semibold">QRIS</span><span class="mt-1 block text-xs text-muted-foreground">Konfirmasi
            manual kasir</span></button></div>@error('paymentMethod')<p class="mt-2 text-sm text-destructive">{{
    $message }}</p>@enderror<div class="mt-6 flex justify-end gap-3"><button type="button" wire:click="cancelPayment"
        class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted">Batal</button><button type="button"
        wire:click="continuePayment"
        class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:opacity-90">Lanjutkan</button>
</div>@else<div class="mt-5 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
    <p>Order akan ditandai sebagai <strong>paid</strong> menggunakan <strong>{{ strtoupper($paymentMethod) }}</strong>.
    </p>@if($paymentMethod === 'qris')<label class="mt-4 flex items-start gap-3"><input wire:model.live="qrisConfirmed"
            type="checkbox" class="mt-0.5 rounded border-amber-500 text-primary focus:ring-primary"><span>Saya sudah
            menerima dan memverifikasi pembayaran QRIS secara manual.</span></label>@endif
</div>@error('qrisConfirmed')<p class="mt-2 text-sm text-destructive">{{ $message }}</p>@enderror@if($paymentError !==
'')<p class="mt-2 text-sm text-destructive">{{ $paymentError }}</p>@endif<div class="mt-6 flex justify-between gap-3">
    <button type="button" wire:click="$set('paymentStep', 'method')"
        class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted">Kembali</button>
    <div class="flex gap-3"><button type="button" wire:click="cancelPayment"
            class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted">Batal</button><button type="button"
            wire:click="confirmPayment" wire:loading.attr="disabled" wire:target="confirmPayment"
            @disabled($paymentMethod==='qris' && ! $qrisConfirmed)
            class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:opacity-90"><span
                wire:loading.remove wire:target="confirmPayment">Konfirmasi Pembayaran</span><span wire:loading
                wire:target="confirmPayment">Memproses...</span></button></div>
</div>@endif
</div>
</div>
@endif

@if($receiptDialogOpen)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:click.self="closeReceiptDialog"
    wire:keydown.escape="closeReceiptDialog">
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
        <div class="mt-5 space-y-3 rounded-xl border bg-muted/30 p-4 text-sm">
            <div class="flex justify-between gap-3"><span>Customer</span><span class="font-medium">{{
                    $receiptData['customer_name'] ?? '-' }}</span></div>
            <div class="flex justify-between gap-3"><span>Table</span><span class="font-medium">{{
                    $receiptData['table_number'] ?? '-' }}</span></div>
            <div class="flex justify-between gap-3"><span>Payment method</span><span class="font-medium uppercase">{{
                    $receiptData['payment_method'] ?? '-' }}</span></div>
            <div class="flex justify-between gap-3"><span>Cashier</span><span class="font-medium">{{
                    $receiptData['cashier_name'] ?? '-' }}</span></div>
            <div class="flex justify-between gap-3 border-t pt-3"><span>Total</span><span
                    class="text-base font-semibold">Rp {{ number_format((float) ($receiptData['total_price'] ?? 0), 0,
                    ',', '.') }}</span></div>
        </div>
        <div class="mt-4 space-y-1 text-sm">@foreach($receiptData['items'] ?? [] as $item)<div
                class="flex justify-between gap-3"><span>{{ $item['quantity'] }}&times; {{ $item['product_name']
                    }}</span><span>Rp {{ number_format((float) $item['subtotal'], 0, ',', '.') }}</span></div>
            @endforeach</div>
        <div class="mt-6 flex justify-end gap-3"><button type="button" wire:click="closeReceiptDialog"
                class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted cursor-pointer">Close</button><a
                href="{{ $receiptData['receipt_url'] ?? '#' }}" target="_blank" rel="noopener"
                class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:opacity-90 cursor-pointer">Print
                / Open Receipt</a></div>
    </div>
</div>
@endif

@if(false && $receiptDialogOpen)
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