<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

/**
 * P-020 / P-062: ticket scanner.
 *
 * Admins (and the lead promoter) use this to mark a ticket as scanned
 * at the gate.  The browser-based camera-based scanner lives in
 * `resources/views/admin/scan/index.blade.php`; this controller is
 * the JSON endpoint the scanner hits when it reads a QR code.
 */
class TicketScanController extends Controller
{
    /**
     * GET — show the scanner UI.
     */
    public function index(Request $request)
    {
        $festival = $request->attributes->get('festival');
        $recent = Ticket::with('ticketOrder')
            ->whereNotNull('scanned_at')
            ->whereHas('ticketOrder', function ($q) use ($festival) {
                if ($festival) $q->where('festival_id', $festival->id);
            })
            ->orderByDesc('scanned_at')
            ->limit(20)
            ->get();
        return view('pages.admin.scan.index', compact('festival', 'recent'));
    }

    /**
     * POST — look up a ticket by its code and toggle its scanned state.
     */
    public function scan(Request $request)
    {
        $data = $request->validate(['code' => 'required|string']);

        $ticket = Ticket::where('code', $data['code'])->first();
        if (!$ticket) {
            return response()->json([
                'ok' => false,
                'error' => 'Ticket not found',
            ], 404);
        }

        // Authorize: ticket must belong to the festival in scope
        $festival = $request->attributes->get('festival');
        if ($festival && $ticket->festival_id !== $festival->id) {
            return response()->json([
                'ok' => false,
                'error' => 'Ticket does not belong to this festival',
            ], 403);
        }

        if ($ticket->scanned_at) {
            return response()->json([
                'ok' => false,
                'error' => 'Ticket was already scanned at ' . $ticket->scanned_at->format('d.m.Y H:i'),
                'ticket' => $this->presentTicket($ticket),
            ], 409);
        }

        $ticket->update(['scanned_at' => now(), 'is_active' => false]);

        return response()->json([
            'ok' => true,
            'ticket' => $this->presentTicket($ticket->fresh('ticketOrder')),
        ]);
    }

    /**
     * POST — un-scan a ticket (admin override).
     */
    public function unscan(Request $request)
    {
        $data = $request->validate(['code' => 'required|string']);
        $ticket = Ticket::where('code', $data['code'])->first();
        if (!$ticket) {
            return response()->json(['ok' => false, 'error' => 'Ticket not found'], 404);
        }
        $ticket->update(['scanned_at' => null, 'is_active' => true]);
        return response()->json(['ok' => true, 'ticket' => $this->presentTicket($ticket)]);
    }

    private function presentTicket(Ticket $ticket): array
    {
        return [
            'id'         => $ticket->id,
            'code'       => $ticket->code,
            'is_active'  => $ticket->is_active,
            'scanned_at' => $ticket->scanned_at?->format('d.m.Y H:i:s'),
            'order'      => [
                'id'           => $ticket->ticketOrder?->id,
                'order_number' => $ticket->ticketOrder?->order_number,
                'email'        => $ticket->ticketOrder?->email,
            ],
        ];
    }
}
