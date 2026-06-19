<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Festival extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'year',
        'tagline',
        'description',
        'location',
        'start_date',
        'end_date',
        'logo_path',
        'primary_color',
        'secondary_color',
        'status',
        'is_public',
        'created_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_public' => 'boolean',
    ];

    /* -------------------- Boot -------------------- */

    protected static function booted(): void
    {
        static::saving(function (self $festival) {
            if (empty($festival->slug)) {
                $festival->slug = static::makeUniqueSlug($festival);
            }
        });
    }

    public static function makeUniqueSlug(self $festival): string
    {
        $base = Str::slug(($festival->name ?? 'festival') . ' ' . ($festival->year ?? ''));
        $base = $base !== '' ? $base : 'festival';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->where('id', '!=', $festival->id ?? 0)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    /* -------------------- Relations -------------------- */

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(TicketOrder::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'festival_user')
            ->withPivot(['role_in_festival', 'assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivot('role_in_festival', 'admin');
    }

    public function promoters(): BelongsToMany
    {
        return $this->users()->wherePivot('role_in_festival', 'promoter');
    }

    public function subPromoters(): BelongsToMany
    {
        return $this->users()->wherePivot('role_in_festival', 'sub_promoter');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* -------------------- Scopes -------------------- */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /* -------------------- Helpers -------------------- */

    public function displayName(): string
    {
        return "{$this->name} {$this->year}";
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}