@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Sales Reports</h1>
        <div class="text-sm text-muted-foreground">Today: {{ now()->format('d M Y') }}</div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border bg-card shadow-sm p-6">
            <p class="text-sm text-muted-foreground">Today's Revenue</p>
            <p class="text-2xl font-bold mt-1">Rp {{ number_format($stats['today_revenue'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border bg-card shadow-sm p-6">
            <p class="text-sm text-muted-foreground">Total Paid Orders</p>
            <p class="text-2xl font-bold mt-1">{{ $stats['total_paid_orders'] }}</p>
        </div>
        <div class="rounded-xl border bg-card shadow-sm p-6">
            <p class="text-sm text-muted-foreground">All-Time Revenue</p>
            <p class="text-2xl font-bold mt-1">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="rounded-xl border bg-card shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Top Selling Products</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-muted">
                    <tr>
                        <th class="p-3 text-left text-sm font-medium">Product</th>
                        <th class="p-3 text-right text-sm font-medium">Sold</th>
                        <th class="p-3 text-right text-sm font-medium">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $prod)
                        <tr class="border-t last:border-0">
                            <td class="p-3 text-sm font-medium">{{ $prod->name }}</td>
                            <td class="p-3 text-right text-sm text-muted-foreground">{{ $prod->sold }}</td>
                            <td class="p-3 text-right text-sm">Rp {{ number_format($prod->revenue, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-8 text-center text-sm text-muted-foreground">No sales data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach