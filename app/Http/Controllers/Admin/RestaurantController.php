<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index()
    {
        $restaurants = Restaurant::with('owner')->latest()->get();
        $owners = User::where('role', 'admin')->whereNull('restaurant_id')->get();
        return view('superadmin.restaurants.index', compact('restaurants', 'owners'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'owner_id' => 'required|exists:users,id']);
        $restaurant = Restaurant::create([
            'name' => $request->name,
            'owner_id' => $request->owner_id,
            'is_active' => true,
        ]);
        User::find($request->owner_id)->update(['restaurant_id' => $restaurant->id]);

        $restaurant->load('owner');
        return response()->json([
            'success' => true,
            'restaurant' => $restaurant,
            'message' => 'Restaurant created successfully.'
        ]);
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $request->validate(['name' => 'required', 'owner_id' => 'required|exists:users,id']);

        $oldOwnerId = $restaurant->owner_id;
        $restaurant->update($request->only('name', 'owner_id'));

        if ($oldOwnerId != $request->owner_id) {
            User::find($oldOwnerId)->update(['restaurant_id' => null]);
            User::find($request->owner_id)->update(['restaurant_id' => $restaurant->id]);
        }

        $restaurant->load('owner');
        return response()->json([
            'success' => true,
            'restaurant' => $restaurant,
            'message' => 'Restaurant updated successfully.'
        ]);
    }

    public function toggle(Restaurant $restaurant)
    {
        $restaurant->update(['is_active' => !$restaurant->is_active]);
        return response()->json([
            'is_active' => $restaurant->is_active,
            'message' => 'Restaurant status updated successfully.'
        ]);
    }

    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['owner', 'staff', 'categories.products', 'tables']);
        return view('superadmin.restaurants.show', compact('restaurant'));
    }

    public function showTable(Restaurant $restaurant, $table)
    {
        $tableModel = Table::findOrFail($table);
        $tableModel->load(['orders' => fn ($q) => $q->where('status', '!=', \App\Enums\OrderStatus::Confirmed)->latest()]);
        return view('superadmin.restaurants.tables.show', ['restaurant' => $restaurant, 'table' => $tableModel]);
    }



    public function storeStaff(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => \App\Enums\UserRole::Kasir,
            'restaurant_id' => $restaurant->id,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function updateStaff(Request $request, Restaurant $restaurant, User $user)
    {
        if ($user->role !== \App\Enums\UserRole::Kasir) {
            return response()->json(['error' => 'Cannot edit admin'], 403);
        }

        $request->validate([
            'name' => 'required|max:255',
            'email' => ['required', 'email', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
        ]);

        $data = $request->only('name', 'email');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return response()->json(['success' => true, 'user' => $user]);
    }

    public function destroyStaff(Restaurant $restaurant, User $user)
    {
        if ($user->restaurant_id !== $restaurant->id || $user->role !== \App\Enums\UserRole::Kasir) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $user->delete();
        return response()->json(['success' => true]);
    }

    public function toggleStaff(Restaurant $restaurant, User $user)
    {
        if ($user->restaurant_id !== $restaurant->id || $user->role !== \App\Enums\UserRole::Kasir) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $user->update(['is_active' => !$user->is_active]);
        return response()->json(['is_active' => $user->is_active]);
    }

    public function storeCategory(Request $request, Restaurant $restaurant)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category = $restaurant->categories()->create(['name' => $request->name]);
        return response()->json(['success' => true, 'category' => $category]);
    }

    public function updateCategory(Request $request, Restaurant $restaurant, Category $category)
    {
        if ($category->restaurant_id !== $restaurant->id) return response()->json(['error' => 'Forbidden'], 403);
        $request->validate(['name' => 'required|string|max:255']);
        $category->update(['name' => $request->name]);
        return response()->json(['success' => true, 'category' => $category]);
    }

    public function destroyCategory(Restaurant $restaurant, Category $category)
    {
        if ($category->restaurant_id !== $restaurant->id) return response()->json(['error' => 'Forbidden'], 403);
        $category->delete();
        return response()->json(['success' => true]);
    }

    public function storeProduct(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('category_id', 'name', 'price', 'description');
        $data['restaurant_id'] = $restaurant->id;
        $data['is_available'] = true;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);
        return response()->json(['success' => true, 'product' => $product]);
    }

    public function updateProduct(Request $request, Restaurant $restaurant, Product $product)
    {
        if ($product->restaurant_id !== $restaurant->id) return response()->json(['error' => 'Forbidden'], 403);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_available' => 'nullable|boolean',
        ]);

        $data = $request->only('category_id', 'name', 'price', 'description');
        if ($request->has('is_available')) {
            $data['is_available'] = $request->boolean('is_available');
        }

        if ($request->hasFile('image')) {
            if ($product->image) \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);
        return response()->json(['success' => true, 'product' => $product]);
    }

    public function destroyProduct(Restaurant $restaurant, Product $product)
    {
        if ($product->restaurant_id !== $restaurant->id) return response()->json(['error' => 'Forbidden'], 403);
        if ($product->image) \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
        $product->delete();
        return response()->json(['success' => true]);
    }

    // Tables Management
    public function storeTable(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'table_number' => 'required|string|max:20',
            'capacity' => 'required|integer|min:1|max:50',
        ]);

        $table = $restaurant->tables()->create([
            'table_number' => $request->table_number,
            'capacity' => $request->capacity,
            'qr_token' => \Illuminate\Support\Str::random(32),
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'table' => $table]);
    }

    public function updateTable(Request $request, Restaurant $restaurant, Table $table)
    {
        if ($table->restaurant_id !== $restaurant->id) return response()->json(['error' => 'Forbidden'], 403);

        $request->validate([
            'table_number' => 'required|string|max:20',
            'capacity' => 'required|integer|min:1|max:50',
        ]);

        $table->update($request->only('table_number', 'capacity'));
        return response()->json(['success' => true, 'table' => $table]);
    }

    public function destroyTable(Restaurant $restaurant, Table $table)
    {
        if ($table->restaurant_id !== $restaurant->id) return response()->json(['error' => 'Forbidden'], 403);
        $table->delete();
        return response()->json(['success' => true]);
    }

}
