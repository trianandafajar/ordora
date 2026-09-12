@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Dashboard Admin</h1>
            <p class="text-sm text-muted-foreground mt-1">Overview of your coffee shop operations and revenue.</p>
        </div>
        <div class="flex items-center gap-2">
            <span x-data="{
                time: @js(now()->format('d M Y, H:i:s')),
                startClock() {
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    setInterval(() => {
                        const d = new Date();
                        this.time = String(d.getDate()).padStart(2, '0') + ' ' + months[d.getMonth()] + ' ' + d.getFullYear() + ', ' +
                            String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0') + ':' + String(d.getSeconds()).padStart(2, '0');
                    }, 1000);
                }
            }" x-init="startClock()" x-text="time"
                class="text-xs bg-muted text-muted-foreground px-3 py-1.5 rounded-md font-medium tabular-nums">
                {{ now()->format('d M Y, H:i:s') }}
            </span>
        </div>
    </div>

    <!-- stats cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
        $cardIcons = [
        'today_revenue' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75
        4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0
        .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125
        1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5
        1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12
        0h.008v.008H6V10.5Z',

        'pending_orders' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',

        'active_kasir' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0
        .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12
        0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681
        2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6
        3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z',

        'total_tables' => 'M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504
        1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504
        1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12
        18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504
        1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125
        1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125
        1.125-1.125 1.125m8.625-1.125c.621 0 1.125 1.125 1.125 1.125v1.5c0 .621-1.125 1.125-1.125 1.125m-17.25
        0h7.5m-7.5 0c-.621 0-1.125 1.125-1.125 1.125v1.5c0 .621 1.125 1.125 1.125 1.125m12 0v-1.5m0 1.5c0 .621-1.125
        1.125-1.125 1.125M12 10.875c0 .621 1.125 1.125 1.125 1.125m-2.25 0c.621 0 1.125 1.125 1.125 1.125M13.125
        12h7.5m-7.5 0c-.621 0-1.125 1.125-1.125 1.125M20.625 12c.621 0 1.125 1.125 1.125 1.125v1.5c0 .621-1.125
        1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-1.125 1.125-1.125 1.125M12 14.625c0 .621 1.125 1.125
        1.125 1.125m-2.25 0c.621 0 1.125 1.125 1.125 1.125m0 1.5v-1.5m0 0c0-.621 1.125-1.125 1.125-1.125m0 0h7.5',
        ];
        $cardLabels = [
        'today_revenue' => "Today's Revenue",
        'pending_orders' => 'Pending Orders',
        'active_kasir' => 'Active Cashiers',
        'total_tables' => 'Total Tables',
        ];
        $cardNotes = [
        'today_revenue' => 'Total paid orders today',
        'pending_orders' => 'Orders waiting to be processed',
        'active_kasir' => 'Cashiers online right now',
        'total_tables' => 'Registered tables in coffee shop',
        ];
        $cardValues = [
        'today_revenue' => '$ ' . number_format($stats['today_revenue'], 0, '.', ','),
        'pending_orders' => (string) $stats['pending_orders'],
        'active_kasir' => (string) $stats['active_kasir'],
        'total_tables' => (string) $stats['total_tables'],
        ];
        @endphp
        @foreach($cardLabels as $key => $label)
        <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-muted-foreground">{{ $label }}</span>
                <svg class="size-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cardIcons[$key] }}" />
                </svg>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold">{{ $cardValues[$key] }}</div>
                <p class="text-xs text-muted-foreground mt-1">{{ $cardNotes[$key] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <!-- quick links -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.categories.index') }}"
            class="rounded-xl border bg-card hover:bg-accent/50 transition-colors p-6 flex items-center gap-4">
            <div class="size-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>

            </div>
            <div>
                <h3 class="font-semibold text-sm">Categories</h3>
                <p class="text-xs text-muted-foreground">Manage product categories</p>
            </div>
        </a>
        <a href="{{ route('admin.products.index') }}"
            class="rounded-xl border bg-card hover:bg-accent/50 transition-colors p-6 flex items-center gap-4">
            <div class="size-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>

            </div>
            <div>
                <h3 class="font-semibold text-sm">Products</h3>
                <p class="text-xs text-muted-foreground">Manage coffee menu items</p>
            </div>
        </a>
        <a href="{{ route('admin.tables.index') }}"
            class="rounded-xl border bg-card hover:bg-accent/50 transition-colors p-6 flex items-center gap-4">
            <div class="size-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                </svg>

            </div>
            <div>
                <h3 class="font-semibold text-sm">Tables QR</h3>
                <p class="text-xs text-muted-foreground">Manage tables and QR tokens</p>
            </div>
        </a>
        <a href="{{ route('admin.cashiers.index') }}"
            class="rounded-xl border bg-card hover:bg-accent/50 transition-colors p-6 flex items-center gap-4">
            <div class="size-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>

            </div>
            <div>
                <h3 class="font-semibold text-sm">Cashier Accounts</h3>
                <p class="text-xs text-muted-foreground">Manage cashier users</p>
            </div>
        </a>
    </div>

    <!-- revenue & status -->
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border bg-card shadow-sm">
            <div class="flex items-center justify-between px-6 pt-6 pb-2">
                <div>
                    <h3 class="font-semibold">Revenue Trend</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Last 7 days total paid orders</p>
                </div>
            </div>
            <div class="p-6 pt-4">
                <div class="space-y-3">
                    @foreach($revenueData as $item)
                    <div class="flex items-center gap-4 text-xs">
                        <span class="w-16 text-muted-foreground">{{ $item['label'] }}</span>
                        <div class="flex-1 h-3 bg-muted rounded-full overflow-hidden">
                            <div class="bg-primary rounded-full h-full transition-all"
                                style="width: {{ round(($item['revenue'] / $maxRev) * 100) }}%"></div>
                        </div>
                        <span class="w-28 text-right font-medium">$ {{ number_format($item['revenue'], 0, '.', ',')
                            }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-card shadow-sm">
            <div class="flex items-center justify-between px-6 pt-6 pb-2">
                <div>
                    <h3 class="font-semibold">Order Status Distribution</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">All-time orders by current status</p>
                </div>
            </div>
            <div class="p-6 pt-4">
                <div class="space-y-3">
                    @foreach($statusCounts as $status => $count)
                    <div class="flex items-center gap-4 text-xs">
                        <span class="w-20 capitalize text-muted-foreground">{{ $status }}</span>
                        <div class="flex-1 h-3 bg-muted rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $status === 'paid' ? 'bg-green-500' : ($status === 'preparing' ? 'bg-amber-500' : ($status === 'ready' ? 'bg-blue-500' : ($status === 'served' ? 'bg-purple-500' : 'bg-red-500'))) }}"
                                style="width: {{ $total > 0 ? round(($count / $total) * 100) : 0 }}%"></div>
                        </div>
                        <span class="w-10 font-medium">{{ $count }}</span>
                        <span class="w-12 text-right text-muted-foreground">{{ $total > 0 ? round(($count / $total) *
                            100) : 0 }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- recent orders -->
    <div class="rounded-xl border bg-card shadow-sm">
        <div class="flex items-center justify-between px-6 pt-6 pb-2">
            <div>
                <h3 class="font-semibold">Recent Orders</h3>
                <p class="text-xs text-muted-foreground mt-0.5">Latest 5 orders from all tables</p>
            </div>
        </div>
        <div class="p-6 pt-4">
            @if(count($recentOrders) > 0)
            <div class="divide-y">
                @foreach($recentOrders as $order)
                <div class="flex items-center gap-4 py-3 text-sm first:pt-0 last:pb-0">
                    <div
                        class="w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $order->status->value === 'paid' ? 'bg-green-500' : ($order->status->value === 'preparing' ? 'bg-amber-500' : ($order->status->value === 'ready' ? 'bg-blue-500' : ($order->status->value === 'served' ? 'bg-purple-500' : 'bg-red-500'))) }}">
                    </div>
                    <span class="font-mono text-xs text-muted-foreground truncate flex-1">{{ $order->order_token
                        }}</span>
                    <span class="text-xs text-muted-foreground">{{ $order->table ? 'Table ' . $order->table->number :
                        '-' }}</span>
                    <span class="text-xs text-muted-foreground">{{ $order->customer_name ?? '-' }}</span>
                    <span
                        class="text-xs capitalize rounded-full px-2.5 py-0.5 {{ $order->status->value === 'paid' ? 'bg-green-100 text-green-800' : ($order->status->value === 'preparing' ? 'bg-amber-100 text-amber-800' : ($order->status->value === 'ready' ? 'bg-blue-100 text-blue-800' : ($order->status->value === 'served' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800'))) }}">
                        {{ $order->status->value }}
                    </span>
                    <span class="font-medium w-28 text-right">$ {{ number_format($order->total_price, 0, '.', ',')
                        }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-muted-foreground text-center py-6">No orders yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection