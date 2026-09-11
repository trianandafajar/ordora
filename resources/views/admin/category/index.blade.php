@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Categories</h1>
        <button data-modal="create-category"
            class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 transition-opacity">
            Add Category
        </button>
    </div>

    @if(session('success'))
    <div class="rounded-md border border-green-300 bg-green-50 text-green-800 p-3 text-sm">{{ session('success') }}
    </div>
    @endif

    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-muted">
                <tr>
                    <th class="p-4 text-left text-sm font-medium">#</th>
                    <th class="p-4 text-left text-sm font-medium">Name</th>
                    <th class="p-4 text-left text-sm font-medium">Slug</th>
                    <th class="p-4 text-left text-sm font-medium">Products</th>
                    <th class="p-4 text-right text-sm font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $index => $cat)
                <tr class="border-t last:border-0 hover:bg-accent/30">
                    <td class="p-4 text-sm text-muted-foreground">{{ $index + 1 }}</td>
                    <td class="p-4 text-sm font-medium">{{ $cat->name }}</td>
                    <td class="p-4 text-sm text-muted-foreground font-mono">{{ $cat->slug }}</td>
                    <td class="p-4 text-sm text-muted-foreground">{{ $cat->products_count }}</td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button data-modal="edit-category-{{ $cat->id }}"
                                class="text-xs text-primary hover:underline">Edit</button>
                            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="inline"
                                onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-destructive hover:underline">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-sm text-muted-foreground">No categories yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Create Category Modal --}}
<div id="create-category" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50"
    onclick="this.classList.add('hidden')">
    <div class="relative w-full max-w-md rounded-xl bg-card p-6 shadow-lg" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">New Category</h3>
        <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input name="name" required maxlength="100"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    oninput="document.querySelector('[name=slug]').value || generateSlug(this.value)">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Slug</label>
                <input name="slug" maxlength="100"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('create-category').classList.add('hidden')"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50">Cancel</button>
                <button type="submit"
                    class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90">Create</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Category Modals --}}
@foreach($categories as $cat)
<div id="edit-category-{{ $cat->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50"
    onclick="this.classList.add('hidden')">
    <div class="relative w-full max-w-md rounded-xl bg-card p-6 shadow-lg" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">Edit Category</h3>
        <form method="POST" action="{{ route('admin.categories.update', $cat) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input name="name" required maxlength="100" value="{{ $cat->name }}"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                    oninput="document.querySelector('[name=slug]')?.value || generateSlug(this.value)">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Slug</label>
                <input name="slug" maxlength="100" value="{{ $cat->slug }}"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button"
                    onclick="document.getElementById('edit-category-{{ $cat->id }}').classList.add('hidden')"
                    class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50">Cancel</button>
                <button type="submit"
                    class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90">Save</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<script>
    function generateSlug(text) {
    return text.toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

document.querySelector('[data-modal="create-category"]').onclick = () => document.getElementById('create-category').classList.remove('hidden');

@foreach($categories as $cat)
document.querySelector('[data-modal="edit-category-{{ $cat->id }}"]').onclick = () => document.getElementById('edit-category-{{ $cat->id }}').classList.remove('hidden');
@endforeach

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id^="create-category"], [id^="edit-category-"]').forEach(el => el.classList.add('hidden'));
    }
});
</script>
@endsection