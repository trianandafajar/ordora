@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Products</h1>
        <button data-modal="create-product" class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90 transition-opacity">
            Add Product
        </button>
    </div>

    @if(session('success'))
        <div class="rounded-md border border-green-300 bg-green-50 text-green-800 p-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-muted">
                <tr>
                    <th class="p-4 text-left text-sm font-medium">Name</th>
                    <th class="p-4 text-left text-sm font-medium">Category</th>
                    <th class="p-4 text-left text-sm font-medium">Price</th>
                    <th class="p-4 text-center text-sm font-medium">Status</th>
                    <th class="p-4 text-right text-sm font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $prod)
                    <tr class="border-t last:border-0 hover:bg-accent/30">
                        <td class="p-4 text-sm font-medium">{{ $prod->name }}</td>
                        <td class="p-4 text-sm text-muted-foreground">{{ $prod->category->name ?? '-' }}</td>
                        <td class="p-4 text-sm">Rp {{ number_format($prod->price, 0, ',', '.') }}</td>
                        <td class="p-4 text-center text-sm">
                            <span class="rounded-full px-2.5 py-0.5 text-xs capitalize
                                {{ $prod->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $prod->is_available ? 'Available' : 'Unavailable' }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <form method="POST" action="{{ route('admin.products.update', $prod) }}" class="inline-flex items-center gap-2">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $prod->name }}" class="h-8 w-32 rounded-md border border-input bg-transparent px-1 text-sm">
                                <button type="submit" class="text-xs bg-primary text-primary-foreground px-2 py-1 rounded hover:opacity-90">Save</button>
                            </form>
                            <form method="POST" action="{{ route('admin.products.destroy', $prod) }}" class="inline" onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-destructive hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-sm text-muted-foreground">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div id="create-product" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50" onclick="this.classList.add('hidden')">
    <div class="relative w-full max-w-md rounded-xl bg-card p-6 shadow-lg" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">New Product</h3>
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Name</label>
                    <input name="name" required maxlength="255" class="mt-1 w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                </div>
                <div>
                    <label class="text-sm font-medium">Category</label>
                    <select name="category_id" class="mt-1 w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Price</label>
                    <input name="price" type="number" required min="0" class="mt-1 w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm outline-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                </div>
                <div>
                    <label class="text-sm font-medium">Description</label>
                    <textarea name="description" rows="2" class="mt-1 w-full h-24 rounded-md border border-input bg-transparent px-3 text-sm outline-none resize-none transition-colors focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"></textarea>
                </div>
                <div>
                    <label class="text-sm font-medium">Image</label>
                    <input type="file" name="image" class="mt-1 w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Available</label>
                    <select name="is_available" class="mt-1 w-full h-9 rounded-md border border-input bg-transparent px-3 text-sm">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('create-product').classList.add('hidden')" class="rounded-md border text-sm font-medium h-9 px-4 hover:bg-accent/50">Cancel</button>
                <button type="submit" class="rounded-md bg-primary text-primary-foreground text-sm font-medium h-9 px-4 hover:opacity-90">Create</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelector('[data-modal="create-product"]').onclick = () => document.getElementById('create-product').classList.remove('hidden');
</script>
@endsection