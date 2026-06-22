<?php

namespace App\Providers;

use App\Models\Festival;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Allow festival routes to resolve by either numeric id or
        // string slug.  Without this binding, /superadmin/festivals/refest-2026
        // 404s because `refest-2026` isn't a numeric id and the
        // Superadmin\FestivalController uses implicit route-model
        // binding (`Festival $festival`).
        //
        // The binding is opt-in: it only kicks in for the superadmin
        // routes that declare the parameter as a model.  Admin/promoter
        // routes accept `string $festival` and resolve themselves.
        Route::bind('festival', function ($value) {
            if ($value instanceof Festival) {
                return $value;
            }
            if (is_numeric($value)) {
                return Festival::find((int) $value) ?? abort(404, 'Festival not found.');
            }
            return Festival::where('slug', $value)->first() ?? abort(404, 'Festival not found.');
        });
    }
}
