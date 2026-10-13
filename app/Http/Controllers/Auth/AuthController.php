<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'password.required' => 'Password is required.',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Account inactive, please contact Admin.',
            ]);
        }

        if (! auth()->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        $user = auth()->user();
        if (($user->role->value === 'admin' || $user->role->value === 'kasir') && $user->restaurant_id) {
            $restaurant = Restaurant::find($user->restaurant_id);
            if ($restaurant && ! $restaurant->is_active) {
                auth()->logout();
                throw ValidationException::withMessages([
                    'email' => 'Your restaurant account has been suspended. Please contact administrator.',
                ]);
            }
        }

        $request->session()->regenerate();

        return redirect()->intended(
            match (auth()->user()->role->value) {
                'superadmin' => route('superadmin.dashboard'),
                'admin' => '/admin/dashboard',
                default => '/cashier/dashboard',
            }
        );
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
