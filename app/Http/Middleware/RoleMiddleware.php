<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware accepts a Laravel-style `|`-separated role list:
 *   Route::middleware('role:admin|superadmin')
 *
 * Superadmin is always allowed through (it is the global escape hatch).
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $allowed = collect($roles)
            ->flatMap(fn ($r) => explode('|', $r))
            ->map(fn ($r) => trim($r))
            ->filter()
            ->values()
            ->all();

        if (in_array($user->role, $allowed, true)) {
            return $next($request);
        }

        abort(403, __('alert.role_unauthorized'));
    }
}