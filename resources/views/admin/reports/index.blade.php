@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-2xl font-bold tracking-tight">Sales Reports</h1>
        <div class="flex items-center gap-2" x-data="{ downloading: false }">
            <button type="button"
                @click="downloading = true; window.location.href = '{{ route('admin.reports.pdf', ['start_date' => $stats['period_start'], 'end_date' => $stats['period_end']]) }}'; setTimeout(() => downloading = false, 2000)"
                :disabled="downloading"
                class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 transition-opacity inline-flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span x-text="downloading ? 'Downloading...' : 'Download PDF'"></span>
            </button>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="rounded-xl border bg-card shadow-sm p-4">
        <form method="GET" action="{{ route('admin.reports') }}" class="flex items-end gap-3 flex-wrap">
            <div>
                <label class="block text-sm font-medium mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Filter</button>
                <a href="{{ route('admin.reports') }}"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 inline-flex items-center cursor-pointer">Reset</a>
            </div>
            <div class="text-sm text-muted-foreground ml-auto self-center">
                Periode: <span class="font-medium">{{ date('d M Y', strtotime($startDate)) }}</span> —
                <span class="font-medium">{{ date('d M Y', strtotime($endDate)) }}</span>
            </div>
        </form>
    </div>

    {{-- Stat Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border bg-card shadow-sm p-6">
            <p class="text-sm text-muted-foreground">Revenue (Periode)</p>
            <p class="text-2xl font-bold mt-1">Rp {{ number_format($stats['period_revenue'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border bg-card shadow-sm p-6">
            <p class="text-sm text-muted-foreground">Paid Orders (Periode)</p>
            <p class="text-2xl font-bold mt-1">{{ $stats['period_paid_orders'] }}</p>
        </div>
        <div class="rounded-xl border bg-card shadow-sm p-6">
            <p class="text-sm text-muted-foreground">Avg Per Order</p>
            <p class="text-2xl font-bold mt-1">Rp {{ number_format($stats['aov'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border bg-card shadow-sm p-6">
            <p class="text-sm text-muted-foreground">All-Time Revenue</p>
            <p class="text-2xl font-bold mt-1">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Top Products --}}
    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
        <div class="p-6 pb-0">
            <h3 class="text-lg font-semibold">Top Selling Products</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr>
                        <th class="p-3 text-left font-medium">#</th>
                        <th class="p-3 text-left font-medium">Product</th>
                        <th class="p-3 text-right font-medium">Sold</th>
                        <th class="p-3 text-right font-medium">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $i => $prod)
                    <tr class="border-t last:border-0 hover:bg-accent/30">
                        <td class="p-3 text-muted-foreground">{{ $i + 1 }}</td>
                        <td class="p-3 font-medium">{{ $prod->name }}</td>
                        <td class="p-3 text-right">{{ $prod->sold }}</td>
                        <td class="p-3 text-right font-medium">Rp {{ number_format($prod->revenue, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-sm text-muted-foreground">No sales data for this
                            period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Order History --}}
    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
        <div class="p-6 pb-0">
            <h3 class="text-lg font-semibold">Paid Orders History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr>
                        <th class="p-3 text-left font-medium">#</th>
                        <th class="p-3 text-left font-medium">Customer</th>
                        <th class="p-3 text-left font-medium">Table</th>
                        <th class="p-3 text-left font-medium">Payment</th>
                        <th class="p-3 text-left font-medium">Cashier</th>
                        <th class="p-3 text-right font-medium">Total</th>
                        <th class="p-3 text-right font-medium">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orderHistory as $order)
                    <tr class="border-t last:border-0 hover:bg-accent/30">
                        <td class="p-3 text-muted-foreground">{{ $order->id }}</td>
                        <td class="p-3 font-medium">{{ $order->customer_name ?? '—' }}</td>
                        <td class="p-3">{{ $order->table->number ?? '—' }}</td>
                        <td class="p-3 capitalize">{{ $order->payment_method?->value ?? '—' }}</td>
                        <td class="p-3">{{ $order->user->name ?? '—' }}</td>
                        <td class="p-3 text-right font-medium">Rp {{ number_format($order->total_price, 0, ',', '.') }}
                        </td>
                        <td class="p-3 text-right text-muted-foreground">{{ $order->created_at->format('d M Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-sm text-muted-foreground">No paid orders for this
                            period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection