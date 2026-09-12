<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.product.index', compact('products', 'categories'));
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

        return back()->with('success', 'Product created.');
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

        return back()->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            return back()->with('error', "Product \"{$product->name}\" has {$product->orderItems()->count()} order item(s) and cannot be deleted.");
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $name = $product->name;
        $product->delete();

        return back()->with('success', "Product \"{$name}\" deleted.");
    }
}
