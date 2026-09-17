@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Cashier Accounts</h1>
        <button x-data @click="$dispatch('open-modal', { id: 'create-kasir' })"
            class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 transition-opacity cursor-pointer">
            Add Cashier
        </button>
    </div>

    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-muted">
                <tr>
                    <th class="p-4 text-left font-medium w-10">#</th>
                    <th class="p-4 text-left font-medium">Name</th>
                    <th class="p-4 text-left font-medium">Email</th>
                    <th class="p-4 text-center font-medium">Status</th>
                    <th class="p-4 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kasirs as $index => $kasir)
                <tr class="border-t last:border-0 hover:bg-accent/30">
                    <td class="p-4 text-muted-foreground">{{ $index + 1 }}</td>
                    <td class="p-4 font-medium">{{ $kasir->name }}</td>
                    <td class="p-4 text-muted-foreground">{{ $kasir->email }}</td>
                    <td class="p-4 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-xs capitalize font-medium
                            {{ $kasir->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $kasir->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-2" x-data="{
            isActive: @js($kasir->is_active),
            toggling: false,
            async toggle() {
                this.toggling = true;

                try {
                    const res = await fetch('{{ route('admin.cashiers.toggle', $kasir) }}', {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        throw new Error(data.message || 'Failed');
                    }

                    this.isActive = data.is_active;

                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: data.message,
                            type: 'success'
                        }
                    }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: e.message,
                            type: 'error'
                        }
                    }));
                } finally {
                    this.toggling = false;
                }
            }
        }">
                            <button @click="toggle()" :disabled="toggling" :title="isActive ? 'Deactivate' : 'Activate'"
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

                            <button @click="$dispatch('open-modal', { id: 'edit-kasir', kasir: @js($kasir) })"
                                title="Edit"
                                class="p-1.5 rounded-md text-blue-600 hover:bg-blue-50 hover:text-blue-700 transition-colors cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>
                            </button>

                            <button @click="$dispatch('open-modal', { id: 'delete-kasir', kasir: @js($kasir) })"
                                title="Delete"
                                class="p-1.5 rounded-md text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-muted-foreground">No cashier accounts yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Edit Kasir Modal --}}
<x-modal id="edit-kasir" title="Edit Cashier">
    <div x-data="{ open: false, kasir: { name: '', email: '', id: '' }, action: '' }"
        x-on:open-modal.window="$event.detail.id === 'edit-kasir' ? (kasir = $event.detail.kasir, action = '{{ route('admin.cashiers.update', 'PLACEHOLDER') }}'.replace('PLACEHOLDER', kasir.id), open = true) : null"
        x-on:close-modal.window="$event.detail.id === 'edit-kasir' ? open = false : null" x-show="open" x-cloak>
        <form method="POST" :action="action" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input name="name" required maxlength="255" x-model="kasir.name"
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email (Readonly)</label>
                <input name="email" readonly :value="kasir.email"
                    class="w-full h-9 rounded-md border border-input bg-muted px-3 text-sm cursor-not-allowed">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">New Password (Optional)</label>
                <input name="password" type="password" minlength="6"
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="open = false"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
                <button type="submit"
                    class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Update</button>
            </div>
        </form>
    </div>
</x-modal>

{{-- Delete Kasir Modal --}}
<x-modal id="delete-kasir" title="Delete Cashier">
    <div x-data="{ open: false, kasir: { name: '', id: '' }, action: '' }"
        x-on:open-modal.window="$event.detail.id === 'delete-kasir' ? (kasir = $event.detail.kasir, action = '{{ route('admin.cashiers.destroy', 'PLACEHOLDER') }}'.replace('PLACEHOLDER', kasir.id), open = true) : null"
        x-on:close-modal.window="$event.detail.id === 'delete-kasir' ? open = false : null" x-show="open" x-cloak>
        <p class="text-sm text-muted-foreground mb-4">Are you sure you want to delete <strong
                x-text="kasir.name"></strong>? This action cannot be undone.</p>
        <form method="POST" :action="action">
            @csrf @method('DELETE')
            <div class="flex justify-end gap-2">
                <button type="button" @click="open = false"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
                <button type="submit"
                    class="rounded-md bg-destructive text-white text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Delete</button>
            </div>
        </form>
    </div>
</x-modal>

{{-- Create Kasir Modal --}}
<x-modal id="create-kasir" title="New Cashier Account">
    <form method="POST" action="{{ route('admin.cashiers.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input name="name" required maxlength="255"
                class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input name="email" type="email" required maxlength="255"
                class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input name="password" type="password" required minlength="6"
                class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="$dispatch('close-modal', { id: 'create-kasir' })"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
            <button type="submit"
                class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Create
                Account</button>
        </div>
    </form>
</x-modal>
@endsection