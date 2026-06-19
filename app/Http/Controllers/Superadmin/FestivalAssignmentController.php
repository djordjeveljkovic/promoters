<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Manages which users are assigned to which festivals and in which role.
 *
 * The same user can be an admin on one festival and a promoter on another,
 * so we operate on rows of `festival_user` directly rather than on the
 * user model.
 */
class FestivalAssignmentController extends Controller
{
    public function show(Festival $festival)
    {
        $festival->load([
            'admins',
            'promoters',
            'subPromoters',
        ]);

        $existingUserIds = collect([
            $festival->admins->pluck('id'),
            $festival->promoters->pluck('id'),
            $festival->subPromoters->pluck('id'),
        ])->flatten()->unique()->values();

        $availableUsers = User::query()
            ->whereNotIn('id', $existingUserIds)
            ->where('role', '!=', 'superadmin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        $candidates = User::where('role', 'superadmin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role'])
            ->merge($availableUsers);

        return view('pages.superadmin.festivals.assign', compact('festival', 'candidates'));
    }

    public function store(Request $request, Festival $festival)
    {
        $data = $request->validate([
            'user_id'          => ['required', 'exists:users,id'],
            'role_in_festival' => ['required', Rule::in(['admin', 'promoter', 'sub_promoter'])],
        ]);

        $festival->users()->attach($data['user_id'], [
            'role_in_festival' => $data['role_in_festival'],
            'assigned_by'      => Auth::id(),
            'assigned_at'      => now(),
        ]);

        return back()->with('success', __('alert.assignment_added'));
    }

    public function destroy(Festival $festival, User $user)
    {
        $festival->users()->detach($user->id);
        return back()->with('success', __('alert.assignment_removed'));
    }
}