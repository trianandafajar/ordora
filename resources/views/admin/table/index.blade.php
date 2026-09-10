@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Tables</h1>
        <button data-modal="create-table"
            class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 transition-opacity">
            Add Table
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
                    <th class="p-4 text-left text-sm font-medium">Number</th>
                    <th class="p-4 text-left text-sm font-medium">Status</th>
                    <th class="p-4 text-center text-sm font-medium">Capacity</th>
                    <th class="p-4 text-right text-sm font-medium">QR Token</th>
                    <th class="p-4 text-right text-sm font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tables as $tbl)
                <tr class="border-t last:border-0 hover:bg-accent/30">
                    <td class="p-4 text-sm font-medium">{{ $tbl->number }}</td>
                    <td
                        class="p-4 text-center text-sm capitalize
                            {{ $tbl->status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $tbl->status }}
                    </td>
                    <td class="p-4 text-center text-sm capitalize">{{ $tbl->capacity }}</td>
                    <td class="p-4 text-sm text-muted-foreground break-all">{{ substr($tbl->qr_token, 0, 8) }}...</td>
                    <td class="p-4 text-right">
                        <form method="POST" action="{{ route('admin.tables.regenQr', $tbl) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="text-xs bg-primary text-primary-foreground px-2 py-1 rounded hover:opacity-90">Regenerate
                                QR</button>
                        </form>
                        <form method="POST" action="{{ route('admin.tables.destroy', $tbl) }}" class="inline"
                            onsubmit="return confirm('Delete this table?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-destructive hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-sm text-muted-foreground">No tables yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="create-table" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50"
    onclick="this.classList.add('hidden')">
    <div class="relative w-full max-w-md rounded-xl bg-card p-6 shadow-lg" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">New Table</h3>
        <form method="POST" action="{{ route('admin.tables.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Number</label>
                <input name="number" required maxlength="20"
                    class="mt-1 w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
            </div>
            <div>
                <label class="text-sm font-medium">Capacity</label>
                <input name="capacity" type="number" required min="1" max="50"
                    class="mt-1 w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('create-table').classList.add('hidden')"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50">Cancel</button>
                <button type="submit"
                    class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90">Create</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelector('[data-modal="create-table"]').onclick = () => document.getElementById('create-table').classList.remove('hidden');
</script>
@endsection