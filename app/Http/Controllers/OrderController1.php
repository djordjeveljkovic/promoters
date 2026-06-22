<?php

/**
 * DEPRECATED duplicate of OrderController. Pre-dates the multi-festival
 * refactor. Not referenced by any route — kept only because the sandbox
 * this project lives in disallows `rm`. Safe to delete.
 *
 * If you ever autoload it, note that it declares `class OrderController`
 * in the `App\Http\Controllers` namespace, which would cause a fatal
 * "Cannot declare class" error because the real OrderController is in
 * the same namespace. So don't autoload it.
 */
namespace App\Http\Controllers\_deprecated;

use App\Jobs\GenerateTicketImagesJob;
use App\Jobs\SendCustomerTicketsEmailJob;
use App\Jobs\OrderCompleted;
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

class OrderController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of the orders placed by the promoter.
     */
    public function index()
    {
        $promoterId = Auth::id();
        $orders = TicketOrder::where('requested_by', $promoterId)
            ->with(['items.ticketType', 'orderedBy']) // Removed 'requestedBy' as it's the current user
            ->latest()
            ->paginate(15);

        // Pass status colors for job_status to the view
        $jobStatusColors = [
            'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-yellow-100',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-blue-100',
            'failed'     => 'bg-red-100 text-red-800 dark:bg-red-600 dark:text-red-100',
            'blocked'    => 'bg-gray-200 text-gray-700 dark:bg-gray-500 dark:text-gray-200',
            'completed'  => 'bg-green-100 text-green-800 dark:bg-green-600 dark:text-green-100',
            'sent'       => 'bg-teal-100 text-teal-800 dark:bg-teal-600 dark:text-teal-100',
        ];

        return view('pages.promoters.orders.index', compact('orders', 'jobStatusColors'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        $ticketTypes = TicketType::orderBy('name')->get();
        return view('pages.promoters.orders.create', compact('ticketTypes'));
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
            // Auth::user() is the currently authenticated user, who is the promoter in this context.
            $promoterUser = Auth::user();
            if (!$promoterUser) {
                // This should ideally be caught by auth middleware, but as a safeguard
                throw new \Exception("Promoter not authenticated.");
            }

            // --- Create the main order record ---
            $ticketOrder = TicketOrder::create([
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
                    'ticket_order_id' => $ticketOrder->id,
                    'ticket_type_id' => $itemData['ticket_type_id'],
                    'quantity' => $itemData['quantity'],
                    'price_at_order' => $ticketType->price,
                    // 'commission_earned' will be calculated and stored by the OrderCompleted job
                ]);

                for ($i = 0; $i < $itemData['quantity']; $i++) {
                    Ticket::create([
                        'code' => Str::uuid()->toString(),
                        'ticket_type_id' => $itemData['ticket_type_id'],
                        'ticket_order_id' => $ticketOrder->id,
                        'is_active' => true, // Or based on your business logic
                    ]);
                }

                $orderTotal += $itemData['quantity'] * $ticketType->price;

                // --- This is the call that was causing the error ---
                // You are calling calculateCommission here. Consider its purpose:
                // Is it for an immediate estimate? Or are you trying to store the final commission here?
                // The recommended approach is to calculate and store final commission in the OrderCompleted job.
                // If you calculate it here, be aware that $quantityPreviousOrders might not be accurate
                // as other orders might not have completed yet.

                // Fixed call with all 5 arguments:
                $itemEstimatedCommission = User::calculateCommission(
                    $itemData['ticket_type_id'],
                    $ticketOrder->id,
                    $itemData['quantity'],
                    $promoterUser,          // Pass the promoter User model instance
                    $ticketOrder->created_at // Pass the order's creation timestamp
                );
                Log::info("OrderController@store: Estimated commission for order {$ticketOrder->id}, item type {$itemData['ticket_type_id']}: {$itemEstimatedCommission}");
                // If you were to store it per item here (not recommended as final):
                // $orderItem->commission_earned_estimate = $itemEstimatedCommission; // Example custom field
                // $orderItem->save();
                // $orderTotalCommission += $itemEstimatedCommission;

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

            return redirect()->route('promoter.orders.index') // Adjust route as necessary
                ->with('success', 'Order created successfully! Processing initiated for order #' . $ticketOrder->id . '.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OrderController@store - Order creation failed: ' . $e->getMessage(), [
                'request' => $request->except(['password', '_token']),
                'trace_snippet' => substr($e->getTraceAsString(), 0, 1000)
            ]);
            return back()->withInput()->with('error', 'Failed to create order due to an internal error: ' . $e->getMessage());
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

            return back()->with('success', "Image generation for order #{$order->id} has been re-queued.");
        }

        return back()->with('info', "Image generation for order #{$order->id} cannot be rerun from its current state ({$order->job_status}).");
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
            return back()->with('success', "Email for order #{$order->id} has been re-queued for sending.");
        }
        return back()->with('info', "Email for order #{$order->id} cannot be re-sent from its current state ({$order->job_status}).");
    }
}
