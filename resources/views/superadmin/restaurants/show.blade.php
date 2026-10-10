@extends('layouts.app')

@section('content')
<div x-data="{
    staff: @js($restaurant->staff),
    staffMember: { id: '', name: '', email: '', password: '', role: 'kasir', is_active: true },
    loading: { save: false, toggle: false, delete: false },
    errors: {},
    tab: 'staff',

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
        this.staffMember = { id: '', name: '', email: '', password: '', role: 'kasir', is_active: true };
        $dispatch('open-modal', { id: 'staff-modal' });
    },

    openEditStaff(s) {
        this.errors = {};
        this.staffMember = { ...s, password: '' };
        $dispatch('open-modal', { id: 'staff-modal' });
    },

    async saveStaff() {
        if (this.loading.save) return;
        this.loading.save = true;
        this.errors = {};

        try {
            const method = this.staffMember.id ? 'PUT' : 'POST';
            const url = this.staffMember.id
                ? `{{ route('superadmin.restaurants.staff.update', [$restaurant->id, 'ID']) }}`.replace('ID', this.staffMember.id)
                : `{{ route('superadmin.restaurants.staff.store', $restaurant->id) }}`;

            const { res, data } = await this.send(url, method, this.staffMember);

            if (res.status === 422) {
                this.errors = this.firstErrors(data.errors);
                return;
            }
            if (!res.ok) throw new Error(data.message || 'Something went wrong.');

            if (this.staffMember.id) {
                this.staff = this.staff.map(s => s.id === data.user.id ? data.user : s);
            } else {
                this.staff.unshift(data.user);
            }
            $dispatch('close-modal', { id: 'staff-modal' });
        } catch (e) {
            this.errors.general = e.message;
        } finally {
            this.loading.save = false;
        }
    },

    async toggleStaffStatus(s) {
        try {
            const { res, data } = await this.send(
                `{{ route('superadmin.restaurants.staff.toggle', [$restaurant->id, 'ID']) }}`.replace('ID', s.id),
                'PATCH'
            );
            if (!res.ok) throw new Error('Failed');
            s.is_active = data.is_active;
        } catch (e) {
            alert(e.message);
        }
    },

    async deleteStaff() {
        if (this.loading.delete) return;
        this.loading.delete = true;
        try {
            const { res, data } = await this.send(
                `{{ route('superadmin.restaurants.staff.destroy', [$restaurant->id, 'ID']) }}`.replace('ID', this.staffMember.id),
                'DELETE'
            );
            if (!res.ok) throw new Error('Failed to delete');
            this.staff = this.staff.filter(s => s.id !== this.staffMember.id);
            $dispatch('close-modal', { id: 'delete-staff-modal' });
        } catch (e) {
            alert(e.message);
        } finally {
            this.loading.delete = false;
        }
    }
}" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">{{ $restaurant->name }}</h1>
            <p class="text-sm text-muted-foreground">Owner: {{ $restaurant->owner->name ?? '-' }}</p>
        </div>
        <a href="{{ route('superadmin.restaurants.index') }}" class="rounded-md border h-9 px-4 flex items-center text-sm font-medium hover:bg-accent cursor-pointer">Back</a>
    </div>

    <div class="space-y-4">
        <div class="flex border-b gap-4 items-center justify-between">
            <div class="flex gap-4">
                <button @click="tab = 'staff'" :class="tab === 'staff' ? 'border-b-2 border-primary font-bold' : 'text-muted-foreground'" class="pb-2 text-sm cursor-pointer">Staff</button>
                <button @click="tab = 'menu'" :class="tab === 'menu' ? 'border-b-2 border-primary font-bold' : 'text-muted-foreground'" class="pb-2 text-sm cursor-pointer">Menu & Products</button>
                <button @click="tab = 'tables'" :class="tab === 'tables' ? 'border-b-2 border-primary font-bold' : 'text-muted-foreground'" class="pb-2 text-sm cursor-pointer">Tables QR</button>
            </div>
            <button x-show="tab === 'staff'" @click="openCreate()" class="rounded-md bg-primary text-primary-foreground text-xs font-medium h-8 px-3 mb-2 cursor-pointer">Add Staff</button>
        </div>

        {{-- Staff Tab --}}
        <div x-show="tab === 'staff'" class="rounded-xl border bg-card shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr><th class="p-4 text-left">Name</th><th class="p-4 text-left">Email</th><th class="p-4 text-left">Role</th><th class="p-4 text-center">Status</th><th class="p-4 text-right">Actions</th></tr>
                </thead>
                <tbody>
                    <template x-for="s in staff" :key="s.id">
                        <tr class="border-t hover:bg-accent/30">
                            <td class="p-4 font-medium" x-text="s.name"></td>
                            <td class="p-4" x-text="s.email"></td>
                            <td class="p-4 capitalize" x-text="s.role"></td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="s.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    x-text="s.is_active ? 'Active' : 'Suspended'"></span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2" x-show="s.role === 'kasir'">
                                    <button @click="toggleStaffStatus(s)" class="p-1.5 rounded-md cursor-pointer" :class="s.is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-green-600 hover:bg-green-50'">
                                        <svg x-show="s.is_active" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        <svg x-show="!s.is_active" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                                    </button>
                                    <button @click="openEditStaff(s)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                                    </button>
                                    <button @click="staffMember = s; $dispatch('open-modal', { id: 'delete-staff-modal' })" class="p-1.5 text-red-600 hover:bg-red-50 rounded-md cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Menu Tab --}}
        <div x-show="tab === 'menu'" class="space-y-6">
            @foreach($restaurant->categories as $cat)
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                <div class="p-4 border-b font-medium bg-muted">{{ $cat->name }}</div>
                <table class="w-full text-sm">
                    <thead><tr><th class="p-4 text-left w-16">Image</th><th class="p-4 text-left">Product</th><th class="p-4 text-left">Price</th><th class="p-4 text-center">Status</th></tr></thead>
                    <tbody>
                        @foreach($cat->products as $prod)
                        <tr class="border-t">
                            <td class="p-4">
                                @if($prod->image)
                                    <img src="{{ asset('storage/' . $prod->image) }}" class="size-10 object-cover rounded">
                                @else
                                    <div class="size-10 bg-muted rounded flex items-center justify-center text-[10px] text-muted-foreground">No img</div>
                                @endif
                            </td>
                            <td class="p-4 font-medium">{{ $prod->name }}</td>
                            <td class="p-4">Rp {{ number_format($prod->price, 0, ',', '.') }}</td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $prod->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $prod->is_available ? 'Available' : 'Habis' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>

        {{-- Tables Tab --}}
        <div x-show="tab === 'tables'" class="rounded-xl border bg-card shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted">
                    <tr><th class="p-4 text-left">Table Number</th><th class="p-4 text-left">QR Token</th><th class="p-4 text-center">Status</th><th class="p-4 text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($restaurant->tables as $t)
                    <tr class="border-t">
                        <td class="p-4 font-medium">Meja {{ $t->table_number }}</td>
                        <td class="p-4 font-mono text-xs text-muted-foreground">{{ $t->qr_token }}</td>
                        <td class="p-4 text-center"><span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $t->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $t->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="p-4 text-right">
                            <a href="{{ route('superadmin.restaurants.tables.show', [$restaurant->id, $t->id]) }}" class="text-primary hover:underline cursor-pointer flex items-center justify-end gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Add/Edit Staff --}}
    <x-modal id="staff-modal" title="Staff Management">
        <form @submit.prevent="saveStaff()" class="space-y-4" novalidate>
            <p x-show="errors.general" x-text="errors.general" x-cloak class="rounded-md bg-red-50 text-red-700 text-sm px-3 py-2"></p>
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input x-model="staffMember.name" required class="w-full h-9 rounded-md border px-3 text-sm" :class="errors.name ? 'border-red-500' : 'border-input'">
                <p x-show="errors.name" x-text="errors.name" x-cloak class="mt-1 text-xs text-red-600"></p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input x-model="staffMember.email" type="email" required class="w-full h-9 rounded-md border px-3 text-sm" :class="errors.email ? 'border-red-500' : 'border-input'">
                <p x-show="errors.email" x-text="errors.email" x-cloak class="mt-1 text-xs text-red-600"></p>
            </div>
            <div x-data="{ showPass: false }">
                <label class="block text-sm font-medium mb-1">Password <span x-show="staffMember.id" class="text-xs text-muted-foreground">(Leave blank to keep current)</span></label>
                <div class="relative">
                    <input x-model="staffMember.password" :type="showPass ? 'text' : 'password'" class="w-full h-9 rounded-md border px-3 text-sm pr-10" :class="errors.password ? 'border-red-500' : 'border-input'">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 px-3 text-muted-foreground hover:text-foreground">
                        <svg x-show="!showPass" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        <svg x-show="showPass" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.22 6.22A10.472 10.472 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.778M6.22 6.22 3.86 3.86M17.78 17.78 20.14 20.14M12 9a3 3 0 0 1 2.25 5.06" /><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223 20.14 20.14" /></svg>
                    </button>
                </div>
                <p x-show="errors.password" x-text="errors.password" x-cloak class="mt-1 text-xs text-red-600"></p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <input type="text" value="Kasir" disabled class="w-full h-9 rounded-md border px-3 text-sm bg-muted text-muted-foreground">
                <input type="hidden" x-model="staffMember.role">
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" @click="$dispatch('close-modal', { id: 'staff-modal' })" class="rounded-md border text-sm font-medium h-9 px-4">Cancel</button>
                <button type="submit" :disabled="loading.save" class="inline-flex items-center gap-2 rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4">
                    <span x-text="loading.save ? 'Saving...' : 'Save'"></span>
                </button>
            </div>
        </form>
    </x-modal>

    {{-- Delete Modal --}}
    <x-modal id="delete-staff-modal" title="Delete Staff">
        <div class="space-y-4">
            <p class="text-sm text-muted-foreground">Are you sure you want to delete <strong x-text="staffMember.name"></strong>?</p>
            <div class="flex justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', { id: 'delete-staff-modal' })" class="rounded-md border text-sm font-medium h-9 px-4">Cancel</button>
                <button type="button" @click="deleteStaff()" :disabled="loading.delete" class="inline-flex items-center gap-2 rounded-md bg-destructive text-white text-sm font-medium h-9 px-4">
                    <span x-text="loading.delete ? 'Deleting...' : 'Delete'"></span>
                </button>
            </div>
        </div>
    </x-modal>
</div>
@endsection
