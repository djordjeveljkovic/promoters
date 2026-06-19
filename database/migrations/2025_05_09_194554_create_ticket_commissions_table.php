<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_type_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('min_sold'); // Inclusive lower bound for the tier
            $table->unsignedInteger('max_sold')->nullable(); // Inclusive upper bound for the tier; null indicates no upper limit for this tier
            $table->decimal('commission_amount', 8, 2); // Commission per ticket within this tier

            $table->timestamp('valid_from')->default(DB::raw('CURRENT_TIMESTAMP')); // Indicates when this tier version becomes active
            $table->timestamp('valid_to')->nullable(); // Indicates when this tier version expires (NULL means it's currently active)

            $table->timestamps(); // created_at, updated_at for the audit of the record itself

            // Optional: Add an index that will be useful for querying active tiers
            $table->index(['ticket_type_id', 'valid_from', 'valid_to'], 'idx_ticket_commissions_versioning');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_commissions');
    }
};
