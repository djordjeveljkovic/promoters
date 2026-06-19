<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Festivals represent a single edition of an event (e.g. "REFEST 2026",
     * "Lovefest 2027").  They own ticket types, orders, and through the
     * `festival_user` pivot, the admins / promoters / sub-promoters that
     * are allowed to operate on them.
     */
    public function up(): void
    {
        Schema::create('festivals', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // "REFEST", "Lovefest"
            $table->string('slug')->unique();          // "refest-2026"
            $table->unsignedSmallInteger('year');      // 2026
            $table->string('tagline')->nullable();     // short marketing line
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#ff2d92');
            $table->string('secondary_color', 7)->default('#5ce1ff');
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->boolean('is_public')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['name', 'year']);
            $table->index('status');
            $table->index('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('festivals');
    }
};