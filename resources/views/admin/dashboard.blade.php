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

    <!-- Stats Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-muted-foreground">Today's Revenue</span>
                <svg class="size-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                </svg>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold">Rp {{ number_format($stats['today_revenue'], 0, ',', '.') }}</div>
                <p class="text-xs text-muted-foreground mt-1">Total paid orders today</p>
            </div>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-muted-foreground">Pending Orders</span>
                <svg class="size-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                </svg>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold">{{ $stats['pending_orders'] }}</div>
                <p class="text-xs text-muted-foreground mt-1">Orders waiting to be processed</p>
            </div>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-muted-foreground">Active Kasir</span>
                <svg class="size-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold">{{ $stats['active_kasir'] }}</div>
                <p class="text-xs text-muted-foreground mt-1">Kasir online right now</p>
            </div>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground shadow-sm p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-muted-foreground">Total Tables</span>
                <svg class="size-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-bold">{{ $stats['total_tables'] }}</div>
                <p class="text-xs text-muted-foreground mt-1">Registered tables in coffee shop</p>
            </div>
        </div>
    </div>

    <!-- Quick Links Grid -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.categories.index') }}"
            class="rounded-xl border bg-card hover:bg-accent/50 transition-colors p-6 flex items-center gap-4">
            <div class="size-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold">
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
            <div class="size-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold">
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
            <div class="size-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold">
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
            <div class="size-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold">
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
</div>
@endsection