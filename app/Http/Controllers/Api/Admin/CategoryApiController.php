<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(Category::withCount('products')->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:categories,name']]);
        $category = Category::create($data);

        return response()->json(['message' => 'Category created.', 'data' => new CategoryResource($category)], 201);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:categories,name,'.$category->id]]);
        $category->update($data);

        return response()->json(['message' => 'Category updated.', 'data' => new CategoryResource($category)]);
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return response()->json(['error' => 'Category has products and cannot be deleted.'], 422);
        }
        $category->delete();

        return response()->json(['message' => 'Category deleted.']);
    }
}
