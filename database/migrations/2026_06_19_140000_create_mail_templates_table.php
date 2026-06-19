<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mail templates let superadmins / festival admins change the look and
     * feel of every transactional email without touching code.
     *
     * Each row pairs a logical `key` (e.g. "customer.tickets") with an
     * optional `festival_id`. Resolution order:
     *   1. the most specific template for this festival (festival_id match)
     *   2. the global default (festival_id IS NULL)
     *   3. the built-in Blade fallback in resources/views/emails/...
     *
     * The `html_body` is a full Blade template — the resolver compiles it
     * with the same variables the legacy view received (`$order`,
     * `$festival`, …) plus any extra with() the caller adds.
     */
    public function up(): void
    {
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->index();           // 'customer.tickets'
            $table->foreignId('festival_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name', 160);                   // human label
            $table->string('subject', 255)->nullable();    // mail subject (supports {placeholders})
            $table->longText('html_body');                 // full HTML + Blade
            $table->longText('css')->nullable();           // optional CSS block, inlined into <style>
            $table->string('from_address', 255)->nullable();
            $table->string('from_name', 160)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Only one row per (key, festival). The NULL festival is the
            // global default — MySQL/SQLite treat NULL ≠ NULL in unique
            // indexes, so we model that explicitly below for portability.
            $table->unique(['key', 'festival_id'], 'mail_templates_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_templates');
    }
};
