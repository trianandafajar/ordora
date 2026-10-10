@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
        <p class="text-sm text-muted-foreground mt-1">System overview and statistics</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="rounded-lg border bg-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Restaurants</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['total_restaurants'] }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-muted-foreground opacity-50">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        </div>

        <div class="rounded-lg border bg-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Active Restaurants</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['active_restaurants'] }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-green-600 opacity-50">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="rounded-lg border bg-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Suspended</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['suspended_restaurants'] }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-red-600 opacity-50">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c.865.865 2.291 1.373 3.682 1.373 5.596 0 10.14-4.544 10.14-10.14 0-1.391-.508-2.817-1.373-3.682m0 0a9.005 9.005 0 00-8.716 12.529M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>

        <div class="rounded-lg border bg-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Admins</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['total_admins'] }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-blue-600 opacity-50">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </div>
        </div>

        <div class="rounded-lg border bg-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Cashiers</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['total_cashiers'] }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-purple-600 opacity-50">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold">Recent Restaurants</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-muted">
                <tr>
                    <th class="p-4 text-left font-medium w-10">#</th>
                    <th class="p-4 text-left font-medium">Name</th>
                    <th class="p-4 text-left font-medium">Owner</th>
                    <th class="p-4 text-left font-medium">Email</th>
                    <th class="p-4 text-center font-medium">Status</th>
                    <th class="p-4 text-right font-medium">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentRestaurants as $index => $restaurant)
                <tr class="border-t last:border-0 hover:bg-accent/30">
                    <td class="p-4 text-muted-foreground">{{ $index + 1 }}</td>
                    <td class="p-4 font-medium">{{ $restaurant->name }}</td>
                    <td class="p-4 font-medium">{{ $restaurant->owner->name }}</td>
                    <td class="p-4 text-muted-foreground">{{ $restaurant->owner->email }}</td>
                    <td class="p-4 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-xs capitalize font-medium"
                            class="{{ $restaurant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $restaurant->is_active ? 'Active' : 'Suspended' }}
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <a href="{{ route('superadmin.restaurants.edit', $restaurant) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-muted-foreground">No restaurants yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
