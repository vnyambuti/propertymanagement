<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            // Assuming user has a hasRole method or you're checking a role relationship
            // if ($user->hasRole($role)) {
            //     return $next($request);
            // }
        }

        // If user doesn't have any of the required roles, 403 Forbidden response
        return response()->json(['message' => 'You do not have permission to access this resource'], 403);
    }
}
