<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * P-070: public promoter profile.
 *
 * Anyone can browse to /p/{id}; only promoters who have flipped
 * `users.is_public = true` are actually rendered (everyone else gets a
 * 404 — same behaviour as a private profile on Instagram).
 */
class PublicPromoterController extends Controller
{
    public function show(Request $request, string $id)
    {
        $promoter = User::query()
            ->whereIn('role', ['promoter', 'sub_promoter'])
            ->where('is_public', true)
            ->findOrFail($id);

        // Only show festivals the promoter actually sells for.
        $festivals = $promoter->festivals()
            ->wherePivotIn('role_in_festival', ['promoter', 'promoter_manager'])
            ->orderByDesc('year')
            ->orderBy('name')
            ->get();

        return view('pages.public.promoter', [
            'promoter' => $promoter,
            'festivals' => $festivals,
        ]);
    }
}
