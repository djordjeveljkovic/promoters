<?php

namespace App\Http\Controllers;

use App\Models\Festival;
use Illuminate\Http\Request;

/**
 * P-064: public, no-login landing page for a festival.
 *
 * URL: /f/{slug}
 * Visibility: only festivals with `is_public = true` and `status = 'active'`.
 */
class PublicFestivalController extends Controller
{
    public function show(string $slug)
    {
        $festival = Festival::where('slug', $slug)
            ->where('is_public', true)
            ->where('status', 'active')
            ->with(['ticketTypes' => function ($q) {
                $q->orderBy('price');
            }, 'ticketTypes.commissions' => function ($q) {
                $q->whereNull('valid_to');
            }])
            ->firstOrFail();

        $ticketTypes = $festival->ticketTypes;

        return view('pages.public.festival', compact('festival', 'ticketTypes'));
    }
}
