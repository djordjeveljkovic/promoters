<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * P-070: promoter public profile.
     *
     * Adds a short bio (markdown-safe text), an optional public avatar
     * path, and a public visibility flag to `users`.  When `is_public`
     * is false, the promoter is hidden from the public `/p/{id}` route.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bio', 500)->nullable()->after('paid');
            $table->string('avatar_path')->nullable()->after('bio');
            $table->boolean('is_public')->default(false)->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'avatar_path', 'is_public']);
        });
    }
};
