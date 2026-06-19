<?php

namespace App\Mail;

use App\Models\TicketOrder;
use App\Support\Mail\MailTemplateRenderer;
use App\Support\Mail\ResolvedTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Customer-facing ticket email.
 *
 * The body is resolved through {@see MailTemplateRenderer} so festival
 * admins and superadmins can override the look & feel without touching
 * code.  The data passed to the template is intentionally rich so the
 * template can render order info, ticket images, festival branding and
 * contact details out of the box.
 *
 * Attachments (the ticket PNGs themselves) are still driven by the
 * `attachments()` method — templates can reference them via the
 * `{{ $first_ticket_image_url }}` placeholder but the originals are
 * always sent as files.
 */
class CustomerTicketsMail extends Mailable
{
    use Queueable, SerializesModels;

    public const TEMPLATE_KEY = 'customer.tickets';

    public TicketOrder $order;
    public ResolvedTemplate $resolved;

    public int $tries = 3;
    public int $timeout = 240;

    public function __construct(TicketOrder $order)
    {
        $this->order = $order->loadMissing([
            'items.ticketType',
            'tickets.ticketType',
            'festival',
            'orderedBy',
            'requestedBy',
        ]);

        // Resolve the template once so envelope/content/attachments all
        // see the same value.  Cheap (Blade compile + htmlspecialchars).
        $this->resolved = app(MailTemplateRenderer::class)->resolve(
            self::TEMPLATE_KEY,
            $this->order->festival,
            [
                'order'    => $this->order,
                'festival' => $this->order->festival,
                'promoter' => $this->order->requestedBy,
            ],
        );
    }

    public function envelope(): Envelope
    {
        $envelope = new Envelope(subject: $this->resolved->subject);

        if ($this->resolved->fromAddress) {
            $envelope->from(
                $this->resolved->fromAddress,
                $this->resolved->fromName ?: null,
            );
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails._rendered',
            with: [
                'resolved' => $this->resolved,
                'order'    => $this->order,
                'festival' => $this->order->festival,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        Log::info("[CustomerTicketsMail] Processing attachments for Order ID: {$this->order->id}. Found {$this->order->tickets->count()} tickets.");

        $ticketTypeCounts = [];
        foreach ($this->order->tickets as $ticket) {
            if ($ticket->image_path) {
                $ticketTypeId = $ticket->ticket_type_id;

                if (!isset($ticketTypeCounts[$ticketTypeId])) {
                    $ticketTypeCounts[$ticketTypeId] = 0;
                }
                $ticketTypeCounts[$ticketTypeId]++;
                $currentTicketNumberForType = $ticketTypeCounts[$ticketTypeId];
                $pathOnPublicDisk = ltrim($ticket->image_path, '/');

                Log::info("[CustomerTicketsMail][Attachment] Checking ticket ID: {$ticket->id}, disk path: '{$pathOnPublicDisk}'");

                if (Storage::disk('public')->exists($pathOnPublicDisk)) {
                    Log::info("[CustomerTicketsMail][Attachment] File exists on 'public' disk: '{$pathOnPublicDisk}'. Attempting to attach.");
                    try {
                        $attachments[] = Attachment::fromStorageDisk('public', $pathOnPublicDisk)
                            ->as('Ulaznica_' . $ticket->ticketType->name . '_' . $currentTicketNumberForType . '.png')
                            ->withMime('image/png');
                        Log::info("[CustomerTicketsMail][Attachment] Successfully prepared attachment for ticket ID {$ticket->id}");
                    } catch (\Exception $e) {
                        Log::error("[CustomerTicketsMail][Attachment] Error creating attachment for ticket ID {$ticket->id} from 'public' disk path '{$pathOnPublicDisk}': " . $e->getMessage());
                    }
                } else {
                    Log::warning("[CustomerTicketsMail][Attachment] File does NOT exist on 'public' disk: '{$pathOnPublicDisk}' for ticket ID {$ticket->id}");
                }
            }
        }
        Log::info("[CustomerTicketsMail] Total attachments prepared: " . count($attachments));
        return $attachments;
    }
}
