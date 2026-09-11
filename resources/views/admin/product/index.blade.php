@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Products</h1>
        <button x-data @click="$dispatch('open-modal', { id: 'create-product' })"
            class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 transition-opacity">
            Add Product
        </button>
    </div>

    <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-muted">
                <tr>
                    <th class="p-4 text-left font-medium w-16">Image</th>
                    <th class="p-4 text-left font-medium">Name</th>
                    <th class="p-4 text-left font-medium">Category</th>
                    <th class="p-4 text-left font-medium">Price</th>
                    <th class="p-4 text-center font-medium">Status</th>
                    <th class="p-4 text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $prod)
                <tr class="border-t last:border-0 hover:bg-accent/30">
                    <td class="p-4">
                        <div
                            class="h-10 w-10 rounded-md bg-muted flex items-center justify-center overflow-hidden border">
                            @if($prod->image)
                            <img src="{{ asset('storage/' . $prod->image) }}" class="h-full w-full object-cover">
                            @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-muted-foreground/50" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            @endif
                        </div>
                    </td>
                    <td class="p-4">
                        <div class="font-medium">{{ $prod->name }}</div>
                        <div class="text-xs text-muted-foreground font-mono">{{ $prod->slug }}</div>
                    </td>
                    <td class="p-4 text-muted-foreground">{{ $prod->category->name ?? '-' }}</td>
                    <td class="p-4 font-medium">Rp {{ number_format($prod->price, 0, ',', '.') }}</td>
                    <td class="p-4 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-xs capitalize font-medium
                                {{ $prod->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $prod->is_available ? 'Available' : 'Unavailable' }}
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <button x-data @click="$dispatch('open-modal', { id: 'edit-product-{{ $prod->id }}' })"
                                class="text-primary hover:underline">Edit</button>
                            <button x-data @click="$dispatch('open-modal', { id: 'delete-product-{{ $prod->id }}' })"
                                class="text-destructive hover:underline">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-muted-foreground">No products yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- create modal --}}
<x-modal id="create-product" title="New Product" maxWidth="max-w-2xl">
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Product Image</label>
                <x-image-upload name="image" />
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Name</label>
                <input name="name" required maxlength="255"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    oninput="document.querySelector('[name=slug]').value = generateSlug(this.value)">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Slug</label>
                <input name="slug" maxlength="255"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Category</label>
                <select name="category_id" required
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Price</label>
                <input name="price" type="number" required min="0"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="3"
                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring resize-none"></textarea>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Available</label>
                <select name="is_available"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="$dispatch('close-modal', { id: 'create-product' })"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50">Cancel</button>
            <button type="submit"
                class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90">Create
                Product</button>
        </div>
    </form>
</x-modal>

{{-- edit & delete modals --}}
@foreach($products as $prod)
<x-modal id="edit-product-{{ $prod->id }}" title="Edit Product" maxWidth="max-w-2xl">
    <form method="POST" action="{{ route('admin.products.update', $prod) }}" enctype="multipart/form-data"
        class="space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Product Image</label>
                <x-image-upload name="image" :existing="$prod->image ? asset('storage/' . $prod->image) : null" />
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Name</label>
                <input name="name" required maxlength="255" value="{{ $prod->name }}"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    oninput="document.querySelector('[name=slug]').value = generateSlug(this.value)">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Slug</label>
                <input name="slug" maxlength="255" value="{{ $prod->slug }}"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Category</label>
                <select name="category_id" required
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $prod->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name
                        }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Price</label>
                <input name="price" type="number" required min="0" value="{{ (int)$prod->price }}"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="3"
                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring resize-none">{{ $prod->description }}</textarea>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Available</label>
                <select name="is_available"
                    class="w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    <option value="1" {{ $prod->is_available ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !$prod->is_available ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="$dispatch('close-modal', { id: 'edit-product-{{ $prod->id }}' })"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50">Cancel</button>
            <button type="submit"
                class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90">Save
                Changes</button>
        </div>
    </form>
</x-modal>

<x-modal id="delete-product-{{ $prod->id }}" title="Delete Product">
    <div class="space-y-4">
        <p class="text-sm text-muted-foreground">
            Are you sure you want to delete product <strong>"{{ $prod->name }}"</strong>? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-2">
            <button type="button" @click="$dispatch('close-modal', { id: 'delete-product-{{ $prod->id }}' })"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50">Cancel</button>
            <form method="POST" action="{{ route('admin.products.destroy', $prod) }}">
                @csrf @method('DELETE')
                <button type="submit"
                    class="rounded-md bg-destructive text-destructive-foreground text-sm font-medium h-9 px-4 hover:opacity-90">Delete</button>
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