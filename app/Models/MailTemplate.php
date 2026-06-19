<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MailTemplate
 *
 * A customisable email template. Two flavours:
 *  - **Global** (festival_id IS NULL) — fallback for every festival.
 *  - **Festival-scoped** — overrides the global for that festival only.
 *
 * The `html_body` is a full Blade template. Use the placeholders listed in
 * {@see \App\Support\Mail\MailTemplateRenderer::availableVariables()} to
 * inject order / ticket / festival data.
 */
class MailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'festival_id',
        'name',
        'subject',
        'html_body',
        'css',
        'from_address',
        'from_name',
        'is_active',
        'version',
        'last_edited_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version'   => 'integer',
    ];

    /* --------------- Relationships --------------- */

    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    /* --------------- Scopes --------------- */

    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('festival_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /* --------------- Helpers --------------- */

    /**
     * True if this template is the global default (not tied to a festival).
     */
    public function isGlobal(): bool
    {
        return $this->festival_id === null;
    }

    /**
     * Human-readable label for the dropdown ("REFEST 2026 — Customer
     * tickets" or just the name for the global default).
     */
    public function scopeLabel(): string
    {
        if ($this->isGlobal()) {
            return "{$this->name} (global default)";
        }
        $f = $this->festival;
        return $f ? "{$this->name} — {$f->displayName()}" : $this->name;
    }
}
