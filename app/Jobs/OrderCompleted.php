<?php

namespace App\Jobs;

use App\Models\TicketOrder; // Your TicketOrder model
use App\Models\User;       // Your User/Promoter model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderCompleted implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected TicketOrder $orderInstance; // Renamed to avoid confusion with a local $order variable

    /**
     * How many times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60; // e.g., 1 minute

    /**
     * Create a new job instance.
     *
     * @param TicketOrder $order The completed order instance
     */
    public function __construct(TicketOrder $order)
    {
        $this->orderInstance = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Fetch a fresh instance of the order to ensure up-to-date relations and status,
        // especially important if the job was delayed or retried.
        $currentOrder = TicketOrder::with(['requestedBy', 'items'])->find($this->orderInstance->id);

        if (!$currentOrder) {
            Log::error("[OrderCompleted Job] Order ID: {$this->orderInstance->id} not found. Job cannot proceed.");
            // No point in retrying if the order doesn't exist.
            $this->fail(new \Exception("Order ID {$this->orderInstance->id} not found."));
            return;
        }

        Log::info("[OrderCompleted Job] Starting for Order ID: {$currentOrder->id}. Current job_status: {$currentOrder->job_status}");

        try {
            // Calculate and store commission for the current order
            $commissionChanged = $this->storeOrderCommissionForOrder($currentOrder);

            // If the current order just got its commission calculated/updated,
            // or if it just moved to 'completed' status (meaning its sales volume now counts),
            // trigger recalculation for subsequent orders by the same promoter.
            // The act of this order completing is the trigger.
            if ($currentOrder->job_status === 'completed') {
                $this->triggerRecalculationForSubsequentOrders($currentOrder);
            }

        } catch (Throwable $e) {
            Log::error("[OrderCompleted Job] Exception for Order ID: {$currentOrder->id}. Error: " . $e->getMessage(), [
                'exception' => $e
            ]);
            // Let Laravel handle retries/failed job logging based on $this->fail() or re-throwing.
            // You might want custom logic if the job is failing after multiple retries.
            // For example, update the order with a commission calculation error status.
            // $currentOrder->commission_calculation_status = 'failed';
            // $currentOrder->commission_failure_reason = substr($e->getMessage(), 0, 255);
            // $currentOrder->saveQuietly(); // Avoid triggering more events/observers if any
            $this->fail($e); // Mark the job as failed
        }
    }

    /**
     * Calculate and store commission for the given order.
     * This method can be called for initial calculation or recalculation.
     *
     * @param TicketOrder $order
     * @return bool True if commission was changed/newly set, false otherwise
     */
    private function storeOrderCommissionForOrder(TicketOrder $order): bool
    {
        if ($order->job_status !== 'completed') {
            Log::warning("[storeOrderCommission] Order ID: {$order->id} is not 'completed' (status: '{$order->job_status}'). Skipping commission calculation.");
            return false;
        }

        // It's okay if $order->total_commission_earned is already set, as we might be recalculating.
        $originalCommission = $order->total_commission_earned;

        $totalOrderCommission = 0;
        $promoter = $order->requestedBy; // Should be loaded via `with` in handle()

        if (!$promoter) {
            Log::error("[storeOrderCommission] Promoter not found for Order ID {$order->id}.");
            throw new \RuntimeException("Promoter not found for order {$order->id} during commission calculation.");
        }
        $promoterModelClass = get_class($promoter);

        if ($order->items->isEmpty()) {
            Log::info("[storeOrderCommission] Order ID {$order->id} has no items. Current stored commission: {$originalCommission}. Setting total commission to 0.");
            $totalOrderCommission = 0.00; // Explicitly float
        } else {
            foreach ($order->items as $item) {
                $itemCommissionEarned = 0;
                if (method_exists($promoterModelClass, 'calculateCommission')) {
                    $itemCommissionEarned = $promoterModelClass::calculateCommission(
                        $item->ticket_type_id,
                        $order->id,
                        $item->quantity,
                        $promoter,
                        $order->created_at
                    );
                } else {
                    Log::error("[storeOrderCommission] Static method 'calculateCommission' not found on class {$promoterModelClass} for Order ID {$order->id}, Item ID {$item->id}.");
                    throw new \BadMethodCallException("Static method calculateCommission not found on {$promoterModelClass}.");
                }

                $item->commission_earned = $itemCommissionEarned;
                $item->save(); // Save commission for individual item
                $totalOrderCommission += $itemCommissionEarned;
            }
        }

        // Check if the newly calculated commission is different from the stored one.
        // Handle floating point comparisons carefully.
        $precision = 2; // Define your desired decimal precision
        $commissionHasChanged = (bccomp((string)$originalCommission, (string)$totalOrderCommission, $precision) !== 0);


        if ($commissionHasChanged || $originalCommission === null) {
             Log::info("[storeOrderCommission] Order ID: {$order->id}. Commission " . ($originalCommission === null ? "CALCULATED" : "RECALCULATED") . ". Old: " . ($originalCommission ?? 'NULL') . ", New: {$totalOrderCommission}. Based on rules at {$order->created_at->format('Y-m-d H:i:s')}");
            $order->total_commission_earned = $totalOrderCommission;
            $order->save(); // Save total commission to the order
            return true; // Commission was set or changed
        } else {
            Log::info("[storeOrderCommission] Order ID: {$order->id}. Commission re-evaluated but value remains {$totalOrderCommission}. No update performed.");
            return false; // Commission value did not change
        }
    }

    /**
     * Finds subsequent completed orders by the same promoter and dispatches
     * OrderCompleted jobs for them to recalculate their commission.
     *
     * @param TicketOrder $justCompletedOrder The order that just had its commission processed.
     */
    private function triggerRecalculationForSubsequentOrders(TicketOrder $justCompletedOrder): void
    {
        Log::info("[triggerRecalculation] Checking for subsequent orders to Order ID: {$justCompletedOrder->id} by Promoter ID: {$justCompletedOrder->requested_by} that may need commission recalculation.");

        $subsequentCompletedOrders = TicketOrder::where('requested_by', $justCompletedOrder->requested_by)
            ->where('id', '>', $justCompletedOrder->id) // Orders created after the one that just completed
            ->where('job_status', 'completed')           // That are already marked as completed
            // No need to check whereNotNull('total_commission_earned') here,
            // as storeOrderCommissionForOrder will handle initial calculation if it was null.
            ->orderBy('id', 'asc') // Process them in their creation order
            ->get();

        if ($subsequentCompletedOrders->isEmpty()) {
            Log::info("[triggerRecalculation] No subsequent completed orders found requiring potential commission recalculation for Order ID: {$justCompletedOrder->id}.");
            return;
        }

        Log::info("[triggerRecalculation] Found " . $subsequentCompletedOrders->count() . " subsequent orders to re-evaluate commission for. IDs: " . $subsequentCompletedOrders->pluck('id')->implode(', '));

        foreach ($subsequentCompletedOrders as $orderToRecalculate) {
            Log::info("[triggerRecalculation] Dispatching OrderCompleted job for subsequent Order ID: {$orderToRecalculate->id} to re-evaluate commission.");
            // Dispatch a new job. The `storeOrderCommissionForOrder` method within that job
            // will recalculate and save if the value is different.
            // Consider a specific queue for recalculations if load is a concern.
            OrderCompleted::dispatch($orderToRecalculate)->onQueue('commission_recalc');
        }
    }
}
