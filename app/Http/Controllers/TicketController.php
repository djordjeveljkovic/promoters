<?php

namespace App\Http\Controllers;

use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TicketController extends Controller // Assuming this is the class name
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var Festival $festival */
        $festival = $request->attributes->get('festival');

        $query = TicketType::with('commissions');
        if ($festival) {
            $query->where('festival_id', $festival->id);
        }
        $ticketTypes = $query->latest()->paginate(10);

        return view('pages.admin.ticket_type.index', compact('ticketTypes', 'festival'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        /** @var \App\Models\Festival $festival */
        $festival = $request->attributes->get('festival');
        return view('pages.admin.ticket_type.create', compact('festival'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validation
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_types,name',
            'price' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Example validation for image
            'qr_coordinates' => 'required|json', // Basic JSON validation
            // Add validation for the structure inside JSON if needed, e.g., using a custom rule or validating keys after decoding
            'commissions' => 'required|array|min:1', // Ensure at least one commission tier exists
            'commissions.*.min_sold' => 'required|integer|min:0',
            'commissions.*.max_sold' => 'nullable|integer|min:0', // Can be null for the last tier
            'commissions.*.commission_amount' => 'required|numeric|min:0',
            // Optional: Add cross-field validation for commissions (e.g., max_sold > min_sold) if needed using custom rules or after validation logic
        ]);

        // Optional: Validate JSON structure more deeply
        $qrCoordinates = json_decode($validatedData['qr_coordinates'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($qrCoordinates)) {
            return back()->withErrors(['qr_coordinates' => 'Invalid JSON format for QR Coordinates.'])->withInput();
        }
        // Example: Check for specific keys if required
        // if (!isset($qrCoordinates['x']) || !isset($qrCoordinates['y']) || !isset($qrCoordinates['size'])) {
        //     return back()->withErrors(['qr_coordinates' => 'QR Coordinates must include x, y, and size keys.'])->withInput();
        // }


        DB::beginTransaction(); // Start transaction

        try {
            $photoPath = null; // Initialize variable to store the relative path for the database

            // 2. Handle File Upload (Directly to public/img/ticket_photos)
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $file = $request->file('photo');

                $destinationPath = public_path('img/ticket_photos'); // Gets absolute path to public/img/ticket_photos

                // $filename = $file->hashName();

                $filename = $file->getClientOriginalName();

                if (!file_exists($destinationPath)) {
                    try {
                        mkdir($destinationPath, 0775, true);
                    } catch (\Exception $e) {
                        throw new \Exception("Failed to create directory: " . $e->getMessage());
                    }
                }

                // Move the uploaded file to the public destination
                try {
                    $file->move($destinationPath, $filename);
                } catch (\Exception $e) {
                    throw new \Exception("Failed to move uploaded photo: " . $e->getMessage());
                }

                $photoPath = 'img/ticket_photos/' . $filename;
            }
            // 3. Create Ticket Type
            $festival = $request->attributes->get('festival');
            if (!$festival) {
                // Should never happen because the EnsureFestivalAccess
                // middleware aborts with 404 when the festival in the
                // URL doesn't resolve, but we guard against it so the
                // user sees a clear error instead of a confusing DB
                // constraint violation.
                throw new \Exception(__('alert.no_festival_in_scope'));
            }
            $ticketType = TicketType::create([
                'festival_id' => $festival->id,
                'name' => $validatedData['name'],
                'price' => $validatedData['price'],
                'photo_path' => $photoPath,
                'qr_coordinates' => $validatedData['qr_coordinates'], // Store as JSON string
            ]);

            // 4. Create Ticket Commissions
            $now = now()->setTimezone('Europe/Belgrade'); // Use a consistent timestamp in Europe/Belgrade
            foreach ($validatedData['commissions'] as $commissionData) {
                $maxSold = isset($commissionData['max_sold']) && $commissionData['max_sold'] !== '' ? (int)$commissionData['max_sold'] : null;

                $ticketType->commissions()->create([
                    'min_sold' => $commissionData['min_sold'],
                    'max_sold' => $maxSold,
                    'commission_amount' => $commissionData['commission_amount'],
                    'valid_from' => $now, // Explicitly set, or rely on DB default if it's CURRENT_TIMESTAMP
                    'valid_to' => null,   // This tier is currently active
                ]);
            }

            DB::commit(); // Commit transaction

            // 5. Redirect with success message
            return redirect()->route('admin.ticket-types.index', ['festival' => $request->attributes->get('festival')])
                ->with('success', __('alert.ticket_type_created_success'));
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error

            // Log the error for debugging
            Log::error("Error creating ticket type: " . $e->getMessage());

            // Optionally delete the uploaded photo if creation failed after upload
            if (!empty($photoPath) && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            // Redirect back with error message
            return back()->with('error', __('alert.ticket_type_create_failed', ['message' => $e->getMessage()]))->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     * We use Route Model Binding here (TicketType $ticketType)
     */
    public function edit($id) // Assumes Route Model Binding
    {
        $ticketType = TicketType::findOrFail($id);
        // Eager load commissions to avoid N+1 issues in the view
        $ticketType->load('commissions');

        // Pass the ticket type (with its commissions) to the view
        return view('pages.admin.ticket_type.edit', compact('ticketType'));
    }

    /**
     * Update the specified resource in storage.
     * We use Route Model Binding here (TicketType $ticketType)
     */
    public function update(Request $request, $id) // Assumes Route Model Binding
    {
        $ticketType = TicketType::with('commissions')->findOrFail($id); // Eager load commissions

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('ticket_types')->ignore($ticketType->id)],
            'price' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'qr_coordinates' => 'required|json',
            'commissions' => 'required|array|min:1',
            'commissions.*.min_sold' => 'required|integer|min:0',
            'commissions.*.max_sold' => 'nullable|integer|min:0',
            'commissions.*.commission_amount' => 'required|numeric|min:0',
        ]);
        // Optional: Validate JSON structure more deeply (similar to store method)
        $qrCoordinates = json_decode($validatedData['qr_coordinates'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($qrCoordinates)) {
            return back()->withErrors(['qr_coordinates' => 'Invalid JSON format for QR Coordinates.'])->withInput();
        }


        DB::beginTransaction();
        try {
            $updateDataTicketType = [
                'name' => $validatedData['name'],
                'price' => $validatedData['price'],
                'qr_coordinates' => $validatedData['qr_coordinates'],
            ];

            // --- Handle Photo Update (as in your existing code) ---
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $newFile = $request->file('photo');
                $destinationPath = public_path('img/ticket_photos'); // Your storage path
                $filename = $newFile->hashName(); // Or getClientOriginalName() if you prefer

                // Delete Old Photo if exists
                if ($ticketType->photo_path) {
                    $oldPhotoAbsolutePath = public_path($ticketType->photo_path);
                    if (File::exists($oldPhotoAbsolutePath)) {
                        File::delete($oldPhotoAbsolutePath);
                    }
                }

                // Ensure directory exists & Move new photo
                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0775, true, true);
                }
                $newFile->move($destinationPath, $filename);
                $updateDataTicketType['photo_path'] = 'img/ticket_photos/' . $filename;
            }
            // --- End Photo Update Handling ---

            $ticketType->update($updateDataTicketType);

            // --- Update Commissions with Versioning ---
            $now = now()->setTimezone('Europe/Belgrade'); // Use a consistent timestamp in Europe/Belgrade

            // 1. Expire all currently active commission tiers for this ticket type
            $ticketType->commissions()
                ->whereNull('valid_to') // Find all tiers that are currently active
                ->update(['valid_to' => $now]); // Set their end date to now

            // 2. Create new commission tier versions based on the submitted data
            foreach ($validatedData['commissions'] as $commissionData) {
                $maxSold = isset($commissionData['max_sold']) && $commissionData['max_sold'] !== '' ? (int)$commissionData['max_sold'] : null;

                $ticketType->commissions()->create([
                    'min_sold' => $commissionData['min_sold'],
                    'max_sold' => $maxSold,
                    'commission_amount' => $commissionData['commission_amount'],
                    'valid_from' => $now,      // These new tiers are valid from now
                    'valid_to' => null,       // And are currently active (no end date yet)
                ]);
            }
            // --- End Update Commissions ---

            DB::commit();
            return redirect()->route('admin.ticket-types.index', ['festival' => $ticketType->festival_id])
                ->with('success', __('alert.ticket_type_updated_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating ticket type ID {$ticketType->id}: " . $e->getMessage(), ['trace' => substr($e->getTraceAsString(), 0, 1000)]);
            return back()->with('error', __('alert.ticket_type_update_failed', ['message' => $e->getMessage()]))->withInput();
        }
    }
    /**
     * Remove the specified resource from storage.
     * We use Route Model Binding here (TicketType $ticketType)
     */
    public function destroy($id) // Assumes Route Model Binding
    {
        $ticketType = TicketType::findOrFail($id);
        DB::beginTransaction();
        try {
            // Delete associated photo if it exists
            if ($ticketType->photo_path && Storage::disk('public')->exists($ticketType->photo_path)) {
                Storage::disk('public')->delete($ticketType->photo_path);
            }

            // Delete the ticket type. Associated commissions will be deleted automatically
            // due to the 'onDelete('cascade')' constraint defined in the migration.
            $ticketType->delete();

            DB::commit();

            return redirect()->route('admin.ticket-types.index', ['festival' => $ticketType->festival_id])
                ->with('success', __('alert.ticket_type_deleted_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting ticket type ID {$ticketType->id}: " . $e->getMessage());
            return redirect()->back()->with('error', __('alert.ticket_type_delete_failed', ['message' => $e->getMessage()]));
        }
    }
}
