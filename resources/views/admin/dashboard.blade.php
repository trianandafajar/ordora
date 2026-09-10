@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Dashboard Admin</h1>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border bg-card shadow-sm p-6">
            <p class="text-sm text-muted-foreground">Today's Revenue</p>
            <p class="text-2xl font-bold mt-1">Rp {{ number_format($stats['today_revenue'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border bg-card shadow-sm p-6">
            <p class="text-sm text-muted-foreground">Pending Orders</p>
            <p class="text-2xl font-bold mt-1">{{ $stats['pending_orders'] }}</p>
        </div>
        <div class="rounded-xl border bg-card shadow-sm p-6">
            <p class="text-sm text-muted-foreground">Active Kasir</p>
            <p class="text-2xl font-bold mt-1">{{ $stats['active_kasir'] }}</p>
        </div>
        <div class="rounded-xl border bg-card shadow-sm p-6">
            <p class="text-sm text-muted-foreground">Total Tables</p>
            <p class="text-2xl font-bold mt-1">{{ $stats['total_tables'] }}</p>
        </div>
    </div>
</div>
@endsection