<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user has access to the festival referenced in the
 * route (`festival` parameter or `festival_id` query string).
 *
 * Behaviour:
 *  - superadmin → always allowed
 *  - global admin (users.role = 'admin' AND no festival scope required) → allowed
 *    only on /superadmin/* routes (handled by RoleMiddleware elsewhere)
 *  - everyone else → must have a row in festival_user for this festival,
 *    optionally constrained by `$role` (e.g. `festival.access:promoter`).
 *
 * Stores the resolved festival on the request as `festival` for downstream use.
 */
class EnsureFestivalAccess
{
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->isSuperAdmin()) {
            // Superadmins are global; we still resolve the festival model so
            // controllers have something to work with.
            $festival = $this->resolveFestival($request);
            if ($festival) {
                $request->attributes->set('festival', $festival);
            }
            return $next($request);
        }

        $festival = $this->resolveFestival($request);
        if (!$festival) {
            abort(404, 'Festival not found.');
        }

        if (!$user->hasFestivalAccess($festival->id, $role)) {
            abort(403, __('alert.no_festival_access'));
        }

        $request->attributes->set('festival', $festival);
        return $next($request);
    }

    private function resolveFestival(Request $request): ?\App\Models\Festival
    {
        // 1. Explicit route-model binding if the parameter is named `festival`.
        $festivalParam = $request->route('festival');
        if ($festivalParam instanceof \App\Models\Festival) {
            return $festivalParam;
        }

        // 2. Numeric ID provided in the URL.
        if ($festivalParam !== null && is_numeric($festivalParam)) {
            return \App\Models\Festival::find((int) $festivalParam);
        }

        // 3. Slug provided in the URL.
        if ($festivalParam !== null && is_string($festivalParam)) {
            return \App\Models\Festival::where('slug', $festivalParam)->first();
        }

        // 4. Explicit `festival_id` query string.
        if ($request->filled('festival_id')) {
            return \App\Models\Festival::find((int) $request->input('festival_id'));
        }

        return null;
    }
}