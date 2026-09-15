@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Products</h1>
        <button x-data @click="$dispatch('open-modal', { id: 'create-product' })"
            class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 transition-opacity cursor-pointer">
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
                    <td class="p-4 font-medium">$ {{ number_format($prod->price, 0, '.', ',') }}</td>
                    <td class="p-4 text-center">
                        <span class="rounded-full px-2.5 py-0.5 text-xs capitalize font-medium
                                {{ $prod->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $prod->is_available ? 'Available' : 'Unavailable' }}
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <button x-data @click="$dispatch('open-modal', { id: 'edit-product-{{ $prod->id }}' })"
                                class="text-primary hover:underline cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>

                            </button>
                            <button x-data @click="$dispatch('open-modal', { id: 'delete-product-{{ $prod->id }}' })"
                                class="text-destructive hover:underline cursor-pointer">
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
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    oninput="document.querySelector('[name=slug]').value = generateSlug(this.value)">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Slug</label>
                <input name="slug" maxlength="255"
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Category</label>
                <select name="category_id" required
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Price</label>
                <input name="price" type="number" required min="0"
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="3"
                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring resize-none"></textarea>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Available</label>
                <select name="is_available"
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="$dispatch('close-modal', { id: 'create-product' })"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
            <button type="submit"
                class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Create
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
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    oninput="document.querySelector('[name=slug]').value = generateSlug(this.value)">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Slug</label>
                <input name="slug" maxlength="255" value="{{ $prod->slug }}"
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Category</label>
                <select name="category_id" required
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $prod->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name
                        }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium mb-1">Price</label>
                <input name="price" type="number" required min="0" value="{{ (int)$prod->price }}"
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="3"
                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring resize-none">{{ $prod->description }}</textarea>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Available</label>
                <select name="is_available"
                    class="w-full h-9 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    <option value="1" {{ $prod->is_available ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !$prod->is_available ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="$dispatch('close-modal', { id: 'edit-product-{{ $prod->id }}' })"
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
            <button type="submit"
                class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Save
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
                class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50 cursor-pointer">Cancel</button>
            <form method="POST" action="{{ route('admin.products.destroy', $prod) }}">
                @csrf @method('DELETE')
                <button type="submit"
                    class="rounded-md bg-destructive text-destructive-foreground text-sm text-white font-medium h-9 px-4 hover:opacity-90 cursor-pointer">Delete</button>
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