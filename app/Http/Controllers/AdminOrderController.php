<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateTicketImagesJob;
use App\Jobs\OrderCompleted;
use App\Jobs\SendCustomerTicketsEmailJob;
use App\Models\Ticket;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Festival;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
     *
     * BUG-B-001 fix: previously this method was empty and the form
     * posted straight to `promoter.orders.store`, which means admin
     * orders were created with the admin's role as `requested_by`
     * (i.e. looked like a promoter sale).  We now reuse the same
     * ticket-type picker that the promoter create page uses.
     */
    public function create(Request $request)
    {
        /** @var Festival $festival */
        $festival = $request->attributes->get('festival');

        $ticketTypes = $festival
            ? $festival->ticketTypes()->orderBy('name')->get()
            : TicketType::orderBy('name')->get();

        return view('pages.admin.orders.create', compact('ticketTypes', 'festival'));
    }

    /**
     * Store a newly created order in storage.
     *
     * BUG-B-001 fix: admin-side `store()` was empty (a stub left over
     * from the multi-festival refactor).  We now mirror the promoter
     * order creation flow so admins can place orders on behalf of
     * customers without impersonating a promoter.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|max:255',
            'items' => 'required|array|min:1',
            'items.*.ticket_type_id' => 'required|exists:ticket_types,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'items.required' => 'Please add at least one ticket type to the order.',
            'items.min' => 'Please add at least one ticket type to the order.',
        ]);

        /** @var Festival $festival */
        $festival = $request->attributes->get('festival');

        if ($festival) {
            $validIds = $festival->ticketTypes()->pluck('id')->all();
            foreach ($validatedData['items'] as $item) {
                if (!in_array($item['ticket_type_id'], $validIds, true)) {
                    return back()->withErrors([
                        'items' => 'Selected ticket type does not belong to this festival.',
                    ])->withInput();
                }
            }
        }

        DB::beginTransaction();

        try {
            $customerUser = User::firstOrCreate(
                ['email' => $validatedData['email']],
                [
                    'name'     => Str::before($validatedData['email'], '@'),
                    'password' => Hash::make(Str::random(16)),
                    'role'     => 'buyer',
                ]
            );

            $adminUser = Auth::user();
            if (!$adminUser) {
                throw new \Exception('Admin not authenticated.');
            }

            $ticketOrder = TicketOrder::create([
                'festival_id'   => $festival?->id,
                'order_number'  => $this->generateUniqueCrypticOrderNumber(),
                'ordered_by'    => $customerUser->id,
                'requested_by'  => $adminUser->id,
                'email'         => $validatedData['email'],
                'job_status'    => 'processing',
                'paid'          => 0.00,
                'total'         => 0.00,
            ]);

            $ticketTypes = TicketType::findMany(
                collect($validatedData['items'])->pluck('ticket_type_id')->unique()
            )->keyBy('id');

            $orderTotal = 0.00;

            foreach ($validatedData['items'] as $itemData) {
                $ticketType = $ticketTypes->get($itemData['ticket_type_id']);
                if (!$ticketType) {
                    throw new \Exception('Invalid ticket type ID: ' . $itemData['ticket_type_id']);
                }

                TicketOrderItem::create([
                    'festival_id'     => $festival?->id,
                    'ticket_order_id' => $ticketOrder->id,
                    'ticket_type_id'  => $itemData['ticket_type_id'],
                    'quantity'        => $itemData['quantity'],
                    'price_at_order'  => $ticketType->price,
                ]);

                for ($i = 0; $i < $itemData['quantity']; $i++) {
                    Ticket::create([
                        'festival_id'     => $festival?->id,
                        'code'            => Str::uuid()->toString(),
                        'ticket_type_id'  => $itemData['ticket_type_id'],
                        'ticket_order_id' => $ticketOrder->id,
                        'is_active'       => true,
                    ]);
                }

                $orderTotal += $itemData['quantity'] * $ticketType->price;
            }

            $ticketOrder->total = $orderTotal;
            $ticketOrder->save();

            DB::commit();

            // Dispatch the chain in a try/catch so a job-dispatch
            // failure (e.g. missing public storage in test env) doesn't
            // roll back an otherwise-successful order creation.  The user
            // can always rerun the chain from the order detail page.
            try {
                Bus::chain([
                    new GenerateTicketImagesJob($ticketOrder->id),
                    new SendCustomerTicketsEmailJob($ticketOrder->id, $validatedData['email']),
                    new OrderCompleted($ticketOrder),
                ])->dispatch();
            } catch (\Throwable $chainErr) {
                Log::warning('AdminOrderController@store - chain dispatch failed: ' . $chainErr->getMessage(), [
                    'order_id' => $ticketOrder->id,
                ]);
            }

            // Build the redirect URL safely — `route()` throws if the festival
            // param is null, so fall back to the orders index when the
            // festival context wasn't set (defensive: should never happen
            // because EnsureFestivalAccess short-circuits earlier).
            $redirectParams = ['order' => $ticketOrder->id];
            if ($festival) {
                $redirectParams['festival'] = $festival->slug ?? $festival->id;
            }
            try {
                return redirect()
                    ->route('admin.orders.show', $redirectParams)
                    ->with('success', __('alert.order_created_success', ['orderId' => $ticketOrder->id]));
            } catch (\Throwable $routeErr) {
                Log::warning('AdminOrderController@store - admin.orders.show route failed: ' . $routeErr->getMessage());
                return redirect()
                    ->route('admin.orders.index', $redirectParams)
                    ->with('success', __('alert.order_created_success', ['orderId' => $ticketOrder->id]));
            }
        } catch (\Exception $e) {
            // Roll back the order creation only — the chain dispatch is
            // wrapped in its own try/catch above, so by the time we get
            // here we know the order itself failed.
            try { DB::rollBack(); } catch (\Throwable $_) {}
            Log::error('AdminOrderController@store - Order creation failed: ' . $e->getMessage(), [
                'request' => $request->except(['password', '_token']),
                'trace_snippet' => substr($e->getTraceAsString(), 0, 1000),
            ]);
            return back()->withInput()->with('error', __('alert.order_created_failure', ['message' => $e->getMessage()]));
        }
    }

    /**
     * Generates a 6-character alpha order number, unique across `ticket_orders`.
     * Mirrors the same routine on OrderController — keeping them duplicated
     * is preferable to forcing a service-layer indirection for what is
     * effectively a one-liner.
     */
    private function generateUniqueCrypticOrderNumber(int $length = 6): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);

        do {
            $currentRandomString = '';
            for ($i = 0; $i < $length; $i++) {
                try {
                    $currentRandomString .= $characters[random_int(0, $charactersLength - 1)];
                } catch (\Exception $e) {
                    $currentRandomString .= $characters[mt_rand(0, $charactersLength - 1)];
                }
            }
        } while (DB::table('ticket_orders')->where('order_number', $currentRandomString)->exists());

        return $currentRandomString;
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
        // B-005: this action lives behind the festival-scope middleware,
        // but the route is also referenced from the Livewire admin show
        // view which passes raw festival_id.  Defensively check that the
        // order belongs to the festival in scope so a stray id mismatch
        // doesn't leak tickets across festivals.
        $festival = $request->attributes->get('festival');
        if ($festival instanceof Festival && $order->festival_id !== $festival->id) {
            abort(403, __('alert.role_unauthorized'));
        }
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
     * (Reserved — order editing is not exposed in the UI today.)
     */
    public function edit(string $id)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     * (Reserved — order editing is not exposed in the UI today.)
     */
    public function update(Request $request, string $id)
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     * (Reserved — order deletion is intentionally not exposed in the UI;
     * historical orders should be preserved for accounting.)
     */
    public function destroy(string $id)
    {
        abort(404);
    }

    /**
     * BUG-AUDIT-005: admin-side mirror of OrderController@rerunImageGeneration.
     * Re-queues the GenerateTicketImagesJob for the order so the
     * admin orders index "Generate images" button has a working endpoint.
     */
    public function rerunImageGeneration(Request $request, string $festival, string $order)
    {
        // Authorize: the order must belong to the festival in scope.
        $festivalModel = $request->attributes->get('festival');
        $ticketOrder = TicketOrder::find($order);
        if (!$ticketOrder) {
            abort(404);
        }
        if ($festivalModel instanceof Festival && $ticketOrder->festival_id !== $festivalModel->id) {
            abort(403, __('alert.role_unauthorized'));
        }

        if (in_array($ticketOrder->job_status, ['failed', 'pending', 'processing', 'blocked'], true)) {
            $ticketOrder->job_status = 'pending';
            $ticketOrder->job_failure_reason = null;
            $ticketOrder->save();
            GenerateTicketImagesJob::dispatch($ticketOrder->id);
            return back()->with('success', __('alert.image_generation_requeued', ['orderId' => $ticketOrder->id]));
        }
        return back()->with('info', __('alert.image_generation_cannot_rerun', [
            'orderId' => $ticketOrder->id,
            'status' => $ticketOrder->job_status,
        ]));
    }

    /**
     * BUG-AUDIT-005: admin-side mirror of OrderController@rerunEmailSending.
     */
    public function rerunEmailSending(Request $request, string $festival, string $order)
    {
        $festivalModel = $request->attributes->get('festival');
        $ticketOrder = TicketOrder::find($order);
        if (!$ticketOrder) {
            abort(404);
        }
        if ($festivalModel instanceof Festival && $ticketOrder->festival_id !== $festivalModel->id) {
            abort(403, __('alert.role_unauthorized'));
        }

        if (in_array($ticketOrder->job_status, ['failed', 'completed', 'sent', 'processing'], true)) {
            $wasFailed = $ticketOrder->job_status === 'failed';
            $ticketOrder->job_status = 'pending';
            if ($wasFailed) {
                $ticketOrder->job_failure_reason = null;
            }
            $ticketOrder->save();
            SendCustomerTicketsEmailJob::dispatch($ticketOrder->id, $ticketOrder->email);
            return back()->with('success', __('alert.email_requeued_success', ['orderId' => $ticketOrder->id]));
        }
        return back()->with('info', __('alert.email_cannot_resent', [
            'orderId' => $ticketOrder->id,
            'status' => $ticketOrder->job_status,
        ]));
    }
}
