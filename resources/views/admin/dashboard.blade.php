@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Dashboard Admin</h1>
            <p class="text-sm text-muted-foreground mt-1">Overview of your coffee shop operations and revenue.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs bg-muted text-muted-foreground px-3 py-1.5 rounded-md font-medium">
                {{ now()->format('d M Y, H:i') }}
            </span>
        </div>
    </div>

    <!-- stats cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
        $cardIcons = [
        'today_revenue' => 'M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6',
        'pending_orders' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2',
        'active_kasir' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0
        11-8 0 4 4 0 018 0z',
        'total_tables' => 'M4 6h16M4 10h16M4 14h16M4 18h16',
        ];
        $cardLabels = [
        'today_revenue' => "Today's Revenue",
        'pending_orders' => 'Pending Orders',
        'active_kasir' => 'Active Kasir',
        'total_tables' => 'Total Tables',
        ];
        $cardNotes = [
        'today_revenue' => 'Total paid orders today',
        'pending_orders' => 'Orders waiting to be processed',
        'active_kasir' => 'Kasir online right now',
        'total_tables' => 'Registered tables in coffee shop',
        ];
        $cardValues = [
        'today_revenue' => 'Rp ' . number_format($stats['today_revenue'], 0, ',', '.'),
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
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
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
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
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
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-sm">Tables QR</h3>
                <p class="text-xs text-muted-foreground">Manage tables and QR tokens</p>
            </div>
        </a>
        <a href="{{ route('admin.kasir.index') }}"
            class="rounded-xl border bg-card hover:bg-accent/50 transition-colors p-6 flex items-center gap-4">
            <div class="size-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-sm">Kasir Accounts</h3>
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
                        <span class="w-28 text-right font-medium">Rp {{ number_format($item['revenue'], 0, ',', '.')
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
            <a href="{{ route('kasir.dashboard') }}" class="text-xs text-primary hover:underline">Lihat Semua</a>
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
                    <span class="text-xs text-muted-foreground">Meja {{ $order->table->number ?? '-' }}</span>
                    <span class="text-xs text-muted-foreground">{{ $order->customer_name ?? '-' }}</span>
                    <span
                        class="text-xs capitalize rounded-full px-2.5 py-0.5 {{ $order->status->value === 'paid' ? 'bg-green-100 text-green-800' : ($order->status->value === 'preparing' ? 'bg-amber-100 text-amber-800' : ($order->status->value === 'ready' ? 'bg-blue-100 text-blue-800' : ($order->status->value === 'served' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800'))) }}">
                        {{ $order->status->value }}
                    </span>
                    <span class="font-medium w-28 text-right">Rp {{ number_format($order->total_price, 0, ',', '.')
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