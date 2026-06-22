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

    public function dashboard(Request $request)
    {
        /** @var \App\Models\Festival $festival */
        $festival = $request->attributes->get('festival');

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
        $query = User::where('role', 'promoter');
        if ($festival) {
            $query->whereHas('festivals', function ($q) use ($festival) {
                $q->where('festivals.id', $festival->id);
            });
        }
        $promoters = $query->get();

        // Define successful sale statuses once
        $successfulSaleStatuses = ['completed', 'confirmed']; // Or your actual statuses

        // 2. Iterate through each promoter to calculate and attach their financial data
        foreach ($promoters as $promoter) {
            $promoterId = $promoter->id; // Get the ID of the current promoter in the loop

            $ordersQuery = TicketOrder::where('requested_by', $promoterId)
                ->whereIn('job_status', $successfulSaleStatuses);
            if ($festival) {
                $ordersQuery->where('festival_id', $festival->id);
            }

            // a. Total Commission Earned by Promoter (All Time)
            $promoter->totalCommissionEarned = (clone $ordersQuery)->sum('total_commission_earned');

            // b. Gross Value of Tickets Sold by Promoter (All Time)
            $promoter->grossSalesAllTime = (clone $ordersQuery)->sum('total');

            // c. Amount Already Paid by Promoter to Organizers
            // Assumes 'paid' is a field on your User (promoter) model or you fetch it similarly
            $promoter->amountPaidToOrganizers = $promoter->paid ?? 0.00;

            // d. Amount Owed by Promoter to Organizers
            $promoter->amountOwedToOrganizers = $promoter->grossSalesAllTime - $promoter->amountPaidToOrganizers - $promoter->totalCommissionEarned;

            // e. How much promoter made for organizers
            $promoter->madeForOrganizers = $promoter->grossSalesAllTime - $promoter->totalCommissionEarned;

            // f. How many tickets sold (count of orders)
            $promoter->ticketsSoldCount = (clone $ordersQuery)->count();
        }

        // 3. Pass the collection of promoters (now with added financial data) to the view
        return view('pages.admin.promoters.index', compact('promoters', 'festival'));
    }

    public function createPromoter()
    {
        return view('pages.admin.promoters.create');
    }

    public function editPromoter($id)
    {
        $promoter = User::findOrFail($id);
        return view('pages.admin.promoters.edit', compact('promoter'));
    }

    public function store(Request $request)
    {
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

        return redirect()->route('admin.promoters.index')->with('success', __('alert.promoter_updated_success'));
    }

    public function updatePromoter(Request $request, $id)
    {
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
        ]);

        $promoter->name = $validatedData['name'];
        $promoter->email = $validatedData['email'];
        $promoter->paid = $request->paid;

        if (!empty($validatedData['password'])) {
            $promoter->password = Hash::make($validatedData['password']);
        }

        $promoter->save();

        return redirect()->route('admin.promoters.edit', $id)->with('success', __('alert.promoter_updated_success'));
    }

    /**
     * Promote an existing festival promoter to a "promoter manager" so
     * they can create their own sub-promoters and split their commission
     * with them.  Idempotent: if the user already has a manager pivot
     * row, we re-activate it.
     */
    public function makeManager(Request $request, Festival $festival, $id)
    {
        $user = $request->user();
        if (!$user->isFestivalAdmin($festival->id)) {
            abort(403, __('alert.role_unauthorized'));
        }

        $promoter = User::findOrFail($id);

        // Re-attach (or update) the pivot with role_in_festival = promoter_manager.
        $existing = $festival->users()
            ->wherePivot('user_id', $promoter->id)
            ->first();

        if ($existing) {
            $festival->users()->updateExistingPivot($promoter->id, [
                'role_in_festival' => 'promoter_manager',
                'assigned_by'      => $user->id,
                'assigned_at'      => now(),
            ]);
        } else {
            $festival->users()->attach($promoter->id, [
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
    public function removeManager(Request $request, Festival $festival, $id)
    {
        $user = $request->user();
        if (!$user->isFestivalAdmin($festival->id)) {
            abort(403, __('alert.role_unauthorized'));
        }

        $promoter = User::findOrFail($id);

        // Just downgrade the role_in_festival on the existing pivot row.
        $festival->users()->updateExistingPivot($promoter->id, [
            'role_in_festival' => 'promoter',
        ]);

        return back()->with('success', __('alert.promoter_demoted', ['name' => $promoter->name]));
    }
}
