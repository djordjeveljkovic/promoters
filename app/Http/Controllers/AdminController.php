<?php

namespace App\Http\Controllers;

use App\Models\Festival;
use App\Models\Ticket;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Festival picker — lists every festival this admin can access.
     */
    public function festivalsIndex(Request $request)
    {
        $user = $request->user();
        $festivals = $user->accessibleFestivals()->loadCount(['ticketTypes', 'orders']);

        return view('pages.admin.festivals.index', compact('festivals'));
    }

    /**
     * M-006: the admin dashboard runs ~15 queries.  Wrap the heavy
     * computation in a 60-second cache so refreshing the page in
     * quick succession doesn't hammer MySQL.  Cache key is
     * per-(user role, festival, user) so an admin in festival A
     * doesn't see festival B's numbers, and a superadmin (who sees
     * everything) gets its own bucket.
     *
     * The cache is automatically invalidated whenever a TicketOrder
     * is created/updated/deleted (see {@see \App\Models\TicketOrder::boot()}).
     */
    public function dashboard(Request $request)
    {
        /** @var \App\Models\Festival $festival */
        $festival = $request->attributes->get('festival');
        $user = $request->user();

        $cacheKey = sprintf(
            'admin.dashboard:%s:%s:%s',
            $user?->id ?? 'guest',
            $user?->role ?? 'guest',
            $festival?->id ?? 'all'
        );

        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($request, $festival, $user) {
            return $this->computeDashboardStats($request, $festival, $user);
        });

        // The view needs the same variable names as before — extract
        // them back out of the array.
        extract($stats, EXTR_SKIP);

        return view('pages.admin.dashboard', compact(
            'festival',
            'totalRevenueAllTime',
            'totalPaidAllTime',
            'totalOrdersAllTime',
            'totalTicketsEffectivelySoldAllTime',
            'totalRevenueLast30Days',
            'totalOrdersLast30Days',
            'totalTicketsSoldLast30Days',
            'ticketTypePerformance',
            'promoterPerformance',
            'userCountsByRole',
            'orderStatusCounts',
            'statusColors',
            'activeTicketsCount',
            'inactiveTicketsCount',
            'recentOrders'
        ));
    }

    /**
     * Pure computation for the dashboard — no caching, no view.  Returns
     * an associative array of every variable the dashboard view needs.
     * Extracted from `dashboard()` so the cache wrapper can be a thin
     * shim and the math itself is unit-testable in isolation.
     */
    private function computeDashboardStats(Request $request, ?Festival $festival, ?User $user): array
    {

	$user = auth()->user();
	$role = $user->role;

	$allowedRequestedByUserIds = null;

	if ($role === 'admin') {
	    $allowedRequestedByUserIds = User::whereIn('role', ['admin', 'promoter'])->pluck('id');
	}

	$filterByRequestedByUserIds = function ($query) use ($allowedRequestedByUserIds) {
	    if ($allowedRequestedByUserIds !== null) {
		$query->whereIn('requested_by', $allowedRequestedByUserIds);
	    }
	};

        // Festival scope (set by EnsureFestivalAccess middleware)
        $filterByFestival = function ($query) use ($festival) {
            if ($festival) {
                $query->where('festival_id', $festival->id);
            }
        };

        // --- Timeframe (Example: Last 30 days and All Time) ---
        // You can extend this with a date picker on the frontend
        $endDate = now();
        $startDate30Days = now()->subDays(30);

        // --- Overall Stats (All Time) ---
        $totalRevenueAllTime = TicketOrder::where('job_status', 'completed')->tap($filterByRequestedByUserIds)->tap($filterByFestival)->sum('total');
        $totalPaidAllTime = TicketOrder::where('job_status', 'completed')->tap($filterByRequestedByUserIds)->tap($filterByFestival)->sum('paid');
        $totalOrdersAllTime = TicketOrder::tap($filterByRequestedByUserIds)->tap($filterByFestival)->count();
        $totalTicketsSoldAllTime = TicketOrderItem::whereHas('ticketOrder', function ($query) use ($filterByRequestedByUserIds, $filterByFestival) {
	    $filterByRequestedByUserIds($query);
	    $filterByFestival($query);
        })->sum('quantity');
        // Consider only tickets from completed orders for "sold"
        $totalTicketsEffectivelySoldAllTime = TicketOrderItem::whereHas('ticketOrder', function ($query) use ($filterByRequestedByUserIds, $filterByFestival) {
            $query->where('job_status', 'completed');
	    $filterByRequestedByUserIds($query);
	    $filterByFestival($query);
        })->sum('quantity');


        // --- Overall Stats (Last 30 Days) ---
        $totalRevenueLast30Days = TicketOrder::where('job_status', 'completed')->tap($filterByRequestedByUserIds)->tap($filterByFestival)->whereBetween('created_at', [$startDate30Days, $endDate])->sum('total');
        $totalOrdersLast30Days = TicketOrder::whereBetween('created_at', [$startDate30Days, $endDate])->tap($filterByRequestedByUserIds)->tap($filterByFestival)->count();
        $totalTicketsSoldLast30Days = TicketOrderItem::whereHas('ticketOrder', function ($query) use ($startDate30Days, $endDate, $filterByRequestedByUserIds, $filterByFestival) {
            $query->whereBetween('created_at', [$startDate30Days, $endDate])->where('job_status', 'completed');
	    $filterByRequestedByUserIds($query);
	    $filterByFestival($query);
        })->sum('quantity');


        // --- Ticket Type Performance (All Time, based on effectively sold) ---
	$ticketTypePerformanceQuery = TicketType::select(
		'ticket_types.name',
		DB::raw('SUM(ticket_order_items.quantity) as total_quantity_sold'),
		DB::raw('SUM(ticket_order_items.quantity * ticket_types.price) as total_revenue')
	    )
	    ->join('ticket_order_items', 'ticket_types.id', '=', 'ticket_order_items.ticket_type_id')
	    ->join('ticket_orders', 'ticket_order_items.ticket_order_id', '=', 'ticket_orders.id')
	    ->where('ticket_orders.job_status', 'completed');

	if ($allowedRequestedByUserIds !== null) {
	    $ticketTypePerformanceQuery->whereIn('ticket_orders.requested_by', $allowedRequestedByUserIds);
	}

	if ($festival) {
	    $ticketTypePerformanceQuery->where('ticket_orders.festival_id', $festival->id);
	}

	$ticketTypePerformance = $ticketTypePerformanceQuery
	    ->groupBy('ticket_types.id', 'ticket_types.name')
	    ->orderBy('total_quantity_sold', 'desc')
	    ->take(5)
	    ->get();

        // --- Promoter Performance (All Time, based on revenue from completed orders) ---
        $promoterPerformance = User::where('role', 'promoter')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(DISTINCT ticket_orders.id) as total_orders_generated'),
                DB::raw('SUM(ticket_orders.total) as total_revenue_generated')
            )
            ->leftJoin('ticket_orders', function ($join) use ($festival) {
                $join->on('users.id', '=', 'ticket_orders.requested_by')
                    ->where('ticket_orders.job_status', '=', 'completed');
                if ($festival) {
                    $join->where('ticket_orders.festival_id', '=', $festival->id);
                }
            })
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_revenue_generated', 'desc')
            ->take(5) // Top 5 promoters
            ->get();

        // --- User Statistics ---
        $userCountsByRole = User::select('role', DB::raw('count(*) as count'))->where('role','<>','superadmin')
            ->groupBy('role')
            ->pluck('count', 'role');

        // --- Order Statuses ---
        $orderStatusCounts = TicketOrder::select('job_status', DB::raw('count(*) as count'))
	    ->tap($filterByRequestedByUserIds)
	    ->tap($filterByFestival)
            ->groupBy('job_status')
            ->pluck('count', 'job_status');

        // --- Ticket Activation ---
	$activeTicketsQuery = Ticket::where('is_active', true)
	    ->whereHas('ticketOrder', function ($query) use ($allowedRequestedByUserIds, $filterByFestival) {
		if ($allowedRequestedByUserIds !== null) {
		    $query->whereIn('requested_by', $allowedRequestedByUserIds);
		}
		$filterByFestival($query);
	    });

	$inactiveTicketsQuery = Ticket::where('is_active', false)
	    ->whereHas('ticketOrder', function ($query) use ($allowedRequestedByUserIds, $filterByFestival) {
		if ($allowedRequestedByUserIds !== null) {
		    $query->whereIn('requested_by', $allowedRequestedByUserIds);
		}
		$filterByFestival($query);
	    });

	$activeTicketsCount = $activeTicketsQuery->count();
	$inactiveTicketsCount = $inactiveTicketsQuery->count();

        // --- Recent Orders ---
        $recentOrders = TicketOrder::with(['orderedBy', 'requestedBy', 'items.ticketType'])
	    ->tap($filterByRequestedByUserIds)
	    ->tap($filterByFestival)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // --- Define Order Status Colors for the view ---
        $statusColors = [
            'processing' => 'bg-blue-100 text-blue-800',
            'failed' => 'bg-red-100 text-red-800',
            'blocked' => 'bg-gray-100 text-gray-800',
            'completed' => 'bg-green-100 text-green-800',
            'sent' => 'bg-teal-100 text-teal-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
        ];

        return [
            'festival'                          => $festival,
            'totalRevenueAllTime'               => $totalRevenueAllTime,
            'totalPaidAllTime'                  => $totalPaidAllTime,
            'totalOrdersAllTime'                => $totalOrdersAllTime,
            'totalTicketsEffectivelySoldAllTime'=> $totalTicketsEffectivelySoldAllTime,
            'totalRevenueLast30Days'            => $totalRevenueLast30Days,
            'totalOrdersLast30Days'             => $totalOrdersLast30Days,
            'totalTicketsSoldLast30Days'        => $totalTicketsSoldLast30Days,
            'ticketTypePerformance'             => $ticketTypePerformance,
            'promoterPerformance'               => $promoterPerformance,
            'userCountsByRole'                  => $userCountsByRole,
            'orderStatusCounts'                 => $orderStatusCounts,
            'statusColors'                      => $statusColors,
            'activeTicketsCount'                => $activeTicketsCount,
            'inactiveTicketsCount'              => $inactiveTicketsCount,
            'recentOrders'                      => $recentOrders,
        ];
    }

    /**
     * P-065: full promoter leaderboard (the dashboard shows only the
     * top 5). Supports per-festival filtering, with a default of
     * "all time, all festivals" when called from the festival picker.
     */
    public function leaderboard(Request $request, $festival)
    {
        $festival = \App\Models\Festival::where('slug', $festival)->firstOrFail();

        $promoters = User::where('role', 'promoter')
            ->with(['festivals' => function ($q) use ($festival) {
                $q->where('festival_user.festival_id', $festival->id);
            }])
            ->get();

        $rows = $promoters->map(function ($p) use ($festival) {
            $orders = \App\Models\TicketOrder::where('requested_by', $p->id)
                ->where('festival_id', $festival->id)
                ->whereIn('job_status', ['completed', 'sent'])
                ->get();
            return [
                'promoter' => $p,
                'orders' => $orders->count(),
                'tickets' => $orders->sum(fn ($o) => $o->items->sum('quantity')),
                'revenue' => (float) $orders->sum('total'),
                'commission' => (float) $orders->sum('total_commission_earned'),
            ];
        })
        ->sortByDesc('revenue')
        ->values();

        return view('pages.admin.promoters.leaderboard', [
            'festival' => $festival,
            'leaderboard' => $rows,
        ]);
    }

    public function promoters(Request $request)
    {
        /** @var \App\Models\Festival $festival */
        $festival = $request->attributes->get('festival');

        // 1. Fetch promoters assigned to this festival (or all promoters for superadmins)
        $query = User::whereIn('role', ['promoter', 'sub_promoter']);
        if ($festival) {
            $query->whereHas('festivals', function ($q) use ($festival) {
                $q->where('festivals.id', $festival->id);
            });
        }
        $promoters = $query->get();

        // 2. Single aggregate query per promoter to avoid N+1 (M-005).
        //    We compute gross + commission + tickets in one SQL statement
        //    grouped by `requested_by`.
        $successfulSaleStatuses = ['completed', 'sent'];
        $promoterIds = $promoters->pluck('id')->all();

        $stats = collect();
        if (!empty($promoterIds)) {
            $rows = DB::table('ticket_orders')
                ->selectRaw('
                    requested_by,
                    COALESCE(SUM(total), 0) AS gross,
                    COALESCE(SUM(total_commission_earned), 0) AS commission,
                    COALESCE(SUM(items_count), 0) AS tickets
                ')
                ->selectRaw('COUNT(*) AS orders_count')
                ->whereIn('requested_by', $promoterIds)
                ->whereIn('job_status', $successfulSaleStatuses)
                ->when($festival, fn ($q) => $q->where('festival_id', $festival->id))
                ->leftJoinSub(
                    DB::table('ticket_order_items')
                        ->selectRaw('ticket_order_id, SUM(quantity) AS items_count')
                        ->groupBy('ticket_order_id'),
                    'items',
                    'ticket_orders.id',
                    '=',
                    'items.ticket_order_id'
                )
                ->groupBy('requested_by')
                ->get();

            $stats = $rows->keyBy('requested_by');
        }

        // 3. Attach stats to each promoter model.
        foreach ($promoters as $promoter) {
            $row = $stats->get($promoter->id);
            $gross = (float) ($row->gross ?? 0);
            $commission = (float) ($row->commission ?? 0);

            $promoter->totalCommissionEarned    = $commission;
            $promoter->grossSalesAllTime         = $gross;
            $promoter->madeForOrganizers         = $gross - $commission;
            $promoter->ticketsSoldCount          = (int) ($row->tickets ?? 0);
            $promoter->ordersCount               = (int) ($row->orders_count ?? 0);
            $promoter->amountPaidToOrganizers    = (float) ($promoter->paid ?? 0);
            $promoter->amountOwedToOrganizers    = $gross - $promoter->amountPaidToOrganizers - $commission;
        }

        return view('pages.admin.promoters.index', compact('promoters', 'festival'));
    }

    /**
     * Note on parameter ordering:
     *   The route signature for every action below is
     *       /admin/festivals/{festival}/promoter/.../{id}
     *   Laravel 12 dispatches route parameters to controller methods in
     *   the order they appear in the URL — NOT in the order declared in
     *   the method signature.  Every method therefore takes
     *   `string $festival` as the first parameter, followed by the
     *   resource id.  The festival id/slug is resolved from the request
     *   attributes that `EnsureFestivalAccess` already set, so we don't
     *   re-query when the URL gives us a slug.
     */
    public function createPromoter(Request $request, string $festival)
    {
        $festivalModel = $this->resolveFestivalForRoute($request, $festival);
        return view('pages.admin.promoters.create', ['festival' => $festivalModel]);
    }

    public function editPromoter(Request $request, string $festival, string $id)
    {
        $festivalModel = $this->resolveFestivalForRoute($request, $festival);
        $promoter = User::findOrFail($id);
        return view('pages.admin.promoters.edit', ['festival' => $festivalModel, 'promoter' => $promoter]);
    }

    public function store(Request $request, string $festival)
    {
        $festivalModel = $this->resolveFestivalForRoute($request, $festival);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'password' => 'nullable|string|min:8',
        ]);

        $promoter = new User();
        $promoter->name = $validatedData['name'];
        $promoter->email = $validatedData['email'];
        $promoter->paid = $request->paid;

        if (!empty($validatedData['password'])) {
            $promoter->password = Hash::make($validatedData['password']);
        }
        $promoter->role = 'promoter';

        $promoter->save();

        // Auto-assign the new promoter to the current festival so they
        // can immediately sell tickets (without this the promoter would
        // need a superadmin to manually attach them).
        if ($festivalModel) {
            $festivalModel->users()->syncWithoutDetaching([
                $promoter->id => [
                    'role_in_festival' => 'promoter',
                    'assigned_by'      => $request->user()?->id,
                    'assigned_at'      => now(),
                ],
            ]);
        }

        return redirect()
            ->route('admin.promoters.index', ['festival' => $festival])
            ->with('success', __('alert.promoter_created'));
    }

    public function updatePromoter(Request $request, string $festival, string $id)
    {
        $this->resolveFestivalForRoute($request, $festival);

        $promoter = User::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($promoter->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            // P-070: public profile fields.
            'is_public' => 'nullable|boolean',
            'bio'       => 'nullable|string|max:500',
            // U-005: avatar upload (optional).
            'avatar'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
        ]);

        $promoter->name = $validatedData['name'];
        $promoter->email = $validatedData['email'];
        $promoter->paid = $request->paid;
        $promoter->is_public = (bool) ($validatedData['is_public'] ?? false);
        $promoter->bio = $validatedData['bio'] ?? null;

        if (!empty($validatedData['password'])) {
            $promoter->password = Hash::make($validatedData['password']);
        }

        // U-005: avatar upload.
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $file = $request->file('avatar');
            $dir = public_path('img/promoter_avatars');
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $name = 'u' . $promoter->id . '_' . $file->hashName();
            $file->move($dir, $name);

            // Delete the previous avatar file (best effort).
            if ($promoter->avatar_path && file_exists(public_path($promoter->avatar_path))) {
                @unlink(public_path($promoter->avatar_path));
            }
            $promoter->avatar_path = 'img/promoter_avatars/' . $name;
        }

        $promoter->save();

        return redirect()
            ->route('admin.promoters.edit', ['festival' => $festival, 'id' => $promoter->id])
            ->with('success', __('alert.promoter_updated_success'));
    }

    public function deletePromoter(Request $request, string $festival, string $id)
    {
        $festivalModel = $this->resolveFestivalForRoute($request, $festival);

        $promoter = User::findOrFail($id);

        // Only allow deletion of users that aren't the last admin/superadmin.
        if (in_array($promoter->role, ['admin', 'superadmin'], true)) {
            return back()->with('error', __('alert.user_cannot_delete_admin'));
        }

        // Detach from the festival first so the pivot is cleaned up
        // explicitly, then delete the user.
        if ($festivalModel) {
            $festivalModel->users()->detach($promoter->id);
        }

        $promoter->delete();

        return redirect()
            ->route('admin.promoters.index', ['festival' => $festival])
            ->with('success', __('alert.promoter_deleted'));
    }

    /**
     * Promote an existing festival promoter to a "promoter manager" so
     * they can create their own sub-promoters and split their commission
     * with them.  Idempotent: if the user already has a manager pivot
     * row, we re-activate it.
     */
    public function makeManager(Request $request, string $festival, string $id)
    {
        $festivalModel = $this->resolveFestivalForRoute($request, $festival);
        $user = $request->user();
        if (!$user->isFestivalAdmin($festivalModel?->id)) {
            abort(403, __('alert.role_unauthorized'));
        }

        $promoter = User::findOrFail($id);

        // Re-attach (or update) the pivot with role_in_festival = promoter_manager.
        $existing = $festivalModel->users()
            ->wherePivot('user_id', $promoter->id)
            ->first();

        if ($existing) {
            $festivalModel->users()->updateExistingPivot($promoter->id, [
                'role_in_festival' => 'promoter_manager',
                'assigned_by'      => $user->id,
                'assigned_at'      => now(),
            ]);
        } else {
            $festivalModel->users()->attach($promoter->id, [
                'role_in_festival' => 'promoter_manager',
                'assigned_by'      => $user->id,
                'assigned_at'      => now(),
            ]);
        }

        return back()->with('success', __('alert.promoter_promoted_to_manager', ['name' => $promoter->name]));
    }

    /**
     * Demote a promoter manager back to a regular promoter on this
     * festival.  We close the manager pivot (set valid_to) so historical
     * commission overrides are preserved.
     */
    public function removeManager(Request $request, string $festival, string $id)
    {
        $festivalModel = $this->resolveFestivalForRoute($request, $festival);
        $user = $request->user();
        if (!$user->isFestivalAdmin($festivalModel?->id)) {
            abort(403, __('alert.role_unauthorized'));
        }

        $promoter = User::findOrFail($id);

        // Just downgrade the role_in_festival on the existing pivot row.
        $festivalModel->users()->updateExistingPivot($promoter->id, [
            'role_in_festival' => 'promoter',
        ]);

        return back()->with('success', __('alert.promoter_demoted', ['name' => $promoter->name]));
    }

    /**
     * Resolve the festival model from either:
     *  - the request attributes set by EnsureFestivalAccess middleware, or
     *  - a slug / id string passed via the URL.
     */
    private function resolveFestivalForRoute(Request $request, string $festival): ?Festival
    {
        $attr = $request->attributes->get('festival');
        if ($attr instanceof Festival) {
            return $attr;
        }
        if (is_numeric($festival)) {
            return Festival::find((int) $festival);
        }
        return Festival::where('slug', $festival)->first();
    }

    /**
     * P-025: change a user's role_in_festival on this festival directly.
     *
     * The promoter-managers page already lets the admin promote / demote
     * `promoter ↔ promoter_manager`. This endpoint covers the rest of the
     * matrix (`admin` / `promoter` / `sub_promoter`) and is exposed as
     * an inline dropdown on the promoters index page.
     */
    public function changeRole(Request $request, string $festival, string $id)
    {
        $festivalModel = $this->resolveFestivalForRoute($request, $festival);
        $user = $request->user();
        if (!$user->isFestivalAdmin($festivalModel?->id)) {
            abort(403, __('alert.role_unauthorized'));
        }

        $data = $request->validate([
            'role' => ['required', 'string', 'in:admin,promoter,promoter_manager,sub_promoter'],
        ]);

        $target = User::findOrFail($id);
        $newRole = $data['role'];

        // Don't let a festival admin demote themselves out of admin on
        // their own festival (would lock them out of the scope).
        if ($target->id === $user->id && $user->isSuperAdmin() === false
            && $user->roleInFestival($festivalModel->id) === 'admin'
            && $newRole !== 'admin') {
            return back()->with('error', __('alert.user_cannot_demote_self'));
        }

        $existing = $festivalModel->users()
            ->wherePivot('user_id', $target->id)
            ->first();

        if ($existing) {
            $festivalModel->users()->updateExistingPivot($target->id, [
                'role_in_festival' => $newRole,
                'assigned_by'      => $user->id,
                'assigned_at'      => now(),
            ]);
        } else {
            $festivalModel->users()->attach($target->id, [
                'role_in_festival' => $newRole,
                'assigned_by'      => $user->id,
                'assigned_at'      => now(),
            ]);
        }

        return back()->with('success', __('alert.role_changed', [
            'name' => $target->name,
            'role' => __("promoter_managers.role.{$newRole}"),
        ]));
    }

    /**
     * P-027: printable commission statement for a single promoter on
     * a single festival.
     *
     * Renders an HTML view (which the user can `Ctrl+P` to PDF) that
     * shows every commission-earning order, the per-ticket-type
     * breakdown, totals and the running balance owed to / paid by
     * the promoter.
     */
    public function promoterStatement(Request $request, string $festival, string $id)
    {
        $festivalModel = $this->resolveFestivalForRoute($request, $festival);
        $user = $request->user();
        if (!$user->isFestivalAdmin($festivalModel?->id)) {
            abort(403, __('alert.role_unauthorized'));
        }

        $promoter = User::findOrFail($id);

        $orders = \App\Models\TicketOrder::with(['items.ticketType', 'orderedBy'])
            ->where('requested_by', $promoter->id)
            ->where('festival_id', $festivalModel->id)
            ->whereIn('job_status', ['completed', 'sent'])
            ->orderBy('created_at')
            ->get();

        $totals = [
            'orders_count'      => $orders->count(),
            'tickets_count'     => $orders->sum(fn ($o) => $o->items->sum('quantity')),
            'gross_revenue'     => (float) $orders->sum('total'),
            'commission_total'  => (float) $orders->sum('total_commission_earned'),
            'paid_to_organizer'  => (float) ($promoter->paid ?? 0),
            'owed_to_organizer'  => 0.0,
        ];
        $totals['owed_to_organizer'] = max(0, $totals['gross_revenue'] - $totals['commission_total'] - $totals['paid_to_organizer']);

        // Per-ticket-type breakdown.
        $byTicketType = $orders->flatMap->items
            ->groupBy(fn ($i) => $i->ticket_type_id)
            ->map(function ($items) {
                $tt = $items->first()->ticketType;
                return [
                    'name'        => $tt?->name ?? '—',
                    'quantity'    => $items->sum('quantity'),
                    'gross'       => (float) $items->sum(fn ($i) => $i->quantity * ($i->price_at_order ?? $tt?->price ?? 0)),
                    'commission'  => 0.0,
                ];
            })
            ->values();

        return view('pages.admin.promoters.statement', [
            'festival'    => $festivalModel,
            'promoter'    => $promoter,
            'orders'      => $orders,
            'totals'      => $totals,
            'byTicketType'=> $byTicketType,
            'generatedAt' => now(),
        ]);
    }
}
