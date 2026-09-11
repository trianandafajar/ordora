@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Categories</h1>
        <button x-data @click="$dispatch('open-modal', { id: 'create-category' })"
            class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 transition-opacity">
            Add Category
        </button>
    </div>



    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
        <table class="w-full">
            <thead class="bg-muted">
                <tr>
                    <th class="p-4 text-left text-sm font-medium">No</th>
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
                            <button x-data @click="$dispatch('open-modal', { id: 'edit-category-{{ $cat->id }}' })"
                                class="text-xs text-primary hover:underline">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>

                            </button>
                            <button x-data @click="$dispatch('open-modal', { id: 'delete-category-{{ $cat->id }}' })"
                                class="text-xs text-destructive hover:underline">
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
                    <td colspan="5" class="p-8 text-center text-sm text-muted-foreground">No categories yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-modal id="create-category" title="New Category">
    <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input name="name" required maxlength="100"
                class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                oninput="document.querySelector('[name=slug]').value = generateSlug(this.value)">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Slug</label>
            <input name="slug" maxlength="100"
                class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" @click="$dispatch('close-modal', { id: 'create-category' })"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50">Cancel</button>
            <button type="submit"
                class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90">Create</button>
        </div>
    </form>
</x-modal>

@foreach($categories as $cat)
<x-modal id="edit-category-{{ $cat->id }}" title="Edit Category">
    <form method="POST" action="{{ route('admin.categories.update', $cat) }}" class="space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input name="name" required maxlength="100" value="{{ $cat->name }}"
                class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                oninput="document.querySelector('[name=slug]').value = generateSlug(this.value)">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Slug</label>
            <input name="slug" maxlength="100" value="{{ $cat->slug }}"
                class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" @click="$dispatch('close-modal', { id: 'edit-category-{{ $cat->id }}' })"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50">Cancel</button>
            <button type="submit"
                class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90">Save</button>
        </div>
    </form>
</x-modal>

<x-modal id="delete-category-{{ $cat->id }}" title="Delete Category">
    <div class="space-y-4">
        <p class="text-sm text-muted-foreground">
            Are you sure you want to delete category <strong>"{{ $cat->name }}"</strong>? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-2">
            <button type="button" @click="$dispatch('close-modal', { id: 'delete-category-{{ $cat->id }}' })"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50">Cancel</button>
            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}">
                @csrf @method('DELETE')
                <button type="submit"
                    class="rounded-md bg-destructive text-destructive-foreground  text-white text-sm font-medium h-9 px-4 hover:opacity-90">Delete</button>
            </form>
        </div>
    </div>
</x-modal>
@endforeach

<script>
    function generateSlug(text) {
    return text.toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}
</script>
@endsection