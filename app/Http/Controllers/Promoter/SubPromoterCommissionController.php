<?php

namespace App\Http\Controllers\Promoter;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Models\FestivalUser;
use App\Models\ManagerCommission;
use App\Models\SubPromoterCommission;
use App\Models\TicketCommission;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Promoter-manager endpoints for managing their sub-promoters and the
 * commission they earn.
 *
 * The sub-promoter's commission is always <= the manager's own
 * commission for the same ticket type — the manager's payout is
 * `max(0, manager_commission - sub_promoter_commission)`.  We enforce
 * this in `update()` so the UI cannot accidentally promise a manager
 * less than zero.
 */
class SubPromoterCommissionController extends Controller
{
    public function __construct()
    {
        // Auth is enforced by the route group middleware. Nothing to do here.
    }

    /**
     * List of sub-promoters the manager has on this festival, with
     * each one's current commission overrides.
     */
    public function index(Request $request, $festival)
    {
        $festival = $this->resolveFestival($request, $festival);
        $manager = $request->user();
        if (!$manager->isPromoterManager($festival->id)) {
            abort(403, __('alert.sub_promoter_manager_required', [
                'festival' => $festival->displayName(),
            ]));
        }

        $subPromoters = $manager->subPromoters()
            ->whereHas('festivalAssignments', function ($q) use ($festival, $manager) {
                $q->where('festival_id', $festival->id)
                    ->where('role_in_festival', 'sub_promoter')
                    ->where('user_id', '!=', $manager->id);
            })
            ->with(['festivalAssignments' => function ($q) use ($festival) {
                $q->where('festival_id', $festival->id);
            }])
            ->get();

        $ticketTypes = $festival->ticketTypes()->orderBy('name')->get();

        $pivotIds = $subPromoters
            ->pluck('festivalAssignments.*.id')
            ->flatten()
            ->filter()
            ->values();

        $overrides = SubPromoterCommission::query()
            ->whereIn('festival_user_id', $pivotIds)
            ->whereNull('valid_to')
            ->get()
            ->groupBy('festival_user_id')
            ->map->keyBy('ticket_type_id');

        // The manager's own commission per ticket type (override or default).
        $managerPivot = $manager->festivalAssignments()
            ->where('festival_id', $festival->id)
            ->where('role_in_festival', 'promoter_manager')
            ->first();

        $managerOverrides = ManagerCommission::query()
            ->where('festival_user_id', $managerPivot?->id ?? 0)
            ->whereNull('valid_to')
            ->get()
            ->keyBy('ticket_type_id');

        $defaults = TicketCommission::query()
            ->whereIn('ticket_type_id', $ticketTypes->pluck('id'))
            ->whereNull('valid_to')
            ->orderBy('min_sold')
            ->get()
            ->groupBy('ticket_type_id')
            ->map(fn ($rows) => $rows->firstWhere('min_sold', '<=', 1) ?? $rows->first());

        return view('pages.promoter.sub-promoters.index', [
            'festival'         => $festival,
            'subPromoters'     => $subPromoters,
            'ticketTypes'      => $ticketTypes,
            'overrides'        => $overrides,
            'managerOverrides' => $managerOverrides,
            'defaults'         => $defaults,
        ]);
    }

    /**
     * Editor for a single sub-promoter's commissions.
     */
    public function show(Request $request, $festival, $subPromoter)
    {
        $festival = $this->resolveFestival($request, $festival);
        $manager = $request->user();
        $subPromoter = $this->resolveUser($subPromoter);
        $this->authorize($manager, $festival, $subPromoter);

        $ticketTypes = $festival->ticketTypes()->orderBy('name')->get();

        $subPivot = FestivalUser::where('festival_id', $festival->id)
            ->where('user_id', $subPromoter->id)
            ->where('role_in_festival', 'sub_promoter')
            ->firstOrFail();

        $overrides = SubPromoterCommission::query()
            ->where('festival_user_id', $subPivot->id)
            ->whereNull('valid_to')
            ->get()
            ->keyBy('ticket_type_id');

        $managerPivot = $manager->festivalAssignments()
            ->where('festival_id', $festival->id)
            ->where('role_in_festival', 'promoter_manager')
            ->first();

        $managerOverrides = ManagerCommission::query()
            ->where('festival_user_id', $managerPivot?->id ?? 0)
            ->whereNull('valid_to')
            ->get()
            ->keyBy('ticket_type_id');

        $defaults = TicketCommission::query()
            ->whereIn('ticket_type_id', $ticketTypes->pluck('id'))
            ->whereNull('valid_to')
            ->orderBy('min_sold')
            ->get()
            ->groupBy('ticket_type_id')
            ->map(fn ($rows) => $rows->firstWhere('min_sold', '<=', 1) ?? $rows->first());

        return view('pages.promoter.sub-promoters.show', [
            'festival'         => $festival,
            'subPromoter'      => $subPromoter,
            'subPivot'         => $subPivot,
            'ticketTypes'      => $ticketTypes,
            'overrides'        => $overrides,
            'managerOverrides' => $managerOverrides,
            'defaults'         => $defaults,
        ]);
    }

    /**
     * Save the sub-promoter's commission overrides.
     *
     * The sub-promoter's commission per ticket type must be <= the
     * manager's own commission for that ticket type — otherwise the
     * manager would end up with a negative payout.
     */
    public function update(Request $request, $festival, $subPromoter)
    {
        $festival = $this->resolveFestival($request, $festival);
        $manager = $request->user();
        $subPromoter = $this->resolveUser($subPromoter);
        $this->authorize($manager, $festival, $subPromoter);

        $subPivot = FestivalUser::where('festival_id', $festival->id)
            ->where('user_id', $subPromoter->id)
            ->where('role_in_festival', 'sub_promoter')
            ->firstOrFail();

        $data = $request->validate([
            'commissions'   => ['array'],
            'commissions.*' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
        ]);

        // Cap each override at the manager's own commission for the
        // same ticket type, so the manager never goes negative.
        $managerPivot = $manager->festivalAssignments()
            ->where('festival_id', $festival->id)
            ->where('role_in_festival', 'promoter_manager')
            ->first();

        $managerOverrides = ManagerCommission::query()
            ->where('festival_user_id', $managerPivot?->id ?? 0)
            ->whereNull('valid_to')
            ->get()
            ->keyBy('ticket_type_id');

        $defaults = TicketCommission::query()
            ->whereIn('ticket_type_id', array_keys($data['commissions'] ?? []))
            ->whereNull('valid_to')
            ->orderBy('min_sold')
            ->get()
            ->groupBy('ticket_type_id')
            ->map(fn ($rows) => $rows->firstWhere('min_sold', '<=', 1) ?? $rows->first());

        $errors = [];
        $clean = [];
        foreach (($data['commissions'] ?? []) as $ticketTypeId => $amount) {
            if ($amount === null || $amount === '') continue;

            $managerComm = (float) ($managerOverrides[$ticketTypeId]->commission_amount
                ?? $defaults[$ticketTypeId]->commission_amount
                ?? 0);

            if ((float) $amount > $managerComm) {
                $errors["commissions.{$ticketTypeId}"] = __('alert.sub_commission_cannot_exceed_manager', [
                    'manager' => number_format($managerComm, 2),
                ]);
                continue;
            }
            $clean[(int) $ticketTypeId] = (float) $amount;
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($subPivot, $clean) {
            SubPromoterCommission::query()
                ->where('festival_user_id', $subPivot->id)
                ->whereNull('valid_to')
                ->update(['valid_to' => now()]);

            foreach ($clean as $ticketTypeId => $amount) {
                SubPromoterCommission::create([
                    'festival_user_id'   => $subPivot->id,
                    'ticket_type_id'     => $ticketTypeId,
                    'commission_amount'  => $amount,
                    'valid_from'         => now(),
                    'valid_to'           => null,
                    'set_by'             => Auth::id(),
                ]);
            }
        });

        return redirect()
            ->route('promoter.sub-promoters.show', ['festival' => $festival->slug, 'subPromoter' => $subPromoter->id])
            ->with('success', __('alert.sub_commissions_saved'));
    }

    /**
     * Make sure the calling user is allowed to manage this sub-promoter
     * on the given festival.
     *
     * The two checks are deliberately separated with distinct messages
     * so the user (or an admin debugging "why am I getting a 403?")
     * can tell what went wrong:
     *
     *   1. "must be a promoter manager on this festival" — happens
     *      when a plain promoter tries to access the endpoint, or
     *      when a manager tries the URL on a festival they aren't
     *      promoted on.  Superadmins and festival admins always
     *      pass this check (admins can rescue sub-promoters whose
     *      parent_id has somehow drifted).
     *   2. "must be the parent of this sub-promoter" — happens when
     *      a manager A tries to edit a sub-promoter that belongs to
     *      manager B (which is correct: you can only manage your own).
     *      Superadmins and festival admins skip this check too.
     */
    private function authorize(User $manager, Festival $festival, User $subPromoter): void
    {
        // Admins and superadmins can always manage sub-promoters.
        if ($manager->isSuperAdmin() || $manager->isFestivalAdmin($festival->id)) {
            return;
        }

        if (!$manager->isPromoterManager($festival->id)) {
            abort(403, __('alert.sub_promoter_manager_required', [
                'festival' => $festival->displayName(),
            ]));
        }
        if ($subPromoter->parent_id !== $manager->id) {
            abort(403, __('alert.sub_promoter_owner_required', [
                'name' => $subPromoter->name,
            ]));
        }
    }

    /**
     * Look up the festival from either:
     *  - a Festival model passed directly (e.g. unit tests)
     *  - the request attributes set by EnsureFestivalAccess middleware
     *  - a slug string from the URL (default in production routes)
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
     * Same trick for the sub-promoter: accept either a User model or an
     * integer id (the test suite passes models, the route passes ids).
     */
    private function resolveUser($user): User
    {
        if ($user instanceof User) {
            return $user;
        }
        return User::findOrFail($user);
    }
}
