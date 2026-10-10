@extends('layouts.app')

@section('content')
<div x-data="{
    owners: @js($owners),
    restaurants: @js($restaurants),
    modal: 'restaurant-modal',
    restaurant: { name: '', owner_id: '', id: '' },
    user: { name: '', email: '' },

    async storeRestaurant() {
        const method = this.restaurant.id ? 'PUT' : 'POST';
        const url = this.restaurant.id ? `{{ route('superadmin.restaurants.update', 'ID') }}`.replace('ID', this.restaurant.id) : '{{ route('superadmin.restaurants.store') }}';

        const res = await fetch(url, {
            method: method,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify(this.restaurant)
        });
        const data = await res.json();
        if (data.success) {
            if(this.restaurant.id) {
                this.restaurants = this.restaurants.map(r => r.id === data.restaurant.id ? data.restaurant : r);
            } else {
                this.restaurants.unshift(data.restaurant);
                this.owners = this.owners.filter(o => o.id != this.restaurant.owner_id);
            }
            $dispatch('close-modal', { id: 'restaurant-modal' });
        }
    },
    async storeUser() {
        const res = await fetch('{{ route('superadmin.restaurants.storeUser') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify(this.user)
        });
        const data = await res.json();
        if (data.success) {
            this.owners.push(data.user);
            this.restaurant.owner_id = data.user.id;
            this.modal = 'restaurant-modal';
        }
    }
}">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight">Manage Restaurants</h1>
            <button
                @click="modal = 'restaurant-modal'; restaurant = { name: '', owner_id: '' }; $dispatch('open-modal', { id: 'restaurant-modal' })"
                class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">
                Add Restaurant
            </button>
        </div>

        <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr>
                        <th class="p-4 text-left font-medium">Name</th>
                        <th class="p-4 text-left font-medium">Owner</th>
                        <th class="p-4 text-center font-medium">Status</th>
                        <th class="p-4 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="r in restaurants" :key="r.id">
                        <tr class="border-t hover:bg-accent/30">
                            <td class="p-4" x-text="r.name"></td>
                            <td class="p-4" x-text="r.owner.name"></td>
                            <td class="p-4 text-center">
                                <span x-text="r.is_active ? 'Active' : 'Suspended'" class="px-2 py-1 rounded text-xs"
                                    :class="r.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"></span>
                            </td>
                            <td class="p-4 text-right">
                                <button
                                    @click="restaurant = { ...r }; modal = 'restaurant-modal'; $dispatch('open-modal', { id: 'restaurant-modal' })"
                                    class="text-blue-600 mr-2">Edit</button>
                                <button @click="restaurant = r; $dispatch('open-modal', { id: 'delete-modal' })"
                                    class="text-red-600">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <x-modal id="restaurant-modal" title="Restaurant">
        {{-- restaurant form --}}
        <div x-show="modal === 'restaurant-modal'" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input x-model="restaurant.name" required maxlength="255"
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Owner</label>
                <div class="flex items-center gap-2">
                    <select x-model="restaurant.owner_id"
                        class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
                        <option value="">Select Owner</option>
                        <template x-for="o in owners" :key="o.id">
                            <option :value="o.id" x-text="o.name"></option>
                        </template>
                    </select>
                    <button type="button" @click="modal = 'user-modal'" title="Add new owner"
                        class="rounded-md border h-9 w-9 shrink-0 flex items-center justify-center text-primary hover:bg-accent/50 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" @click="$dispatch('close-modal', { id: 'restaurant-modal' })"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
                <button type="button" @click="storeRestaurant()"
                    class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer"
                    x-text="restaurant.id ? 'Save Changes' : 'Create Restaurant'"></button>
            </div>
        </div>

        {{-- new owner form --}}
        <div x-show="modal === 'user-modal'" x-cloak class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input x-model="user.name" required maxlength="255"
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input x-model="user.email" type="email" required maxlength="255"
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" @click="modal = 'restaurant-modal'"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
                <button type="button" @click="storeUser()"
                    class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Create
                    User</button>
            </div>
        </div>
    </x-modal>

    <x-modal id="delete-modal" title="Delete Restaurant">
        <div class="space-y-4">
            <p class="text-sm text-muted-foreground">
                Are you sure you want to delete restaurant <strong
                    x-text="'&quot;' + restaurant.name + '&quot;'"></strong>? This action cannot be undone.
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', { id: 'delete-modal' })"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
                <button type="button" @click="
                    fetch(`{{ route('superadmin.restaurants.destroy', 'ID') }}`.replace('ID', restaurant.id), {
                        method: 'DELETE',
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                    }).then(() => {
                        restaurants = restaurants.filter(i => i.id !== restaurant.id);
                        $dispatch('close-modal', { id: 'delete-modal' });
                    })
                "
                    class="rounded-md bg-destructive text-destructive-foreground text-sm text-white font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Delete</button>
            </div>
        </div>
    </x-modal>
</div>
@endsection