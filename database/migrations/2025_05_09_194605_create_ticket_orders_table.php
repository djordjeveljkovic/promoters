<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.buyer
     */
    public function up(): void
    {
        Schema::create('ticket_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordered_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('requested_by')->nullable()->constrained('users'); // promoter who requested
            $table->string('email');
            $table->decimal('paid', 8, 2);
            $table->decimal('total', 8, 2);
            $table->decimal('total_commission_earned', 10, 2)->nullable();
            $table->enum('job_status', ['pending', 'processing', 'failed', 'blocked', 'completed', 'sent'])->default('pending');
            $table->text('job_failure_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_orders');
    }
};
