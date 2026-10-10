@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Dashboard Superadmin</h1>
            <p class="text-sm text-muted-foreground mt-1">Overview of all restaurants, admins and cashiers in the
                system.</p>
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

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @php
        $cardIcons = [
        'total_restaurants' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1
        4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'active_restaurants' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'suspended_restaurants' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728
        12.728L5.636 5.636',
        'total_admins' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15
        19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331
        0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0
        016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'total_cashiers' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75
        4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0
        .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125
        1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5
        1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z',
        ];
        $cardLabels = [
        'total_restaurants' => 'Total Restaurants',
        'active_restaurants' => 'Active Restaurants',
        'suspended_restaurants' => 'Suspended',
        'total_admins' => 'Total Admins',
        'total_cashiers' => 'Total Cashiers',
        ];
        $cardNotes = [
        'total_restaurants' => 'All registered restaurants',
        'active_restaurants' => 'Restaurants currently operating',
        'suspended_restaurants' => 'Restaurants that are suspended',
        'total_admins' => 'Restaurant admin accounts',
        'total_cashiers' => 'Cashier accounts across restaurants',
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
                <div class="text-2xl font-bold">{{ $stats[$key] }}</div>
                <p class="text-xs text-muted-foreground mt-1">{{ $cardNotes[$key] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border bg-card shadow-sm">
            <div class="flex items-center justify-between px-6 pt-6 pb-2">
                <div>
                    <h3 class="font-semibold">Restaurant Growth</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">New restaurants in the last 6 months</p>
                </div>
            </div>
            <div class="p-6 pt-4">
                @php $maxGrowth = max(1, ...array_merge([1], array_column($growthData, 'count'))); @endphp
                <div class="space-y-3">
                    @foreach($growthData as $item)
                    <div class="flex items-center gap-4 text-xs">
                        <span class="w-16 text-muted-foreground">{{ $item['label'] }}</span>
                        <div class="flex-1 h-3 bg-muted rounded-full overflow-hidden">
                            <div class="bg-primary rounded-full h-full transition-all"
                                style="width: {{ round(($item['count'] / $maxGrowth) * 100) }}%"></div>
                        </div>
                        <span class="w-10 text-right font-medium">{{ $item['count'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-card shadow-sm">
            <div class="flex items-center justify-between px-6 pt-6 pb-2">
                <div>
                    <h3 class="font-semibold">Top Revenue Restaurants</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Restaurants with the highest total sales</p>
                </div>
            </div>
            <div class="p-6 pt-4">
                @php $maxTop = max(1, (float) $topRevenue->max('orders_sum_total_price')); @endphp
                <div class="space-y-3">
                    @forelse($topRevenue as $r)
                    @php $rev = (float) ($r->orders_sum_total_price ?? 0); @endphp
                    <div class="flex items-center gap-4 text-xs">
                        <span class="w-28 truncate text-muted-foreground">{{ $r->name }}</span>
                        <div class="flex-1 h-3 bg-muted rounded-full overflow-hidden">
                            <div class="bg-primary rounded-full h-full transition-all"
                                style="width: {{ round(($rev / $maxTop) * 100) }}%"></div>
                        </div>
                        <span class="w-28 text-right font-medium">$ {{ number_format($rev, 0, '.', ',') }}</span>
                    </div>
                    @empty
                    <p class="text-sm text-muted-foreground text-center py-6">No revenue data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-xl border bg-card shadow-sm">
        <div class="flex items-center justify-between px-6 pt-6 pb-2">
            <div>
                <h3 class="font-semibold">Recent Restaurants</h3>
                <p class="text-xs text-muted-foreground mt-0.5">Latest registered restaurants</p>
            </div>
        </div>
        <div class="p-6 pt-4">
            @if(count($recentRestaurants) > 0)
            <div class="divide-y">
                @foreach($recentRestaurants as $restaurant)
                <div class="flex items-center gap-4 py-3 text-sm first:pt-0 last:pb-0">
                    <div
                        class="w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $restaurant->is_active ? 'bg-green-500' : 'bg-red-500' }}">
                    </div>
                    <span class="font-medium flex-1 truncate">{{ $restaurant->name }}</span>
                    <span class="text-xs text-muted-foreground hidden sm:inline">{{ $restaurant->owner->name }}</span>
                    <span class="text-xs text-muted-foreground hidden md:inline truncate">{{ $restaurant->owner->email
                        }}</span>
                    <span
                        class="text-xs capitalize rounded-full px-2.5 py-0.5 {{ $restaurant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $restaurant->is_active ? 'Active' : 'Suspended' }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-muted-foreground text-center py-6">No restaurants yet.</p>
            @endif
        </div>
    </div>
</div>

@endsection