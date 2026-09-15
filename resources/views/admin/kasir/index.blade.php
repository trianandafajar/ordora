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
                        <div class="flex items-center justify-end gap-3" x-data="{
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
                                    if (!res.ok) throw new Error(data.message || 'Failed');
                                    this.isActive = data.is_active;
                                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                                } catch (e) {
                                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: e.message, type: 'error' } }));
                                } finally {
                                    this.toggling = false;
                                }
                            }
                        }">
                            <button @click="toggle()" :disabled="toggling"
                                class="text-xs font-medium px-2.5 py-1 rounded-md transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="isActive ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'bg-green-100 text-green-800 hover:bg-green-200'"
                                x-text="toggling ? '...' : (isActive ? 'Deactivate' : 'Activate')"></button>
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