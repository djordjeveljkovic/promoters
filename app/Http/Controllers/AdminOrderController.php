<?php

namespace App\Http\Controllers;

use App\Models\TicketOrder;
use App\Models\Festival;
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Facades\Storage;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) // Inject Request
    {
	$user = auth()->user();
	$role = $user->role;

	/** @var Festival $festival */
	$festival = $request->attributes->get('festival');

	// Get allowed requested_by user IDs if admin (superadmin sees everything)
	$allowedRequestedByUserIds = null;
	if ($role === 'admin') {
	    $allowedRequestedByUserIds = \App\Models\User::whereIn('role', ['admin', 'promoter'])->pluck('id');
	}

	$query = TicketOrder::with([
	    'items.ticketType',
	    'orderedBy',
	    'requestedBy',
	    'festival',
	]);

	// Apply role-based filtering
	if ($allowedRequestedByUserIds !== null) {
	    $query->whereIn('requested_by', $allowedRequestedByUserIds);
	}

	// Apply festival-based filtering (set by EnsureFestivalAccess middleware)
	if ($festival) {
	    $query->where('festival_id', $festival->id);
	}

	// Search functionality
	if ($request->filled('search')) {
	    $searchTerm = $request->input('search');
	    $query->where(function ($q) use ($searchTerm) {
		$q->where('id', 'LIKE', "%{$searchTerm}%")
		    ->orWhere('email', 'LIKE', "%{$searchTerm}%")
		    ->orWhereHas('orderedBy', function ($subQ) use ($searchTerm) {
			$subQ->where('name', 'LIKE', "%{$searchTerm}%");
		    })
		    ->orWhereHas('requestedBy', function ($subQ) use ($searchTerm) {
			$subQ->where('name', 'LIKE', "%{$searchTerm}%");
		    });
	    });
	}

	// Status filter functionality
	if ($request->filled('status_filter')) {
	    $query->where('job_status', $request->input('status_filter'));
	}

	// Final result
	$orders = $query->latest()->paginate(15)->withQueryString();


        $jobStatusColors = [
            'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-200',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-200',
            'failed'     => 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-200',
            'failed_clickable' => 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-200 hover:bg-red-200 dark:hover:bg-red-600 cursor-pointer',
            'blocked'    => 'bg-gray-300 text-gray-700 dark:bg-gray-600 dark:text-gray-100',
            'completed'  => 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-200',
            'sent'       => 'bg-teal-100 text-teal-800 dark:bg-teal-700 dark:text-teal-200',
            'N/A'        => 'bg-gray-100 text-gray-800 dark:bg-gray-500 dark:text-gray-300',
        ];

        return view('pages.admin.orders.index', compact('orders', 'jobStatusColors', 'festival'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }


    public function updatePayment(Request $request, TicketOrder $order)
    {
        $request->validate([
            'paid' => 'required|numeric|min:0',
        ]);

        $order->paid = $request->input('paid');
        $order->save();

        return redirect()->back()->with('success', __('alert.payment_amount_updated'));
    }

    public function downloadQRCodes(Request $request, TicketOrder $order)
    {
        $zip = new ZipArchive();
        $fileName = 'qrcodes_order_' . $order->id . '.zip';

        // Define a directory for creating the zip. Using storage/app/temp is often safer.
        // For this example, we'll ensure the user's chosen public path part.
        $zipDirectory = storage_path("app/temp_zips"); // Or storage_path("app/temp_zips")

        // Ensure the directory exists and is writable
        if (!file_exists($zipDirectory)) {
            mkdir($zipDirectory, 0775, true);
        }
        $zipPath = $zipDirectory . DIRECTORY_SEPARATOR . $fileName;

        // Determine which tickets to process
        $selectedCodes = $request->input('selected_codes'); // This comes from your form
        $ticketsToProcess = collect(); // Initialize an empty collection

        if (is_array($selectedCodes) && !empty($selectedCodes)) {
            $ticketsToProcess = $order->tickets()->whereIn('code', $selectedCodes)->get();
            if ($ticketsToProcess->isEmpty()) {
                return back()->with('error', __('alert.ticket_codes_not_found'));
            }
        } else {
            // If no selected_codes, assume download all for this order
            $ticketsToProcess = $order->tickets;
        }

        if ($ticketsToProcess->isEmpty()) {
            return back()->with('error', __('alert.no_tickets_to_process'));
        }

        // Try to create and open the zip file
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $filesAdded = 0;

            foreach ($ticketsToProcess as $ticket) {
                $individualQrPath = storage_path("app/public/{$ticket->qr_code_path}");

                if (file_exists($individualQrPath)) {
                    $zip->addFile($individualQrPath, basename($individualQrPath)); // e.g., "TICKETCODE1.png"
                    $filesAdded++;
                }
            }

            $zip->close(); // Close the zip archive to finalize it

            // Only attempt to download if files were added and the zip file exists
            if ($filesAdded > 0 && file_exists($zipPath)) {
                return response()->download($zipPath, $fileName)->deleteFileAfterSend(true);
            } else {
                // If no files were added, or zip somehow wasn't created, clean up if an empty zip exists
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                }
                return back()->with('error', __('alert.no_qr_codes_found'));
            }
        } else {
            return back()->with('error', __('alert.zip_creation_failed'));
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
