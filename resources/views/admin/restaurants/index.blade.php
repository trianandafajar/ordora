@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
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
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Manage Restaurants</h1>
        <button @click="modal = 'restaurant-modal'; restaurant = { name: '', owner_id: '' }; $dispatch('open-modal', { id: 'restaurant-modal' })"
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
                            <span x-text="r.is_active ? 'Active' : 'Suspended'" class="px-2 py-1 rounded text-xs" :class="r.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"></span>
                        </td>
                        <td class="p-4 text-right">
                            <button @click="restaurant = { ...r }; modal = 'restaurant-modal'; $dispatch('open-modal', { id: 'restaurant-modal' })" class="text-blue-600 mr-2">Edit</button>
                            <button @click="restaurant = r; $dispatch('open-modal', { id: 'delete-modal' })" class="text-red-600">Delete</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <x-modal id="restaurant-modal" title="Restaurant">
        <div x-show="modal === 'restaurant-modal'">
            <input x-model="restaurant.name" placeholder="Name" class="w-full p-2 border mb-4 rounded">
            <select x-model="restaurant.owner_id" class="w-full p-2 border mb-4 rounded">
                <option value="">Select Owner</option>
                <template x-for="o in owners" :key="o.id">
                    <option :value="o.id" x-text="o.name"></option>
                </template>
            </select>
            <button @click="modal = 'user-modal'" class="text-blue-500 mb-4 block">Add New User</button>
            <div class="flex gap-2">
                <button @click="$dispatch('close-modal', { id: 'restaurant-modal' })" class="flex-1 border p-2 rounded">Cancel</button>
                <button @click="storeRestaurant()" class="flex-1 bg-primary text-white p-2 rounded">Save</button>
            </div>
        </div>
        <div x-show="modal === 'user-modal'" x-cloak>
            <input x-model="user.name" placeholder="Name" class="w-full p-2 border mb-4 rounded">
            <input x-model="user.email" placeholder="Email" class="w-full p-2 border mb-4 rounded">
            <div class="flex gap-2">
                <button @click="modal = 'restaurant-modal'" class="flex-1 border p-2 rounded">Cancel</button>
                <button @click="storeUser()" class="flex-1 bg-primary text-white p-2 rounded">Create User</button>
            </div>
        </div>
    </x-modal>

    <x-modal id="delete-modal" title="Delete Confirmation">
        <p>Delete <strong x-text="restaurant.name"></strong>?</p>
        <div class="flex gap-2 mt-4">
            <button @click="$dispatch('close-modal', { id: 'delete-modal' })" class="flex-1 border p-2 rounded">Cancel</button>
            <button @click="
                fetch(`{{ route('superadmin.restaurants.destroy', 'ID') }}`.replace('ID', restaurant.id), { 
                    method: 'DELETE', 
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} 
                }).then(() => { 
                    restaurants = restaurants.filter(i => i.id !== restaurant.id); 
                    $dispatch('close-modal', { id: 'delete-modal' }); 
                })
            " class="flex-1 bg-red-500 text-white p-2 rounded">Confirm</button>
        </div>
    </x-modal>
</div>
@endsection
