<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Scope ticket types, orders, order items, and individual tickets to a
     * specific festival.  Without this, a REFEST 2026 ticket type would be
     * visible to a Lovefest promoter.
     *
     * For tables that are fully owned by another scoped table (items via
     * order, tickets via order) the column is denormalized for fast filtering.
     */
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->foreignId('festival_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index('festival_id');
        });

        Schema::table('ticket_orders', function (Blueprint $table) {
            $table->foreignId('festival_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index('festival_id');
        });

        Schema::table('ticket_order_items', function (Blueprint $table) {
            $table->foreignId('festival_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index('festival_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('festival_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index('festival_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('festival_id');
        });
        Schema::table('ticket_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('festival_id');
        });
        Schema::table('ticket_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('festival_id');
        });
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('festival_id');
        });
    }
};