<div data-order-history class="space-y-6">
    <div>
        <h1 class="mt-1 text-3xl font-semibold tracking-tight">Payment History</h1>
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
                            '.') }}</td>
                    </tr>@empty<tr>
                        <td colspan="5" class="px-3 py-12 text-center text-muted-foreground">No payment history yet.
                        </td>
                    </tr>@endforelse</tbody>
            </table>
        </div>
    </div>
</div>