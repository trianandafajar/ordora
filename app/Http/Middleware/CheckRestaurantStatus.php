<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRestaurantStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            if (!$user->is_active) {
                auth()->logout();
                return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact administrator.');
            }

            if (($user->role->value === 'admin' || $user->role->value === 'kasir') && $user->restaurant_id) {
                $restaurant = Restaurant::find($user->restaurant_id);
                if ($restaurant && !$restaurant->is_active) {
                    auth()->logout();
                    return redirect()->route('login')->with('error', 'Your restaurant account has been suspended. Please contact administrator.');
                }
            }
        }

        return $next($request);
    }
}
