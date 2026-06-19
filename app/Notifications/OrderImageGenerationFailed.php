<?php

namespace App\Notifications;

use App\Models\TicketOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
// Add any other channels you might want (e.g., Slack, Database)
// use Illuminate\Notifications\Messages\SlackMessage;

class OrderImageGenerationFailed extends Notification // Optionally implement ShouldQueue for async notification sending
{
    use Queueable;

    protected TicketOrder $order;
    protected string $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(TicketOrder $order, string $errorMessage)
    {
        $this->order = $order;
        $this->errorMessage = $errorMessage; // Pass the error message
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Define how to notify the user (e.g., email, database, slack)
        return ['mail']; // Example: Send via email
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // $url = route('admin.orders.show', $this->order->id); // URL for admin/promoter to check the order

        return (new MailMessage)
                    ->error() // Use error styling
                    ->subject('Ticket Generation Failed for Order #' . $this->order->id)
                    ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
                    ->line('The automatic ticket image generation failed for Order #' . $this->order->id . ' placed for ' . $this->order->email . '.')
                    ->line('Reason: ' . $this->errorMessage) // Show the error message
                    ->line('The order status has been set to "failed" and the customer was not notified.')
                    ->line('Please review the order and logs for more details.')
                    ->action('View Order', url('/')) // Replace url('/') with actual admin/promoter order view URL
                    ->line('Thank you');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'customer_email' => $this->order->email,
            'error_message' => $this->errorMessage,
        ];
    }

     /**
      * Get the Slack representation of the notification.
      */
     // public function toSlack(object $notifiable): SlackMessage
     // {
     //     return (new SlackMessage)
     //         ->error()
     //         ->content("Ticket Generation Failed for Order #{$this->order->id} ({$this->order->email})!")
     //         ->attachment(function ($attachment) {
     //             $attachment->title('Error Details', url('/')) // Link to order
     //                        ->content($this->errorMessage);
     //         });
     // }
}
