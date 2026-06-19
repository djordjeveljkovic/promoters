<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Required for DB operations
// Illuminate\Support\Str; // Str::random() is no longer used for this specific generation

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Ensure the column exists and is nullable for the update process.
        // Column length 15 is fine for a 6-character string.
        Schema::table('ticket_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_orders', 'order_number')) {
                $table->string('order_number', 15)->nullable()->after('id');
            } else {
                $table->string('order_number', 15)->nullable()->change();
            }
        });

        // Step 2: Populate existing NULL or empty string order_numbers
        DB::table('ticket_orders')
            ->whereNull('order_number')
            ->orWhere('order_number', '')
            ->orderBy('id') // Important for chunkById
            ->chunkById(100, function ($ordersToUpdate) {
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);

                foreach ($ordersToUpdate as $order) {
                    $uniqueOrderNumber = '';
                    do {
                        $currentRandomString = '';
                        for ($i = 0; $i < 6; $i++) { // Length is 6
                            try {
                                // Use random_int for cryptographically secure random numbers
                                $currentRandomString .= $characters[random_int(0, $charactersLength - 1)];
                            } catch (\Exception $e) {
                                // Fallback for environments where random_int might fail (highly unlikely)
                                // or handle error appropriately
                                $currentRandomString .= $characters[mt_rand(0, $charactersLength - 1)];
                            }
                        }
                        $uniqueOrderNumber = $currentRandomString;
                    } while (DB::table('ticket_orders')->where('order_number', $uniqueOrderNumber)->exists());

                    DB::table('ticket_orders')
                        ->where('id', $order->id)
                        ->update(['order_number' => $uniqueOrderNumber]);
                }
            });

        // Step 3: Now that all rows should have a unique order number,
        // make the column non-nullable and add the unique constraint.
        Schema::table('ticket_orders', function (Blueprint $table) {
            $table->string('order_number', 15)->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     * The down method remains the same.
     */
    public function down(): void
    {
        Schema::table('ticket_orders', function (Blueprint $table) {
            $table->dropColumn('order_number');
        });
    }
};
