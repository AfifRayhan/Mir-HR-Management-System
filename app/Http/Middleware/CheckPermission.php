<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * The $permission parameter is now a menu item slug.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'You do not have the required permission to access this page.');
        }

        // HR Admin role bypasses all permission checks
        if (optional($user->role)->name === 'HR Admin') {
            return $next($request);
        }

        if (!$user->hasMenuAccess($permission)) {
            abort(403, 'You do not have the required permission to access this page.');
        }

        return $next($request);
    }
}
