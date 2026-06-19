<?php

namespace App\Jobs;

use App\Events\JobCompleted;
use App\Mail\CustomerTicketsMail; // Import the Mailable we will create
use App\Models\TicketOrder;
use App\Models\TicketType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendCustomerTicketsEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $ticketOrderId;
    protected string $customerEmail;

    /**
     * Create a new job instance.
     *
     * @param int $ticketOrderId The ID of the TicketOrder
     * @param string $customerEmail The email address to send to
     */
    public function __construct(int $ticketOrderId, string $customerEmail)
    {
        $this->ticketOrderId = $ticketOrderId;
        $this->customerEmail = $customerEmail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info("SendCustomerTicketsEmailJob started for Order ID: {$this->ticketOrderId} to {$this->customerEmail}"); // <-- ADD THIS

        try {
            $order = TicketOrder::with([
                'items.ticketType',
                'tickets.ticketType'
            ])->findOrFail($this->ticketOrderId);

            Log::info("Order data fetched. Attempting to send email for Order ID: {$this->ticketOrderId}.");

            $mailable = new CustomerTicketsMail($order);

            // This will now write to the log file as per your MAIL_MAILER setting
            Mail::to($this->customerEmail)->send($mailable);

            Log::info("Successfully initiated mail sending (to log) for Order ID: {$this->ticketOrderId} to {$this->customerEmail}");

            $order = TicketOrder::find($this->ticketOrderId);
            $order->update([
                'job_status' => 'completed',
                'job_failure_reason' => null,
            ]);

            event(new JobCompleted('Email sent Successfully'));
        } catch (\Exception $e) {
            Log::error("Failed to send ticket email for Order ID: {$this->ticketOrderId}. Error in handle: " . $e->getMessage(), [ // Added "in handle"
                'trace' => $e->getTraceAsString()
            ]);
            // Mark job as failed and save message
            $order = TicketOrder::find($this->ticketOrderId);
            if ($order) {
                $order->update([
                    'job_status' => 'failed',
                    'job_failure_reason' => $e->getMessage(),
                ]);
            }

            event(new JobCompleted('Error sending email'));
            throw $e;
        }
    }
}
