<?php

namespace App\Http\Controllers;

use App\Models\Festival;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PromoterController extends Controller
{
    /**
     * Festival picker — shows the festivals this promoter can sell for.
     */
    public function festivalsIndex(Request $request)
    {
        $user = $request->user();
        $festivals = $user->accessibleFestivals()->loadCount(['ticketTypes', 'orders']);

        return view('pages.promoters.festivals.index', compact('festivals'));
    }

    public function dashboard(Request $request)
    {
        /** @var Festival $festival */
        $festival = $request->attributes->get('festival');

        $promoter = Auth::user(); // Get the authenticated promoter User model
        if (!$promoter) {
            return redirect()->route('login')->with('error', __('alert.auth_required'));
        }
        $promoterId = $promoter->id;

        $successfulSaleStatuses = ['completed', 'sent'];
        $endDate = now();
        $startDate30Days = now()->subDays(30);

        $baseOrdersQuery = function () use ($promoterId, $successfulSaleStatuses, $festival) {
            $q = TicketOrder::where('requested_by', $promoterId)
                ->whereIn('job_status', $successfulSaleStatuses);
            if ($festival) {
                $q->where('festival_id', $festival->id);
            }
            return $q;
        };

        // --- Promoter's Financials (All Time) ---
        // 1. Total Commission Earned by Promoter (All Time)
        $promoterTotalEarnedCommissionAllTime = $baseOrdersQuery()->sum('total_commission_earned'); // Sum the stored commission

        // 2. Gross Value of Tickets Sold by Promoter (All Time)
        // This is the sum of the 'total' field from their successful orders
        $promoterGrossSalesAllTime = $baseOrdersQuery()->sum('total');

        // 3. Amount Already Paid by Promoter to Organizers
        // IMPORTANT: Replace 'paid_to_organizers_amount' with your actual field name on the User model
        $amountAlreadyPaidByPromoter = $promoter->paid ?? 0.00;

        // 4. Amount Owed by Promoter to Organizers
        $amountOwedToOrganizersByPromoter = $promoterGrossSalesAllTime - $amountAlreadyPaidByPromoter - $promoterTotalEarnedCommissionAllTime;


        // --- Promoter's Overall Stats (All Time) - Other stats remain similar ---
        $totalOrdersAllQ = TicketOrder::where('requested_by', $promoterId);
        if ($festival) { $totalOrdersAllQ->where('festival_id', $festival->id); }
        $promoterTotalOrdersAllTime = $totalOrdersAllQ->count();

        $promoterTotalTicketsSoldAllTime = TicketOrderItem::whereHas('ticketOrder', function ($query) use ($promoterId, $successfulSaleStatuses, $festival) {
            $query->where('requested_by', $promoterId)
                ->whereIn('job_status', $successfulSaleStatuses);
            if ($festival) {
                $query->where('festival_id', $festival->id);
            }
        })->sum('quantity');


        // --- Promoter's Overall Stats (Last 30 Days) ---
        // Commission Earned by Promoter (Last 30 Days)
        $promoterTotalEarnedCommissionLast30Days = $baseOrdersQuery()
            ->whereBetween('created_at', [$startDate30Days, $endDate])
            ->sum('total_commission_earned');

        $promoterTotalOrdersLast30Days = TicketOrder::where('requested_by', $promoterId)
            ->whereBetween('created_at', [$startDate30Days, $endDate])
            ->when($festival, fn ($q) => $q->where('festival_id', $festival->id))
            ->count();

        $promoterTotalTicketsSoldLast30Days = TicketOrderItem::whereHas('ticketOrder', function ($query) use ($promoterId, $successfulSaleStatuses, $startDate30Days, $endDate, $festival) {
            $query->where('requested_by', $promoterId)
                ->whereIn('job_status', $successfulSaleStatuses)
                ->whereBetween('created_at', [$startDate30Days, $endDate]);
            if ($festival) {
                $query->where('festival_id', $festival->id);
            }
        })->sum('quantity');

        // --- Ticket Type Performance (Gross Revenue) ---
        $promoterTicketTypePerformance = TicketType::select(
            'ticket_types.name',
            DB::raw('SUM(ticket_order_items.quantity) as total_quantity_sold'),
            DB::raw('SUM(ticket_order_items.quantity * ticket_types.price) as total_revenue_generated') // Gross revenue
        )
            ->join('ticket_order_items', 'ticket_types.id', '=', 'ticket_order_items.ticket_type_id')
            ->join('ticket_orders', 'ticket_order_items.ticket_order_id', '=', 'ticket_orders.id')
            ->where('ticket_orders.requested_by', $promoterId)
            ->whereIn('ticket_orders.job_status', $successfulSaleStatuses)
            ->when($festival, fn ($q) => $q->where('ticket_orders.festival_id', $festival->id))
            ->groupBy('ticket_types.id', 'ticket_types.name')
            ->orderBy('total_quantity_sold', 'desc')
            ->take(5)
            ->get();

        // --- Promoter's Order Statuses ---
        $promoterOrderStatusCountsQuery = TicketOrder::where('requested_by', $promoterId);
        if ($festival) { $promoterOrderStatusCountsQuery->where('festival_id', $festival->id); }
        $promoterOrderStatusCounts = $promoterOrderStatusCountsQuery
            ->select('job_status', DB::raw('count(*) as count'))
            ->groupBy('job_status')
            ->pluck('count', 'job_status');

        // --- Promoter's Recent Orders ---
        $promoterRecentOrdersQuery = TicketOrder::with(['orderedBy', 'items.ticketType', 'festival'])
            ->where('requested_by', $promoterId)
            ->orderBy('created_at', 'desc');
        if ($festival) { $promoterRecentOrdersQuery->where('festival_id', $festival->id); }
        $promoterRecentOrders = $promoterRecentOrdersQuery->take(5)->get();

        // --- Job Status Colors ---
        $jobStatusColors = [
            'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-yellow-100',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-blue-100',
            'failed'     => 'bg-red-100 text-red-800 dark:bg-red-600 dark:text-red-100',
            'blocked'    => 'bg-gray-200 text-gray-700 dark:bg-gray-500 dark:text-gray-200',
            'completed'  => 'bg-green-100 text-green-800 dark:bg-green-600 dark:text-green-100',
            'sent'       => 'bg-teal-100 text-teal-800 dark:bg-teal-600 dark:text-teal-100',
        ];

        return view('pages.promoters.dashboard', compact(
            'festival',
            'promoterTotalEarnedCommissionAllTime', // Renamed for clarity
            'promoterTotalOrdersAllTime',
            'promoterTotalTicketsSoldAllTime',
            'promoterTotalEarnedCommissionLast30Days', // Renamed for clarity
            'promoterTotalOrdersLast30Days',
            'promoterTotalTicketsSoldLast30Days',
            'promoterTicketTypePerformance',
            'promoterOrderStatusCounts',
            'jobStatusColors',
            'promoterRecentOrders',
            'promoterGrossSalesAllTime',         // New: For display and calculation
            'amountAlreadyPaidByPromoter',       // New
            'amountOwedToOrganizersByPromoter'   // New
        ));
    }

    public function help()
    {
        return view('pages.promoters.help');
    }
}
