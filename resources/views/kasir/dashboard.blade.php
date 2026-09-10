@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Incoming Orders</h1>
        <span id="orderCount" class="text-sm text-muted-foreground">{{ $orders->count() }} active orders</span>
    </div>

    <div id="ordersList" class="space-y-3">
        @forelse ($orders as $order)
            <div class="rounded-xl border bg-card shadow-sm p-4 flex items-center justify-between hover:bg-accent/30 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center font-bold text-sm text-primary">
                        #{{ $order->id }}
                    </div>
                    <div>
                        <p class="font-medium text-sm">{{ $order->customer_name }}</p>
                        <p class="text-xs text-muted-foreground">Table {{ $order->table->number ?? '-' }} &middot; Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $order->status === 'preparing' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $order->status === 'ready' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $order->status === 'served' ? 'bg-purple-100 text-purple-800' : '' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                    <a href="{{ route('kasir.order.show', $order->id) }}"
                       class="text-xs bg-primary text-primary-foreground px-3 py-1.5 rounded-md hover:opacity-90 transition-opacity">
                        Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border bg-card shadow-sm p-8 text-center text-muted-foreground text-sm">
                No incoming orders
            </div>
        @endforelse
    </div>
</div>

<script>
setInterval(() => {
    fetch('/kasir/dashboard', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const list = doc.getElementById('ordersList');
            if (list) document.getElementById('ordersList').innerHTML = list.innerHTML;
        })
        .catch(() => {});
}, 5000);
</script>
@endsection