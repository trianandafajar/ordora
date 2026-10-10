@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Table #{{ $table->number }}</h1>
            <p class="text-sm text-muted-foreground">Restaurant: {{ $restaurant->name }}</p>
        </div>
        <a href="{{ route('superadmin.restaurants.show', $restaurant->id) }}" class="rounded-md border h-9 px-4 flex items-center text-sm font-medium hover:bg-accent cursor-pointer">Back to Restaurant</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        {{-- Info & Active Orders --}}
        <div class="space-y-6">
            <div class="rounded-xl border bg-card p-4 space-y-3">
                <h4 class="text-sm font-medium text-muted-foreground">Table Information</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted-foreground">Number</span> <span class="font-medium">{{ $table->number }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Capacity</span> <span class="font-medium">{{ $table->capacity }} people</span></div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Status</span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs capitalize font-medium {{ $table->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $table->status }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted-foreground">Menu URL</span>
                        <a href="{{ $table->qr_url }}" target="_blank" class="text-primary text-xs hover:underline truncate max-w-[200px]">{{ $table->qr_url }}</a>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-4 space-y-3">
                <h4 class="text-sm font-medium text-muted-foreground">Active Orders</h4>
                @forelse($table->orders as $order)
                <div class="rounded-lg border p-3 text-sm space-y-1">
                    <div class="flex justify-between">
                        <span class="font-medium">#{{ $order->id }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs capitalize font-medium bg-muted text-muted-foreground">{{ $order->status }}</span>
                    </div>
                    <div class="text-muted-foreground text-xs">Customer: {{ $order->customer_name ?? '-' }}</div>
                    <div class="font-medium">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                    <div class="text-xs text-muted-foreground">{{ $order->created_at->format('d M Y H:i') }}</div>
                </div>
                @empty
                <div class="rounded-lg border p-4 text-center text-sm text-muted-foreground">
                    No active orders
                </div>
                @endforelse
            </div>
        </div>

        {{-- QR Code Card --}}
        <div class="rounded-xl border bg-card p-6 flex flex-col items-center justify-center gap-4">
            <h4 class="text-sm font-medium text-muted-foreground">QR Code</h4>
            <div class="rounded-lg border bg-white p-4">
                <img src="{{ route('admin.tables.qr', $table) }}" alt="QR Table {{ $table->number }}" class="w-48 h-48">
            </div>
            <p class="text-xs text-muted-foreground text-center">Scan to view menu at table {{ $table->number }}</p>
            <a href="{{ route('admin.tables.qr', $table) }}" download="table-{{ $table->number }}-qr.png"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 flex items-center gap-2 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download QR
            </a>
        </div>
    </div>
</div>
@endsection
