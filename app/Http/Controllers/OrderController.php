<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateTicketImagesJob;
use App\Jobs\SendCustomerTicketsEmailJob;
use App\Jobs\OrderCompleted;
use App\Models\Festival;
use App\Models\Ticket;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Generates a cryptic, unique order number.
     */
    private function generateUniqueCrypticOrderNumber(int $length = 6): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $uniqueOrderNumber = '';

        do {
            $currentRandomString = '';
            for ($i = 0; $i < $length; $i++) {
                try {
                    // Use random_int for cryptographically secure random numbers
                    $currentRandomString .= $characters[random_int(0, $charactersLength - 1)];
                } catch (\Exception $e) {
                    // Fallback for environments where random_int might fail (highly unlikely)
                    // or handle error appropriately
                    // Log::error('random_int failed, falling back to mt_rand: ' . $e->getMessage());
                    $currentRandomString .= $characters[mt_rand(0, $charactersLength - 1)];
                }
            }
            $uniqueOrderNumber = $currentRandomString;
            // Check for uniqueness in the 'ticket_orders' table
        } while (DB::table('ticket_orders')->where('order_number', $uniqueOrderNumber)->exists());

        return $uniqueOrderNumber;
    }

    /**
     * Display a listing of the orders placed by the promoter.
     */
    public function index(Request $request)
    {
        /** @var Festival $festival */
        $festival = $request->attributes->get('festival');

        $promoterId = Auth::id();
        $query = TicketOrder::where('requested_by', $promoterId)
            ->with(['items.ticketType', 'orderedBy', 'festival']);
        if ($festival) {
            $query->where('festival_id', $festival->id);
        }
        $orders = $query->latest()->paginate(15);

        // Pass status colors for job_status to the view
        $jobStatusColors = [
            'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-yellow-100',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-blue-100',
            'failed'     => 'bg-red-100 text-red-800 dark:bg-red-600 dark:text-red-100',
            'blocked'    => 'bg-gray-200 text-gray-700 dark:bg-gray-500 dark:text-gray-200',
            'completed'  => 'bg-green-100 text-green-800 dark:bg-green-600 dark:text-green-100',
            'sent'       => 'bg-teal-100 text-teal-800 dark:bg-teal-600 dark:text-teal-100',
        ];

        return view('pages.promoters.orders.index', compact('orders', 'jobStatusColors', 'festival'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create(Request $request)
    {
        /** @var Festival $festival */
        $festival = $request->attributes->get('festival');

        $ticketTypes = $festival
            ? $festival->ticketTypes()->orderBy('name')->get()
            : TicketType::orderBy('name')->get();

        return view('pages.promoters.orders.create', compact('ticketTypes', 'festival'));
    }

    /**
     * Store a newly created order in storage.
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

        // Enforce that ticket types belong to the current festival
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
            // --- Find or create the customer user ---
            $customerUser = User::firstOrCreate(
                ['email' => $validatedData['email']],
                [
                    'name' => Str::before($validatedData['email'], '@'),
                    'password' => Hash::make(Str::random(16)), // Use a random password
                    'role' => 'buyer' // Ensure you have roles set up if using this
                ]
            );

            // --- Get the Promoter User instance ---
            $promoterUser = Auth::user();
            if (!$promoterUser) {
                // This should ideally be caught by auth middleware, but as a safeguard
                throw new \Exception("Promoter not authenticated.");
            }

            $orderNumber = $this->generateUniqueCrypticOrderNumber(); // e.g., "aK9ZxP4qR7Vc"
            // --- Create the main order record ---
            $ticketOrder = TicketOrder::create([
                'festival_id'  => $festival?->id,
                'order_number' => $orderNumber,
                'ordered_by' => $customerUser->id,
                'requested_by' => $promoterUser->id, // Use promoter's ID
                'email' => $validatedData['email'],
                'job_status' => 'processing', // Or 'pending' if jobs handle setting to 'processing'
                'paid' => 0.00, // Assuming payment handling is separate or comes later
                'total' => 0.00 // Will be updated after calculating
            ]);

            $ticketTypeIds = collect($validatedData['items'])->pluck('ticket_type_id')->unique();
            $ticketTypes = TicketType::findMany($ticketTypeIds)->keyBy('id');

            $orderTotal = 0.00;
            // $orderTotalCommission = 0.00; // If you intend to sum and store commission here

            foreach ($validatedData['items'] as $itemData) {
                $ticketType = $ticketTypes->get($itemData['ticket_type_id']);

                if (!$ticketType) {
                    throw new \Exception("Invalid ticket type ID: " . $itemData['ticket_type_id']);
                }

                $orderItem = TicketOrderItem::create([
                    'festival_id'      => $festival?->id,
                    'ticket_order_id'  => $ticketOrder->id,
                    'ticket_type_id'   => $itemData['ticket_type_id'],
                    'quantity'         => $itemData['quantity'],
                    'price_at_order'   => $ticketType->price,
                    // 'commission_earned' will be calculated and stored by the OrderCompleted job
                ]);

                for ($i = 0; $i < $itemData['quantity']; $i++) {
                    Ticket::create([
                        'festival_id'     => $festival?->id,
                        'code' => Str::uuid()->toString(),
                        'ticket_type_id' => $itemData['ticket_type_id'],
                        'ticket_order_id' => $ticketOrder->id,
                        'is_active' => true, // Or based on your business logic
                    ]);
                }

                $orderTotal += $itemData['quantity'] * $ticketType->price;

                $itemEstimatedCommission = User::calculateCommission(
                    $itemData['ticket_type_id'],
                    $ticketOrder->id,
                    $itemData['quantity'],
                    $promoterUser,          // Pass the promoter User model instance
                    $ticketOrder->created_at // Pass the order's creation timestamp
                );
                Log::info("OrderController@store: Estimated commission for order {$ticketOrder->id}, item type {$itemData['ticket_type_id']}: {$itemEstimatedCommission}");
            }

            $ticketOrder->total = $orderTotal;
            // If you summed $orderTotalCommission:
            // $ticketOrder->total_commission_estimate = $orderTotalCommission; // Example custom field
            $ticketOrder->save();

            DB::commit();

            Log::info("Order {$ticketOrder->id} created by promoter {$promoterUser->id}. Dispatching job chain.");
            Bus::chain([
                new GenerateTicketImagesJob($ticketOrder->id),
                new SendCustomerTicketsEmailJob($ticketOrder->id, $validatedData['email']),
                new OrderCompleted($ticketOrder)
            ])->dispatch();

            return redirect()->route('promoter.orders.index', ['festival' => $festival]) // Adjust route as necessary
                ->with('success', __('alert.order_created_success', ['orderId' => $ticketOrder->id]));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OrderController@store - Order creation failed: ' . $e->getMessage(), [
                'request' => $request->except(['password', '_token']),
                'trace_snippet' => substr($e->getTraceAsString(), 0, 1000)
            ]);
            return back()->withInput()
                ->with('error', __('alert.order_created_failure', ['message' => $e->getMessage()]));
        }
    }


    /**
     * Display the specified order.
     */
    public function show(TicketOrder $order) // Route model binding
    {
        // Ensure the authenticated promoter is authorized to view this order
        if ($order->requested_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $order->load(['items.ticketType', 'tickets.ticketType', 'orderedByUser', 'requestedByUser']);

        // Calculate total price for display (if not stored directly on order)
        $totalPrice = 0;
        foreach ($order->items as $item) {
            $totalPrice += $item->quantity * $item->ticketType->price;
        }

        return view('pages.promoters.orders.show', compact('order', 'totalPrice'));
    }

    public function rerunImageGeneration(TicketOrder $order)
    {
        if (in_array($order->job_status, ['failed', 'pending', 'processing'])) {
            $order->job_status = 'pending'; // Reset status to re-trigger processing pipeline
            $order->job_failure_reason = null;
            $order->save();

            GenerateTicketImagesJob::dispatch($order->id);

            return back()->with('success', __('alert.image_generation_requeued', ['orderId' => $order->id]));
        }

        return back()->with('info', __('alert.image_generation_cannot_rerun', [
            'orderId' => $order->id,
            'status' => $order->job_status
        ]));
    }

    public function rerunEmailSending(TicketOrder $order)
    {
        if (in_array($order->job_status, ['failed', 'completed', 'sent', 'processing'])) {
            $originalStatusBeforeRetry = $order->job_status;
            $order->job_status = 'pending';
            if ($originalStatusBeforeRetry === 'failed') {
                $order->job_failure_reason = null;
            }
            $order->save();

            SendCustomerTicketsEmailJob::dispatch($order->id, $order->email);
            return back()->with('success', __('alert.email_requeued_success', ['orderId' => $order->id]));
        }
        return back()->with('info', __('alert.email_cannot_resent', [
            'orderId' => $order->id,
            'status' => $order->job_status
        ]));
    }
}
