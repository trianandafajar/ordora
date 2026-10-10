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
        return view('admin.restaurants.index', compact('restaurants'));
    }

    public function create()
    {
        $owners = User::where('role', 'admin')->whereNull('restaurant_id')->get();
        return view('admin.restaurants.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'owner_id' => 'required|exists:users,id']);
        $restaurant = Restaurant::create($request->only('name', 'owner_id'));
        User::find($request->owner_id)->update(['restaurant_id' => $restaurant->id]);
        return redirect()->route('admin.restaurants.index');
    }

    public function edit(Restaurant $restaurant)
    {
        $owners = User::where('role', 'admin')->where(function($query) use ($restaurant) {
            $query->whereNull('restaurant_id')->orWhere('id', $restaurant->owner_id);
        })->get();
        return view('admin.restaurants.edit', compact('restaurant', 'owners'));
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
        
        return redirect()->route('admin.restaurants.index');
    }

    public function toggle(Restaurant $restaurant)
    {
        $restaurant->update(['is_active' => !$restaurant->is_active]);
        return response()->json([
            'is_active' => $restaurant->is_active,
            'message' => 'Restaurant status updated successfully.'
        ]);
    }
}
