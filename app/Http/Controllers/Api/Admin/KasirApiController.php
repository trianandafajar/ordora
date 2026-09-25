<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KasirApiController extends Controller
{
    public function index()
    {
        return UserResource::collection(User::where('role', 'kasir')->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $data['role'] = UserRole::Kasir;
        $data['is_active'] = true;
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json(['message' => 'Kasir account created.', 'data' => new UserResource($user)], 201);
    }

    public function toggle(User $user)
    {
        if ($user->role !== UserRole::Kasir) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return response()->json([
            'message' => 'Account '.($user->is_active ? 'activated' : 'deactivated').'.',
            'is_active' => $user->is_active,
        ]);
    }

    public function show($kasir)
    {
        $user = User::findOrFail($kasir);

        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['message' => 'Kasir updated.', 'data' => new UserResource($user)]);
    }

    public function destroy($kasir)
    {
        $user = User::findOrFail($kasir);

        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'Cannot delete yourself.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Kasir deleted.']);
    }
}
