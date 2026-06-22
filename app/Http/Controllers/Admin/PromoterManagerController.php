<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Models\FestivalUser;
use App\Models\ManagerCommission;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Festival-admin endpoints for managing promoter managers and their
 * per-ticket-type commission overrides.
 *
 * The `TicketCommission` table is the *default* commission for every
 * promoter manager.  When admin wants to give a specific manager a
 * different rate, they create a `ManagerCommission` here.
 *
 * Note: festival and manager route params are typed `string` rather than
 * `Festival`/`User` because the URL passes slugs and we resolve them
 * explicitly — implicit route-model binding would try to look them up
 * by primary key and fail (404) for non-numeric slugs.
 */
class PromoterManagerController extends Controller
{
    /**
     * List all promoter managers on the festival (with their current
     * commission override, if any).
     */
    public function index(Request $request, $festival)
    {
        $festival = $this->resolveFestival($request, $festival);
        $user = $request->user();
        if (!$user->isFestivalAdmin($festival->id)) {
            abort(403, __('alert.role_unauthorized'));
        }

        $managers = $festival->users()
            ->wherePivot('role_in_festival', 'promoter_manager')
            ->withPivot(['role_in_festival', 'assigned_by', 'assigned_at'])
            ->orderBy('name')
            ->get();

        // Pre-fetch ticket types so we can render a small status column
        // for each manager (how many overrides they have set).
        $ticketTypeIds = $festival->ticketTypes()->pluck('id');
        $overrideCounts = ManagerCommission::query()
            ->whereIn('festival_user_id', $managers->pluck('pivot.id'))
            ->whereNull('valid_to')
            ->selectRaw('festival_user_id, count(*) as cnt')
            ->groupBy('festival_user_id')
            ->pluck('cnt', 'festival_user_id');

        return view('pages.admin.promoter-managers.index', [
            'festival'        => $festival,
            'managers'        => $managers,
            'ticketTypeCount' => $ticketTypeIds->count(),
            'overrideCounts'  => $overrideCounts,
        ]);
    }

    /**
     * Show the commission-overrides editor for a single manager.
     */
    public function show(Request $request, $festival, $manager)
    {
        $festival = $this->resolveFestival($request, $festival);
        $user = $request->user();
        if (!$user->isFestivalAdmin($festival->id)) {
            abort(403, __('alert.role_unauthorized'));
        }

        $manager = $this->resolveUser($manager);

        $pivot = FestivalUser::where('festival_id', $festival->id)
            ->where('user_id', $manager->id)
            ->where('role_in_festival', 'promoter_manager')
            ->firstOrFail();

        $ticketTypes = $festival->ticketTypes()->orderBy('name')->get();

        $overrides = ManagerCommission::query()
            ->where('festival_user_id', $pivot->id)
            ->whereNull('valid_to')
            ->get()
            ->keyBy('ticket_type_id');

        $defaults = \App\Models\TicketCommission::query()
            ->whereIn('ticket_type_id', $ticketTypes->pluck('id'))
            ->whereNull('valid_to')
            ->orderBy('min_sold')
            ->get()
            ->groupBy('ticket_type_id')
            ->map(fn ($rows) => $rows->firstWhere('min_sold', '<=', 1) ?? $rows->first());

        return view('pages.admin.promoter-managers.show', [
            'festival'    => $festival,
            'manager'     => $manager,
            'pivot'       => $pivot,
            'ticketTypes' => $ticketTypes,
            'overrides'   => $overrides,
            'defaults'    => $defaults,
        ]);
    }

    /**
     * Save commission overrides for the manager.  Each ticket type can
     * have an override; if the admin leaves a field empty, the existing
     * override (if any) is closed and the manager falls back to the
     * default TicketCommission.
     */
    public function update(Request $request, $festival, $manager)
    {
        $festival = $this->resolveFestival($request, $festival);
        $user = $request->user();
        if (!$user->isFestivalAdmin($festival->id)) {
            abort(403, __('alert.role_unauthorized'));
        }

        $manager = $this->resolveUser($manager);

        $pivot = FestivalUser::where('festival_id', $festival->id)
            ->where('user_id', $manager->id)
            ->where('role_in_festival', 'promoter_manager')
            ->firstOrFail();

        $data = $request->validate([
            'commissions'   => ['array'],
            'commissions.*' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
        ]);

        DB::transaction(function () use ($data, $pivot) {
            // Close any existing active override for every ticket type
            // belonging to this festival — we'll reopen the ones the
            // admin submitted a non-empty value for.
            ManagerCommission::query()
                ->where('festival_user_id', $pivot->id)
                ->whereNull('valid_to')
                ->update(['valid_to' => now()]);

            foreach (($data['commissions'] ?? []) as $ticketTypeId => $amount) {
                if ($amount === null || $amount === '') continue;
                ManagerCommission::create([
                    'festival_user_id'   => $pivot->id,
                    'ticket_type_id'     => (int) $ticketTypeId,
                    'commission_amount'  => (float) $amount,
                    'valid_from'         => now(),
                    'valid_to'           => null,
                    'set_by'             => Auth::id(),
                ]);
            }
        });

        return redirect()
            ->route('admin.promoter-managers.show', ['festival' => $festival->slug, 'manager' => $manager->id])
            ->with('success', __('alert.commissions_saved'));
    }

    /**
     * Look up the festival from either:
     *  - a Festival model passed directly (e.g. unit tests)
     *  - the request attributes set by EnsureFestivalAccess middleware
     *  - a slug string from the URL
     */
    private function resolveFestival(Request $request, $festival): Festival
    {
        if ($festival instanceof Festival) {
            return $festival;
        }
        $attr = $request->attributes->get('festival');
        if ($attr instanceof Festival) {
            return $attr;
        }
        $f = Festival::where('slug', $festival)->first();
        if (!$f) {
            abort(404, 'Festival not found.');
        }
        return $f;
    }

    /**
     * Accept a User model or an integer id.
     */
    private function resolveUser($user): User
    {
        if ($user instanceof User) {
            return $user;
        }
        return User::findOrFail($user);
    }
}
