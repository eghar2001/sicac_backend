<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * @param  array<int, string>  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $acceptedRoles = config('app.accepted_roles', []);
        $invalidRoles = array_diff($roles, $acceptedRoles);

        if ($invalidRoles) {
            return response()->json([
                'message' => 'Invalid role.',
                'roles' => array_values($invalidRoles),
            ], 400);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
