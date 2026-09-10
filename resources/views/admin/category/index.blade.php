@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Categories</h1>
        <button data-modal="create-category" class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 transition-opacity">
            Add Category
        </button>
    </div>

    @if(session('success'))
        <div class="rounded-md border border-green-300 bg-green-50 text-green-800 p-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl border bg-card shadow-sm">
        <table class="w-full">
            <thead class="bg-muted">
                <tr>
                    <th class="p-4 text-left text-sm font-medium">Name</th>
                    <th class="p-4 text-left text-sm font-medium">Products</th>
                    <th class="p-4 text-right text-sm font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr class="border-t last:border-0 hover:bg-accent/30">
                        <td class="p-4 text-sm font-medium">{{ $cat->name }}</td>
                        <td class="p-4 text-sm text-muted-foreground">{{ $cat->products_count }}</td>
                        <td class="p-4 text-right">
                            <form method="POST" action="{{ route('admin.categories.update', $cat) }}" class="inline-flex items-center gap-2">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $cat->name }}" class="h-8 w-40 rounded-md border border-input bg-transparent px-2 text-sm">
                                <button type="submit" class="text-xs bg-primary text-primary-foreground px-2 py-1 rounded hover:opacity-90">Save</button>
                            </form>
                            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-destructive hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-8 text-center text-sm text-muted-foreground">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div id="create-category" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50" onclick="this.classList.add('hidden')">
    <div class="relative w-full max-w-md rounded-xl bg-card p-6 shadow-lg" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">New Category</h3>
        <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Name</label>
                <input name="name" required maxlength="100" class="mt-1 w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('create-category').classList.add('hidden')" class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50">Cancel</button>
                <button type="submit" class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90">Create</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelector('[data-modal="create-category"]').onclick = () => document.getElementById('create-category').classList.remove('hidden');
</script>
@endsection