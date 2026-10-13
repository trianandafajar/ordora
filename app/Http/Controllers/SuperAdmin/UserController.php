<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::whereIn('role', [UserRole::Admin->value, UserRole::Kasir->value])
            ->latest()
            ->get();

        $restaurants = Restaurant::all();

        return view('superadmin.users.index', compact('users', 'restaurants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['admin', 'kasir'])],
            'restaurant_id' => 'nullable|exists:restaurants,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'restaurant_id' => $request->restaurant_id,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'User created successfully.',
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(['admin', 'kasir'])],
            'restaurant_id' => 'nullable|exists:restaurants,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'restaurant_id' => $request->restaurant_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'User updated successfully.',
        ]);
    }

    public function toggle(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        return response()->json([
            'is_active' => $user->is_active,
            'message' => 'User status updated successfully.',
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}
