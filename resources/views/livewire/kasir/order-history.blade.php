<div data-order-history class="space-y-6">
    <div>
        <h1 class="mt-1 text-3xl font-semibold tracking-tight">Order History</h1>
        <p class="mt-2 text-sm text-muted-foreground">View completed orders and payments.</p>
    </div>

    <div class="flex flex-col gap-3 md:flex-row">
        <label class="relative flex-1">
            <span class="sr-only">Search orders</span>
            <input wire:model.live.debounce.300ms="search" type="search"
                placeholder="Search orders, customers, or tables..."
                class="w-full rounded-xl border bg-card px-4 py-3 text-sm outline-none ring-primary/30 focus:ring-4">
        </label>
        <div class="flex items-center gap-2">
            <label class="text-xs font-medium">From</label>
            <input wire:model.live="historyFrom" type="date"
                class="rounded-xl border bg-card px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-primary/30">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-xs font-medium">To</label>
            <input wire:model.live="historyTo" type="date"
                class="rounded-xl border bg-card px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-primary/30">
        </div>
    </div>

    <div class="rounded-2xl border bg-card p-4 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[42rem] text-left text-sm">
                <thead class="border-b text-xs uppercase text-muted-foreground">
                    <tr>
                        <th class="px-3 py-3">Order</th>
                        <th class="px-3 py-3">Customer</th>
                        <th class="px-3 py-3">Table</th>
                        <th class="px-3 py-3">Items</th>
                        <th class="px-3 py-3 text-right">Total</th>
                        <th class="px-3 py-3">Payment</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($this->historyOrders as $order)
                    <tr>
                        <td class="px-3 py-3 font-semibold">#{{ $order->id }}
                            <div class="text-xs font-normal text-muted-foreground">
                                {{ $order->paid_at?->format('d M Y, H:i') }}
                            </div>
                        </td>
                        <td class="px-3 py-3">{{ $order->customer_name }}</td>
                        <td class="px-3 py-3">{{ $order->table?->number ?? '-' }}</td>
                        <td class="px-3 py-3">{{ $order->orderItems->sum('quantity') }} item(s)</td>
                        <td class="px-3 py-3 text-right font-semibold">
                            $ {{ number_format($order->total_price, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-3">{{ $order->payment_method?->label() ?? '-' }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                    @if($order->status === \App\Enums\OrderStatus::Paid) bg-green-100 text-green-700
                                    @elseif($order->status === \App\Enums\OrderStatus::Pending) bg-yellow-100 text-yellow-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                {{ $order->status->label() }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-right">
                            @if($order->paid_at)
                            <button wire:click="openReceipt({{ $order->id }})"
                                class="inline-flex items-center rounded-lg bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground hover:bg-primary/90">
                                Print
                            </button>
                            @else
                            <span class="text-xs text-muted-foreground">-</span>
                            @endif
                        </td>
                    </tr>@empty<tr>
                        <td colspan="8" class="px-3 py-12 text-center text-muted-foreground">
                            No order history yet.
                        </td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($selectedOrder)
    <x-modal id="receipt-modal" title="Receipt #{{ $selectedOrder->id }}" maxWidth="max-w-lg">
        <div id="receipt-content" class="space-y-4">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-xs text-muted-foreground">Order</p>
                    <p class="font-semibold">#{{ $selectedOrder->id }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-muted-foreground">Paid at</p>
                    <p class="font-semibold">{{ $selectedOrder->paid_at?->format('d M Y, H:i') }}</p>
                </div>
            </div>
            <div class="border-y py-4">
                @foreach($selectedOrder->orderItems as $item)
                <div class="flex justify-between gap-4 text-sm">
                    <span>{{ $item->quantity }} × {{ $item->product->name }}</span>
                    <span>$ {{ number_format($item->subtotal, 0, '.', ',') }}</span>
                </div>
                @endforeach
            </div>
            <div class="flex justify-between text-base font-bold">
                <span>Total</span>
                <span>$ {{ number_format($selectedOrder->total_price, 0, '.', ',') }}</span>
            </div>
        </div>
        <div class="mt-6">
            <button onclick="window.print()"
                class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground hover:opacity-90 cursor-pointer">
                Print
            </button>
        </div>
    </x-modal>
    <div id="print-receipt">
        <div class="r-center">
            <div class="r-brand">{{ strtoupper(config('app.name')) }}</div>
            <div class="r-muted">Order #{{ $selectedOrder->id }}</div>
            <div class="r-muted">
                {{ ($selectedOrder->paid_at ?? $selectedOrder->created_at)?->format('d M Y, H:i') }}
            </div>
        </div>

        <div class="r-divider"></div>

        <div class="r-row"><span>Customer</span><span>{{ $selectedOrder->customer_name ?? '-' }}</span></div>
        <div class="r-row"><span>Table</span><span>{{ $selectedOrder->table?->number ?? '-' }}</span></div>

        <div class="r-divider"></div>

        @foreach($selectedOrder->orderItems as $item)
        <div class="r-bold">{{ $item->product->name }}</div>
        <div class="r-row r-muted">
            <span>{{ $item->quantity }} x $ {{ number_format($item->product->price, 2) }}</span>
            <span>$ {{ number_format($item->subtotal, 2) }}</span>
        </div>
        @endforeach

        <div class="r-divider"></div>

        <div class="r-row r-total">
            <span>TOTAL</span>
            <span>$ {{ number_format($selectedOrder->total_price, 2) }}</span>
        </div>
        <div class="r-row">
            <span>Payment</span>
            <span>{{ $selectedOrder->payment_method?->label() ?? '-' }}</span>
        </div>

        <div class="r-divider"></div>

        <div class="r-center r-muted">
            <p>Thank you for your order!</p>
        </div>
    </div>
    @endif
</div>
</div>
</div>