<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KasirController extends Controller
{
    public function index()
    {
        $kasirs = User::where('role', 'kasir')->latest()->get();

        return view('admin.kasir.index', compact('kasirs'));
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

        User::create($data);

        return back()->with('success', 'Kasir account created.');
    }

    public function toggle(User $user)
    {
        if ($user->role !== UserRole::Kasir) {
            abort(403);
        }

        $user->update(['is_active' => ! $user->is_active]);
        $label = $user->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Account {$label}.",
            'is_active' => $user->is_active,
        ]);
    }

    public function edit(User $user)
    {
        return response()->json($user);
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

        return back()->with('success', 'Kasir updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete yourself.');
        }

        $user->delete();

        return back()->with('success', 'Kasir deleted.');
    }
}
