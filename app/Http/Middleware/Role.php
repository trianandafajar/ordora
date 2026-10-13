<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            abort(403);
        }

        if (auth()->user()->role === UserRole::SuperAdmin) {
            return $next($request);
        }

        if (! in_array(auth()->user()->role->value, $roles)) {
            abort(403);
        }

        return $next($request);
    }
}
