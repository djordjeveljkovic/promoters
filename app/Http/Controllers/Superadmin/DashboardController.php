<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Festival;
use App\Models\TicketOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalFestivals = Festival::count();
        $activeFestivals = Festival::where('status', 'active')->count();
        $totalUsers      = User::count();
        $totalAdmins     = User::where('role', 'admin')->count();
        $totalPromoters  = User::where('role', 'promoter')->count();
        $totalOrders     = TicketOrder::count();
        $completedOrders = TicketOrder::where('job_status', 'completed')->count();
        $totalRevenue    = TicketOrder::where('job_status', 'completed')->sum('total');

        $recentFestivals = Festival::orderByDesc('created_at')->take(5)->get();
        $recentUsers     = User::orderByDesc('created_at')->take(5)->get();

        $perFestivalRevenue = Festival::withSum(
            ['orders as completed_revenue' => fn ($q) => $q->where('job_status', 'completed')],
            'total'
        )
            ->orderByDesc('completed_revenue')
            ->take(5)
            ->get();

        return view('pages.superadmin.dashboard', compact(
            'totalFestivals',
            'activeFestivals',
            'totalUsers',
            'totalAdmins',
            'totalPromoters',
            'totalOrders',
            'completedOrders',
            'totalRevenue',
            'recentFestivals',
            'recentUsers',
            'perFestivalRevenue',
        ));
    }
}