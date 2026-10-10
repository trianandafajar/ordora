@extends('layouts.app')

@section('content')
<div x-data="{
    users: @js($users),
    restaurants: @js($restaurants),
    user: { id: '', name: '', email: '', password: '', role: 'admin', restaurant_id: '', is_active: true },
    loading: { save: false, toggle: false, delete: false },
    errors: {},

    firstErrors(errors) {
        return Object.fromEntries(Object.entries(errors || {}).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v]));
    },

    async send(url, method, body) {
        const res = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: body ? JSON.stringify(body) : undefined
        });
        let data = {};
        try { data = await res.json(); } catch (e) {}
        return { res, data };
    },

    openCreate() {
        this.errors = {};
        this.user = { id: '', name: '', email: '', password: '', role: 'admin', restaurant_id: '', is_active: true };
        $dispatch('open-modal', { id: 'user-modal' });
    },

    openEdit(u) {
        this.errors = {};
        this.user = { ...u, password: '' };
        $dispatch('open-modal', { id: 'user-modal' });
    },

    async saveUser() {
        if (this.loading.save) return;

        this.errors = {};
        if (!this.user.name.trim()) this.errors.name = 'Name is required.';
        if (!this.user.email.trim()) this.errors.email = 'Email is required.';
        if (!this.user.id && !this.user.password.trim()) this.errors.password = 'Password is required.';
        if (Object.keys(this.errors).length) return;

        this.loading.save = true;
        try {
            const method = this.user.id ? 'PUT' : 'POST';
            const url = this.user.id
                ? `{{ route('superadmin.users.update', 'ID') }}`.replace('ID', this.user.id)
                : '{{ route('superadmin.users.store') }}';

            const { res, data } = await this.send(url, method, this.user);

            if (res.status === 422) {
                this.errors = this.firstErrors(data.errors);
                return;
            }
            if (!res.ok) throw new Error(data.message || 'Something went wrong.');

            if (this.user.id) {
                this.users = this.users.map(u => u.id === data.user.id ? data.user : u);
            } else {
                this.users.unshift(data.user);
            }
            $dispatch('close-modal', { id: 'user-modal' });
        } catch (e) {
            this.errors.general = e.message;
        } finally {
            this.loading.save = false;
        }
    },

    async toggleStatus() {
        if (this.loading.toggle) return;
        this.loading.toggle = true;
        try {
            const { res, data } = await this.send(
                `{{ route('superadmin.users.toggle', 'ID') }}`.replace('ID', this.user.id),
                'PATCH'
            );
            if (!res.ok) throw new Error(data.message || 'Failed');

            const target = this.users.find(u => u.id === this.user.id);
            if (target) target.is_active = data.is_active;
            $dispatch('close-modal', { id: 'toggle-modal' });
        } catch (e) {
            alert(e.message);
        } finally {
            this.loading.toggle = false;
        }
    },

    async deleteUser() {
        if (this.loading.delete) return;
        this.loading.delete = true;
        try {
            const { res, data } = await this.send(
                `{{ route('superadmin.users.destroy', 'ID') }}`.replace('ID', this.user.id),
                'DELETE'
            );
            if (!res.ok) throw new Error(data.message || 'Failed to delete.');

            this.users = this.users.filter(u => u.id !== this.user.id);
            $dispatch('close-modal', { id: 'delete-modal' });
        } catch (e) {
            alert(e.message);
        } finally {
            this.loading.delete = false;
        }
    }
}">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight">Manage Users</h1>
            <button @click="openCreate()"
                class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">
                Add User
            </button>
        </div>

        <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr>
                        <th class="p-4 text-left font-medium">Name</th>
                        <th class="p-4 text-left font-medium">Email</th>
                        <th class="p-4 text-left font-medium">Role</th>
                        <th class="p-4 text-left font-medium">Restaurant</th>
                        <th class="p-4 text-center font-medium">Status</th>
                        <th class="p-4 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="u in users" :key="u.id">
                        <tr class="border-t hover:bg-accent/30">
                            <td class="p-4" x-text="u.name"></td>
                            <td class="p-4" x-text="u.email"></td>
                            <td class="p-4 text-capitalize" x-text="u.role"></td>
                            <td class="p-4">
                                <span x-text="restaurants.find(r => r.id == u.restaurant_id)?.name || '-'"></span>
                            </td>
                            <td class="p-4 text-center">
                                <span x-text="u.is_active ? 'Active' : 'Suspended'"
                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="u.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"></span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="user = { ...u }; $dispatch('open-modal', { id: 'toggle-modal' })"
                                        :title="u.is_active ? 'Suspend' : 'Activate'"
                                        class="p-1.5 rounded-md transition-colors cursor-pointer"
                                        :class="u.is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-green-600 hover:bg-green-50'">
                                        <template x-if="u.is_active">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        </template>
                                        <template x-if="!u.is_active">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                                        </template>
                                    </button>
                                    <button @click="openEdit(u)" title="Edit" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                                    </button>
                                    <button @click="user = u; $dispatch('open-modal', { id: 'delete-modal' })" title="Delete" class="p-1.5 text-red-600 hover:bg-red-50 rounded-md cursor-pointer">
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

    <x-modal id="user-modal" title="User">
        <form @submit.prevent="saveUser()" class="space-y-4" novalidate>
            <p x-show="errors.general" x-text="errors.general" x-cloak class="rounded-md bg-red-50 text-red-700 text-sm px-3 py-2"></p>

            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input x-model="user.name" required class="w-full h-9 rounded-md border px-3 text-sm" :class="errors.name ? 'border-red-500' : 'border-input'">
                <p x-show="errors.name" x-text="errors.name" x-cloak class="mt-1 text-xs text-red-600"></p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input x-model="user.email" type="email" required class="w-full h-9 rounded-md border px-3 text-sm" :class="errors.email ? 'border-red-500' : 'border-input'">
                <p x-show="errors.email" x-text="errors.email" x-cloak class="mt-1 text-xs text-red-600"></p>
            </div>

            <div x-data="{ show: false }">
                <label class="block text-sm font-medium mb-1">Password <span x-show="user.id" class="text-xs text-muted-foreground">(Leave blank to keep current)</span></label>
                <div class="relative">
                    <input x-model="user.password" :type="show ? 'text' : 'password'" class="w-full h-9 rounded-md border px-3 text-sm pr-10" :class="errors.password ? 'border-red-500' : 'border-input'">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3 text-muted-foreground hover:text-foreground">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.22 6.22A10.472 10.472 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.778M6.22 6.22 3.86 3.86M17.78 17.78 20.14 20.14M12 9a3 3 0 0 1 2.25 5.06" /><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223 20.14 20.14" /></svg>
                    </button>
                </div>
                <p x-show="errors.password" x-text="errors.password" x-cloak class="mt-1 text-xs text-red-600"></p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select x-model="user.role" class="w-full h-9 rounded-md border px-3 text-sm">
                    <option value="admin">Admin</option>
                    <option value="kasir">Kasir</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Restaurant</label>
                <select x-model="user.restaurant_id" class="w-full h-9 rounded-md border px-3 text-sm">
                    <option value="">No Restaurant</option>
                    <template x-for="r in restaurants" :key="r.id">
                        <option :value="r.id" x-text="r.name"></option>
                    </template>
                </select>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" @click="$dispatch('close-modal', { id: 'user-modal' })" class="rounded-md border text-sm font-medium h-9 px-4">Cancel</button>
                <button type="submit" :disabled="loading.save" class="inline-flex items-center gap-2 rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4">
                    <svg x-show="loading.save" x-cloak class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                    <span x-text="loading.save ? 'Saving...' : 'Save'"></span>
                </button>
            </div>
        </form>
    </x-modal>

    <x-modal id="toggle-modal" title="Change User Status">
        <div class="space-y-4">
            <p class="text-sm text-muted-foreground">Are you sure you want to <strong x-text="user.is_active ? 'suspend' : 'activate'"></strong> user <strong x-text="'&quot;' + user.name + '&quot;'"></strong>?</p>
            <div class="flex justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', { id: 'toggle-modal' })" class="rounded-md border text-sm font-medium h-9 px-4">Cancel</button>
                <button type="button" @click="toggleStatus()" :disabled="loading.toggle" class="inline-flex items-center gap-2 rounded-md text-sm font-medium h-9 px-4 text-white" :class="user.is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-green-600 hover:bg-green-700'">
                    <span x-text="loading.toggle ? 'Processing...' : (user.is_active ? 'Suspend' : 'Activate')"></span>
                </button>
            </div>
        </div>
    </x-modal>

    <x-modal id="delete-modal" title="Delete User">
        <div class="space-y-4">
            <p class="text-sm text-muted-foreground">Are you sure you want to delete user <strong x-text="'&quot;' + user.name + '&quot;'"></strong>? This action cannot be undone.</p>
            <div class="flex justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', { id: 'delete-modal' })" class="rounded-md border text-sm font-medium h-9 px-4">Cancel</button>
                <button type="button" @click="deleteUser()" :disabled="loading.delete" class="inline-flex items-center gap-2 rounded-md bg-destructive text-white text-sm font-medium h-9 px-4">
                    <span x-text="loading.delete ? 'Deleting...' : 'Delete'"></span>
                </button>
            </div>
        </div>
    </x-modal>
</div>
@endsection
