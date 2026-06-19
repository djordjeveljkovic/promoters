<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot: which users operate on which festivals, and in which role.
     *
     *  - a superadmin is a user with `users.role = 'superadmin'` (global) and
     *    does NOT need a row here to access every festival.
     *  - an admin / promoter / sub_promoter gets access only to the festivals
     *    listed in this pivot with the matching role_in_festival.
     */
    public function up(): void
    {
        Schema::create('festival_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('festival_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role_in_festival', ['admin', 'promoter', 'sub_promoter']);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            // A user can hold at most one assignment per festival.
            $table->unique(['festival_id', 'user_id', 'role_in_festival'], 'festival_user_unique');
            $table->index(['user_id', 'role_in_festival']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('festival_user');
    }
};