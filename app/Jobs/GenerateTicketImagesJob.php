<?php

namespace App\Jobs;

use App\Models\TicketOrder;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateTicketImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public int $ticketOrderId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $ticketOrderId)
    {
        $this->ticketOrderId = $ticketOrderId;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception If image processing fails critically
     */
    public function handle(): void
    {
        Log::info("Starting image generation for Order ID: {$this->ticketOrderId}");
        $order = null;

        try {
            $order = TicketOrder::with('tickets.ticketType')->findOrFail($this->ticketOrderId);
            $processedCount = 0;

            // *** REMOVED Inner try/catch/finally ***
            foreach ($order->tickets as $ticket) {
                $baseImageGdResource = null;
                $qrCodeGdResource = null;

                // Ensure resources are destroyed even on continue/exceptions within loop iteration scope (using finally isn't straightforward without try)
                // It's generally okay as PHP cleans up resources at end of script/request, but explicit destroy is cleaner if possible.
                // We rely on the outer catch now.

                $ticketType = $ticket->ticketType;

                // --- Basic Validation ---
                if (!$ticketType || !$ticketType->photo_path || !$ticketType->qr_coordinates) {
                    // Throw exception to fail the entire job
                    throw new \Exception("Missing ticket type, base photo, or QR coordinates for Ticket ID {$ticket->id}.");
                }
                $baseImagePath = public_path($ticketType->photo_path);
                if (!file_exists($baseImagePath)) {
                    // Throw exception to fail the entire job
                    throw new \Exception("Base image not found at {$baseImagePath} for Ticket ID {$ticket->id}.");
                }
                $qrCoords = json_decode($ticketType->qr_coordinates, true);
                if (json_last_error() !== JSON_ERROR_NONE || !isset($qrCoords['x'], $qrCoords['y'], $qrCoords['size'])) {
                    // Throw exception to fail the entire job
                    throw new \Exception("Invalid QR coordinates format for Ticket ID {$ticket->id}.");
                }
                $qrX = (int) $qrCoords['x'];
                $qrY = (int) $qrCoords['y'];
                $qrSize = max(50, (int) $qrCoords['size']);

                // --- Generate QR Code ---
                $qrCodeContent = $ticket->code;
                $qrCodeImagePngData = QrCode::format('png')->size($qrSize)->generate($qrCodeContent);

                $rawQrPathRelative = 'qr_images/' . $order->id . '/' . $ticket->code . '.png';
                if (!Storage::disk('public')->put($rawQrPathRelative, $qrCodeImagePngData)) {
                    throw new \Exception("Storage: Failed to save raw QR image to {$rawQrPathRelative} for ticket {$ticket->id}");
                }
                // --- Load Images using GD ---
                $qrCodeGdResource = @imagecreatefromstring($qrCodeImagePngData);
                if ($qrCodeGdResource === false) {
                    throw new \Exception("GD: Failed to create QR code image from string for ticket {$ticket->id}");
                }

                $imageInfo = @getimagesize($baseImagePath);
                if ($imageInfo === false) {
                    // Clean up QR resource before throwing
                    @imagedestroy($qrCodeGdResource);
                    throw new \Exception("GD: Failed to get image size/type for base image {$baseImagePath}");
                }
                $mime = $imageInfo['mime'];
                switch ($mime) {
                    case 'image/jpeg':
                        $baseImageGdResource = @imagecreatefromjpeg($baseImagePath);
                        break;
                    case 'image/png':
                        $baseImageGdResource = @imagecreatefrompng($baseImagePath);
                        break;
                    case 'image/gif':
                        $baseImageGdResource = @imagecreatefromgif($baseImagePath);
                        break;
                    case 'image/webp':
                        $baseImageGdResource = @imagecreatefromwebp($baseImagePath);
                        break;
                    default:
                        @imagedestroy($qrCodeGdResource); // Clean up QR resource
                        throw new \Exception("GD: Unsupported base image type: {$mime} for ticket {$ticket->id}");
                }
                if ($baseImageGdResource === false) {
                    @imagedestroy($qrCodeGdResource); // Clean up QR resource
                    throw new \Exception("GD: Failed to load base image {$baseImagePath}");
                }

                // --- Overlay QR Code using GD ---
                imagealphablending($baseImageGdResource, true);
                imagesavealpha($baseImageGdResource, true);
                $qrActualWidth = imagesx($qrCodeGdResource);
                $qrActualHeight = imagesy($qrCodeGdResource);
                if (!@imagecopy($baseImageGdResource, $qrCodeGdResource, $qrX, $qrY, 0, 0, $qrActualWidth, $qrActualHeight)) {
                    @imagedestroy($baseImageGdResource);
                    @imagedestroy($qrCodeGdResource); // Clean up
                    throw new \Exception("GD: Failed to copy QR code onto base image for ticket {$ticket->id}");
                }

                // --- Save Final Image using GD & Storage ---
                $outputPathRelative = 'generated_tickets/' . $order->id . '/' . $ticket->code . '.png';
                ob_start();
                $saved = @imagepng($baseImageGdResource, null, 9);
                $finalImageDataString = ob_get_clean();
                if (!$saved || $finalImageDataString === false) {
                    @imagedestroy($baseImageGdResource);
                    @imagedestroy($qrCodeGdResource); // Clean up
                    throw new \Exception("GD: Failed to generate PNG data for final image for ticket {$ticket->id}");
                }
                if (!Storage::disk('public')->put($outputPathRelative, $finalImageDataString)) {
                    @imagedestroy($baseImageGdResource);
                    @imagedestroy($qrCodeGdResource); // Clean up
                    throw new \Exception("Storage: Failed to save final image to {$outputPathRelative} for ticket {$ticket->id}");
                }

                // --- Update Ticket Record ---
                $ticket->update(['image_path' => $outputPathRelative]);
                $ticket->update(['qr_code_path' => $rawQrPathRelative]);
                $processedCount++;
                Log::debug("Generated image for Ticket ID {$ticket->id} at {$outputPathRelative}");

                // --- Clean up GD Resources for this iteration ---
                @imagedestroy($baseImageGdResource);
                @imagedestroy($qrCodeGdResource);
            } // End foreach ticket loop

            $order->update([
                'job_status' => 'completed',
                'job_failure_reason' => null,
            ]);
            Log::info("Finished generating {$processedCount} images for Order ID: {$this->ticketOrderId}.");
        } catch (\Exception $e) {

            // Update Order Status
            if ($order) {
                try {
                    $order->update([
                        'job_status' => 'failed',
                        'job_failure_reason' => $e->getMessage(),
                    ]);

                    Log::info("Set Order ID {$this->ticketOrderId} status to {$order->job_status} and message is {$order->job_failure_reason}.");
                } catch (\Exception $updateException) { // Catch potential update error
                    Log::error("Could not update status for Order ID {$this->ticketOrderId} after image generation failure.", [
                        'update_error' => $updateException->getMessage()
                    ]);
                }
            } else {
                Log::warning("Order {$this->ticketOrderId} could not be loaded to update status to failed.");
            }
            Log::error("Failed during image generation job for Order ID: {$this->ticketOrderId}. Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString() // Keep logging the original trace
            ]);

            $this->fail();
            throw $e;
        }
    }
}
