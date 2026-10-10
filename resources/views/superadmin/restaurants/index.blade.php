@extends('layouts.app')

@section('content')
<div x-data="{
    owners: @js($owners),
    restaurants: @js($restaurants),
    modal: 'restaurant-modal',
    restaurant: { name: '', owner_id: '', id: '', is_active: true },
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
    },
    async toggleStatus() {
        try {
            const res = await fetch(`{{ route('superadmin.restaurants.toggle', 'ID') }}`.replace('ID', this.restaurant.id), {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed');
            
            const target = this.restaurants.find(r => r.id === this.restaurant.id);
            if (target) {
                target.is_active = data.is_active;
            }
            $dispatch('close-modal', { id: 'toggle-modal' });
        } catch (e) {
            alert(e.message);
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
                            <td class="p-4" x-text="r.owner ? r.owner.name : '-'"></td>
                            <td class="p-4 text-center">
                                <span x-text="r.is_active ? 'Active' : 'Suspended'" class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="r.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"></span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="restaurant = { ...r }; $dispatch('open-modal', { id: 'toggle-modal' })" :title="r.is_active ? 'Suspend' : 'Activate'"
                                        class="p-1.5 rounded-md transition-colors cursor-pointer"
                                        :class="r.is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-green-600 hover:bg-green-50'">
                                        <template x-if="r.is_active">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        </template>
                                        <template x-if="!r.is_active">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                                        </template>
                                    </button>
                                    <button @click="restaurant = { ...r }; modal = 'restaurant-modal'; $dispatch('open-modal', { id: 'restaurant-modal' })" 
                                        title="Edit" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                                    </button>
                                    <button @click="restaurant = r; $dispatch('open-modal', { id: 'delete-modal' })" 
                                        title="Delete" class="p-1.5 text-red-600 hover:bg-red-50 rounded-md cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </div>
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
                    class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Create User</button>
            </div>
        </div>
    </x-modal>

    <x-modal id="toggle-modal" title="Change Restaurant Status">
        <div class="space-y-4">
            <p class="text-sm text-muted-foreground">
                Are you sure you want to <strong x-text="restaurant.is_active ? 'suspend' : 'activate'"></strong> restaurant <strong x-text="'&quot;' + restaurant.name + '&quot;'"></strong>?
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', { id: 'toggle-modal' })"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
                <button type="button" @click="toggleStatus()"
                    class="rounded-md text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer text-white"
                    :class="restaurant.is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-green-600 hover:bg-green-700'"
                    x-text="restaurant.is_active ? 'Suspend Restaurant' : 'Activate Restaurant'"></button>
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
