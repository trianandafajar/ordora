@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Kasir Accounts</h1>
        <button data-modal="create-kasir"
            class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 transition-opacity">
            Add Kasir
        </button>
    </div>

    @if(session('success'))
    <div class="rounded-md border border-green-300 bg-green-50 text-green-800 p-3 text-sm">{{ session('success') }}
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-muted">
                <tr>
                    <th class="p-4 text-left text-sm font-medium">Name</th>
                    <th class="p-4 text-left text-sm font-medium">Email</th>
                    <th class="p-4 text-left text-sm font-medium">Status</th>
                    <th class="p-4 text-center text-sm font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kasirs as $kasir)
                <tr class="border-t last:border-0 hover:bg-accent/30">
                    <td class="p-4 text-sm font-medium">{{ $kasir->name }}</td>
                    <td class="p-4 text-sm text-muted-foreground">{{ $kasir->email }}</td>
                    <td class="p-4 text-center text-sm capitalize
                            {{ $kasir->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $kasir->is_active ? 'Active' : 'Inactive' }}
                    </td>
                    <td class="p-4 text-right">
                        <form method="POST" action="{{ route('admin.kasir.toggle', $kasir) }}" class="inline"
                            onsubmit="return confirm('Are you sure?')">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="text-xs capitalize
                                    {{ $kasir->is_active ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                {{ $kasir->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-8 text-center text-sm text-muted-foreground">No kasir accounts yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="create-kasir" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50"
    onclick="this.classList.add('hidden')">
    <div class="relative w-full max-w-md rounded-xl bg-card p-6 shadow-lg" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">New Kasir Account</h3>
        <form method="POST" action="{{ route('admin.kasir.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Name</label>
                <input name="name" required maxlength="255"
                    class="mt-1 w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
            </div>
            <div>
                <label class="text-sm font-medium">Email</label>
                <input name="email" required maxlength="255"
                    class="mt-1 w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
            </div>
            <div>
                <label class="text-sm font-medium">Password</label>
                <input name="password" required min="6"
                    class="mt-1 w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('create-kasir').classList.add('hidden')"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50">Cancel</button>
                <button type="submit"
                    class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90">Create</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelector('[data-modal="create-kasir"]').onclick = () => document.getElementById('create-kasir').classList.remove('hidden');
</script>
@endsection