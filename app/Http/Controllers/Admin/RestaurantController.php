<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
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

    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();
        return response()->json([
            'success' => true,
            'message' => 'Restaurant deleted successfully.'
        ]);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => "password",
            'role' => 'admin',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'User created successfully.'
        ]);
    }
}
