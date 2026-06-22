<?php

namespace App\Http\Controllers;

use App\Models\Festival;
use App\Models\TicketOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubPromoterController extends Controller
{
    /**
     * Sub-promoter dashboard — limited view of the parent's orders.
     * Resolves the first festival the sub-promoter is assigned to. If the
     * user is a sub-promoter on multiple festivals they are redirected to
     * the festival picker first.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $festivals = $user->accessibleFestivals();
        if ($festivals->isEmpty()) {
            abort(403, __('alert.no_festival_access'));
        }
        if ($festivals->count() > 1) {
            return redirect()->route('promoter.festivals.index');
        }

        /** @var Festival $festival */
        $festival = $festivals->first();

        $recentOrders = TicketOrder::with(['items.ticketType', 'festival'])
            ->where('requested_by', $user->parent_id)
            ->where('festival_id', $festival->id)
            ->latest()
            ->take(10)
            ->get();

        // P-001: surface the parent-promoter's identity so the
        // sub-promoter always knows who they're selling for.
        $parent = $user->parent_id ? User::find($user->parent_id) : null;

        return view('pages.subpromoters.dashboard', compact('festival', 'recentOrders', 'parent'));
    }

    public function placeOrder(Request $request)
    {
        // Stub — sub-promoter order placement is handled through the
        // promoter.orders routes when the sub-promoter has access to a festival.
        abort(501, 'Sub-promoter order placement is handled via the promoter area.');
    }
}