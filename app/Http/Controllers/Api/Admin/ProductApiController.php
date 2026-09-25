<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductApiController extends Controller
{
    public function index()
    {
        return ProductResource::collection(Product::with('category')->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_available' => ['boolean'],
        ]);

        $data['is_available'] = $request->boolean('is_available');
        $product = Product::create($data);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $filename = $product->slug.'-'.now()->format('Y-m-d').'.'.$ext;
            $path = $file->storeAs('products', $filename, 'public');
            $product->update(['image' => $path]);
        }

        return response()->json(['message' => 'Product created.', 'data' => new ProductResource($product)], 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_available' => ['boolean'],
        ]);

        $data['is_available'] = $request->boolean('is_available');
        $product->update($data);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $filename = $product->slug.'-'.now()->format('Y-m-d').'.'.$ext;
            $path = $file->storeAs('products', $filename, 'public');
            $product->update(['image' => $path]);
        }

        return response()->json(['message' => 'Product updated.', 'data' => new ProductResource($product)]);
    }

    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            return response()->json(['error' => 'Product has active orders and cannot be deleted.'], 422);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }
}
