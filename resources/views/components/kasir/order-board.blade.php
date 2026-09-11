<?php

use App\Actions\PayOrderAction;
use App\Actions\UpdateOrderStatusAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
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

    public function activeStatuses(): array
    {
        return [OrderStatus::Pending, OrderStatus::Preparing, OrderStatus::Ready, OrderStatus::Served];
    }

    public function statusMeta(): array
    {
        return [
            'pending' => ['label' => 'Pending', 'description' => 'Pesanan baru', 'dot' => 'bg-amber-500', 'surface' => 'bg-amber-500/10', 'line' => 'border-amber-500/30'],
            'preparing' => ['label' => 'Preparing', 'description' => 'Sedang diproses', 'dot' => 'bg-blue-500', 'surface' => 'bg-blue-500/10', 'line' => 'border-blue-500/30'],
            'ready' => ['label' => 'Ready', 'description' => 'Siap diantar', 'dot' => 'bg-emerald-500', 'surface' => 'bg-emerald-500/10', 'line' => 'border-emerald-500/30'],
            'served' => ['label' => 'Served', 'description' => 'Menunggu pembayaran', 'dot' => 'bg-violet-500', 'surface' => 'bg-violet-500/10', 'line' => 'border-violet-500/30'],
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
            throw ValidationException::withMessages(['status' => 'Status tujuan tidak valid.']);
        }

        DB::transaction(function () use ($orderId, $target, $user): void {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);

            if ($target === OrderStatus::Paid) {
                if ($order->status !== OrderStatus::Served) {
                    throw ValidationException::withMessages(['status' => 'Order hanya dapat dibayar setelah served.']);
                }

                (new PayOrderAction())->execute($order, PaymentMethod::Cash, $user->id);
                return;
            }

            if ($order->status === OrderStatus::Paid || ! in_array($target, $this->activeStatuses(), true)) {
                throw ValidationException::withMessages(['status' => 'Status tujuan tidak valid untuk order ini.']);
            }

            (new UpdateOrderStatusAction())->execute($order, $target, $user->id);
        });

        $this->refreshBoard();
        $this->dispatch('order-board-toast', message: 'Status order berhasil diperbarui.');
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
};
?>

<script>
    window.orderBoardRealtimeState = window.orderBoardRealtimeState || (() => ({
        now: new Date(),
        timer: null,
        soundEnabled: window.localStorage.getItem('ordora.soundEnabled') === 'true',
        toastMessage: '',
        toastTimer: null,
        realtimeHandler: null,
        formattedNow() {
            return this.now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
        },
        init() {
            this.timer = window.setInterval(() => { this.now = new Date(); }, 1000);
            this.realtimeHandler = (event) => this.handleRealtime(event.detail);
            window.addEventListener('order-realtime', this.realtimeHandler);
        },
        destroy() {
            window.clearInterval(this.timer);
            window.removeEventListener('order-realtime', this.realtimeHandler);
            window.clearTimeout(this.toastTimer);
        },
        toggleSound() {
            this.soundEnabled = !this.soundEnabled;
            window.localStorage.setItem('ordora.soundEnabled', String(this.soundEnabled));
            if (this.soundEnabled) this.playSound();
        },
        playSound() {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const context = new AudioContext();
            const oscillator = context.createOscillator();
            const gain = context.createGain();
            oscillator.type = 'sine';
            oscillator.frequency.value = 880;
            gain.gain.setValueAtTime(0.0001, context.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.12, context.currentTime + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.18);
            oscillator.connect(gain);
            gain.connect(context.destination);
            oscillator.start();
            oscillator.stop(context.currentTime + 0.2);
        },
        handleRealtime(payload) {
            const order = payload?.order;
            if (!order) return;
            this.toastMessage = payload.change_type === 'created'
                ? `Pesanan baru #${order.id} masuk.`
                : `Order #${order.id} berubah ke ${order.status}.`;
            window.clearTimeout(this.toastTimer);
            this.toastTimer = window.setTimeout(() => { this.toastMessage = ''; }, 4200);
            if (payload.change_type === 'created' && this.soundEnabled) this.playSound();
        },
    }));
</script>

<div data-order-board x-data="orderBoardRealtimeState()" x-init="init()" class="space-y-6">
    <div x-show="toastMessage" x-cloak x-transition class="fixed right-4 top-4 z-50 rounded-xl border bg-card px-4 py-3 text-sm shadow-lg" role="status"><span x-text="toastMessage"></span></div>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div><p class="text-sm font-medium text-primary">Kasir workspace</p><h1 class="mt-1 text-3xl font-semibold tracking-tight">Live order board</h1><p class="mt-2 text-sm text-muted-foreground">Pantau dan proses pesanan pelanggan secara realtime.</p></div>
        <div class="flex items-center gap-3 rounded-xl border bg-card px-4 py-3 text-sm"><span class="size-2 rounded-full bg-emerald-500"></span><span class="font-medium" x-text="formattedNow()"></span><button type="button" class="ml-2 inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-medium hover:bg-muted" @click="toggleSound()"><span x-text="soundEnabled ? '🔔' : '🔕'"></span><span x-text="soundEnabled ? 'Sound On' : 'Sound Off'"></span></button></div>
    </div>

    <div class="flex gap-1 border-b"><button type="button" @click="$wire.setTab('orders')" class="border-b-2 px-4 py-3 text-sm font-medium" :class="$wire.activeTab === 'orders' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground'">Orders</button><button type="button" @click="$wire.setTab('history')" class="border-b-2 px-4 py-3 text-sm font-medium" :class="$wire.activeTab === 'history' ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground'">History</button></div>

    <div class="flex flex-col gap-3 md:flex-row"><label class="relative flex-1"><span class="sr-only">Cari order</span><input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari order, customer, atau meja..." class="w-full rounded-xl border bg-card px-4 py-3 text-sm outline-none ring-primary/30 focus:ring-4"></label><select wire:model.live="statusFilter" class="rounded-xl border bg-card px-4 py-3 text-sm outline-none focus:ring-4 focus:ring-primary/30"><option value="all">Semua status</option>@foreach($this->activeStatuses() as $status)<option value="{{ $status->value }}">{{ $this->statusMeta()[$status->value]['label'] }}</option>@endforeach</select></div>
    <div wire:loading wire:target="moveOrder" class="rounded-lg border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-primary">Memperbarui status order...</div>
    @error('status')<div class="rounded-lg border border-destructive/30 bg-destructive/5 px-4 py-3 text-sm text-destructive">{{ $message }}</div>@enderror

    @if($activeTab === 'orders')
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @php($kpiCards = [['label' => 'Active orders', 'value' => $this->kpis['active_orders'], 'icon' => '↗'], ['label' => 'Meja occupied', 'value' => $this->kpis['occupied_tables'], 'icon' => '⌂'], ['label' => 'Pending orders', 'value' => $this->kpis['pending_orders'], 'icon' => '◷'], ['label' => 'Revenue hari ini', 'value' => 'Rp '.number_format($this->kpis['today_revenue'], 0, ',', '.'), 'icon' => 'Rp']])
            @foreach($kpiCards as $card)<div class="rounded-2xl border bg-card p-4 shadow-sm"><div class="flex items-center justify-between text-sm text-muted-foreground"><span>{{ $card['label'] }}</span><span>{{ $card['icon'] }}</span></div><p class="mt-3 text-2xl font-semibold tracking-tight">{{ $card['value'] }}</p></div>@endforeach
        </div>

        <div class="grid gap-4 xl:grid-cols-4">
            @foreach($this->activeStatuses() as $status)
                @php($meta = $this->statusMeta()[$status->value]) @php($columnOrders = $this->orderColumns[$status->value] ?? collect())
                <section class="min-h-[26rem] rounded-2xl border {{ $meta['line'] }} bg-muted/30 p-3"><div class="mb-3 flex items-start justify-between"><div><div class="flex items-center gap-2"><span class="size-2.5 rounded-full {{ $meta['dot'] }}"></span><h2 class="font-semibold">{{ $meta['label'] }}</h2></div><p class="mt-1 text-xs text-muted-foreground">{{ $meta['description'] }}</p></div><span class="rounded-full {{ $meta['surface'] }} px-2.5 py-1 text-xs font-semibold">{{ $columnOrders->count() }}</span></div>
                    <div class="space-y-3" data-order-column="{{ $status->value }}">@forelse($columnOrders as $order)<article data-order-id="{{ $order->id }}" class="cursor-grab rounded-xl border bg-card p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md active:cursor-grabbing" wire:key="order-{{ $order->id }}"><div class="flex items-start justify-between gap-3"><div><p class="font-semibold">#{{ $order->id }}</p><p class="mt-1 text-xs text-muted-foreground">{{ $order->customer_name }}</p></div><span class="rounded-md bg-muted px-2 py-1 text-xs font-medium">Meja {{ $order->table?->number ?? '-' }}</span></div><p class="mt-3 text-xs text-muted-foreground">{{ $order->created_at?->format('d M Y, H:i') }}</p><div class="mt-3 space-y-1 border-y py-3 text-sm">@foreach($order->orderItems as $item)<div class="flex justify-between gap-3"><span class="truncate">{{ $item->quantity }}× {{ $item->product->name }}</span><span class="shrink-0 text-muted-foreground">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span></div>@endforeach</div><div class="mt-3 flex items-center justify-between gap-2"><span class="text-sm font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>@if($status !== App\Enums\OrderStatus::Served)<button type="button" wire:click="moveOrder({{ $order->id }}, '{{ $this->nextStatus($status)->value }}')" wire:loading.attr="disabled" class="rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-primary-foreground hover:opacity-90">{{ $this->statusMeta()[$this->nextStatus($status)->value]['label'] }}</button>@else<button type="button" wire:click="moveOrder({{ $order->id }}, 'paid')" wire:loading.attr="disabled" class="rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-primary-foreground hover:opacity-90">Bayar cash</button>@endif</div></article>@empty<div class="rounded-xl border border-dashed p-6 text-center text-xs text-muted-foreground">Belum ada order.</div>@endforelse</div>
                </section>
            @endforeach
        </div>
    @else
        <div class="rounded-2xl border bg-card p-4 shadow-sm"><div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end"><label class="flex-1 text-xs font-medium">Dari<input wire:model.live="historyFrom" type="date" class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm"></label><label class="flex-1 text-xs font-medium">Sampai<input wire:model.live="historyTo" type="date" class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm"></label></div><div class="overflow-x-auto"><table class="w-full min-w-[42rem] text-left text-sm"><thead class="border-b text-xs uppercase text-muted-foreground"><tr><th class="px-3 py-3">Order</th><th class="px-3 py-3">Customer</th><th class="px-3 py-3">Meja</th><th class="px-3 py-3">Items</th><th class="px-3 py-3 text-right">Total</th></tr></thead><tbody class="divide-y">@forelse($this->historyOrders as $order)<tr><td class="px-3 py-3 font-semibold">#{{ $order->id }}<div class="text-xs font-normal text-muted-foreground">{{ $order->updated_at?->format('d M Y, H:i') }}</div></td><td class="px-3 py-3">{{ $order->customer_name }}</td><td class="px-3 py-3">{{ $order->table?->number ?? '-' }}</td><td class="px-3 py-3">{{ $order->orderItems->sum('quantity') }} item</td><td class="px-3 py-3 text-right font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td></tr>@empty<tr><td colspan="5" class="px-3 py-12 text-center text-muted-foreground">Belum ada history pembayaran.</td></tr>@endforelse</tbody></table></div></div>
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
