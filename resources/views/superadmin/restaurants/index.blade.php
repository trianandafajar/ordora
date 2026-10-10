@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Manage Restaurants</h1>
        <a href="{{ route('superadmin.restaurants.create') }}"
            class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 transition-opacity cursor-pointer inline-flex items-center">
            Add Restaurant
        </a>
    </div>

    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-muted">
                <tr>
                    <th class="p-4 text-left font-medium w-10">#</th>
                    <th class="p-4 text-left font-medium">Name</th>
                    <th class="p-4 text-left font-medium">Owner</th>
                    <th class="p-4 text-center font-medium">Status</th>
                    <th class="p-4 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($restaurants as $index => $restaurant)
                <tr class="border-t last:border-0 hover:bg-accent/30" x-data="{
                    isActive: @js($restaurant->is_active),
                    toggling: false,
                    async toggle() {
                        this.toggling = true;
                        try {
                            const res = await fetch('{{ route('superadmin.restaurants.toggle', $restaurant) }}', {
                                method: 'PATCH',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });
                            const data = await res.json();
                            if (!res.ok) throw new Error(data.message || 'Failed');
                            this.isActive = data.is_active;
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { message: data.message, type: 'success' }
                            }));
                        } catch (e) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { message: e.message, type: 'error' }
                            }));
                        } finally {
                            this.toggling = false;
                        }
                    }
                }">
                    <td class="p-4 text-muted-foreground">{{ $index + 1 }}</td>
                    <td class="p-4 font-medium">{{ $restaurant->name }}</td>
                    <td class="p-4 text-muted-foreground">{{ $restaurant->owner->name }}</td>
                    <td class="p-4 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-xs capitalize font-medium"
                            :class="isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            x-text="isActive ? 'Active' : 'Suspended'">
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button @click="toggle()" :disabled="toggling" :title="isActive ? 'Suspend' : 'Activate'"
                                class="p-1.5 rounded-md transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="isActive
                                    ? 'text-green-600 hover:bg-green-50 hover:text-green-700'
                                    : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600'">
                                <template x-if="isActive">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M5.25 7.5A2.25 2.25 0 0 1 7.5 5.25h9a2.25 2.25 0 0 1 2.25 2.25v9a2.25 2.25 0 0 1-2.25 2.25h-9a2.25 2.25 0 0 1-2.25-2.25v-9Z" />
                                    </svg>
                                </template>
                                <template x-if="!isActive">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                                    </svg>
                                </template>
                            </button>
                            <a href="{{ route('superadmin.restaurants.edit', $restaurant) }}" title="Edit"
                                class="p-1.5 rounded-md text-blue-600 hover:bg-blue-50 hover:text-blue-700 transition-colors cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-muted-foreground">No restaurants yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection