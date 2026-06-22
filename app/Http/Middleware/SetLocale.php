<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * P-072: Locale switcher middleware.
 *
 * Resolution order (first hit wins):
 *  1. `?lang=xx` query string (one-shot override — used by the
 *     switcher Livewire component to set the cookie + redirect away).
 *  2. `locale` session value (persisted by the switcher).
 *  3. `Accept-Language` header (browser preference, only on first visit).
 *  4. `APP_LOCALE` env (the platform default).
 *
 * Falls back to `APP_FALLBACK_LOCALE` when an unsupported locale is
 * supplied, so the app never lands on a half-translated page.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = array_keys((array) config('app.available_locales', []));

        // 1. One-shot ?lang= override (Livewire switcher redirect target).
        if ($request->filled('lang')) {
            $candidate = (string) $request->query('lang');
            if (in_array($candidate, $available, true)) {
                App::setLocale($candidate);
                Session::put('locale', $candidate);
            }
        }
        // 2. Session-stored preference.
        elseif (Session::has('locale')) {
            $candidate = (string) Session::get('locale');
            if (in_array($candidate, $available, true)) {
                App::setLocale($candidate);
            }
        }
        // 3. Browser header (best effort).
        elseif ($request->headers->has('Accept-Language')) {
            $preferred = $request->getPreferredLanguage($available);
            if ($preferred) {
                App::setLocale($preferred);
            }
        }

        return $next($request);
    }
}
