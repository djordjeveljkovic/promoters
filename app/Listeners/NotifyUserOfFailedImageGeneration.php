<?php

namespace App\Listeners;

use App\Jobs\GenerateTicketImagesJob; // Import the specific job class
use App\Models\TicketOrder;
use App\Notifications\OrderImageGenerationFailed; // Import the notification
use Illuminate\Contracts\Queue\ShouldQueue; // Make listener queued for performance
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification; // Import Notification facade

class NotifyUserOfFailedImageGeneration implements ShouldQueue // Implement ShouldQueue
{
    use InteractsWithQueue; // Use trait if implementing ShouldQueue

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(JobFailed $event): void
    {
        // Get the job instance from the payload
        $job = @unserialize($event->job->payload()['data']['command']);

        // Check if the failed job is the one we care about
        if ($job instanceof GenerateTicketImagesJob) {
            Log::info("JobFailed event caught for GenerateTicketImagesJob, Order ID: {$job->ticketOrderId}");

            try {
                 // Fetch the order (might already be marked as 'failed' by the job's catch block)
                 $order = TicketOrder::with('requestedBy')->find($job->ticketOrderId);

                 if ($order && $order->requestedBy) { // Check if order and requestor exist
                     $errorMessage = $event->exception->getMessage(); // Get the exception message

                     // Send notification to the user who requested the order
                     Notification::send($order->requestedBy, new OrderImageGenerationFailed($order, $errorMessage));

                     Log::info("Sent failure notification to user ID {$order->requestedBy->id} for Order ID {$job->ticketOrderId}");
                 } elseif ($order) {
                      Log->warning("Order {$job->ticketOrderId} found, but user who requested it was not found. Cannot send failure notification.");
                 } else {
                      Log->warning("Order {$job->ticketOrderId} not found. Cannot send failure notification.");
                 }
            } catch (\Exception $e) {
                Log::error("Failed to send notification about failed image generation for Order ID: {$job->ticketOrderId}. Error: " . $e->getMessage());
            }
        }
    }
}
