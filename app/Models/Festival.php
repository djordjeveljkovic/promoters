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

    /**
     * Default brand colours used when a festival hasn't picked its own.
     * Keeping them on the model means every layout / seed / mail
     * template can fall back to the same values.
     */
    public const DEFAULT_PRIMARY_COLOR   = '#4f46e5';
    public const DEFAULT_SECONDARY_COLOR = '#818cf8';

    /** Hex colour regex used by form validation. */
    public const HEX_COLOR_REGEX = '/^#([0-9a-fA-F]{6})$/';

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

    /**
     * Resolved primary colour, falling back to the platform default if
     * the admin never set one.  Always returns a valid 6-digit hex.
     */
    public function primaryColor(string $fallback = null): string
    {
        $value = $this->primary_color ?: ($fallback ?? self::DEFAULT_PRIMARY_COLOR);
        return preg_match(self::HEX_COLOR_REGEX, $value) ? $value : self::DEFAULT_PRIMARY_COLOR;
    }

    public function secondaryColor(string $fallback = null): string
    {
        $value = $this->secondary_color ?: ($fallback ?? self::DEFAULT_SECONDARY_COLOR);
        return preg_match(self::HEX_COLOR_REGEX, $value) ? $value : self::DEFAULT_SECONDARY_COLOR;
    }

    /**
     * Whether the festival has its own brand colours (i.e. is using
     * something other than the platform default).
     */
    public function hasCustomTheme(): bool
    {
        return !empty($this->primary_color) || !empty($this->secondary_color);
    }

    /**
     * Pick a black-or-white text colour for a given background hex,
     * using the W3C luminance formula.  Useful for buttons / badges
     * that need a legible label on top of the festival's brand colour.
     */
    public function contrastColorOn(string $backgroundHex = null): string
    {
        $hex = $backgroundHex ?? $this->primaryColor();
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return '#ffffff';
        }
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        // sRGB → linear
        $linear = fn($c) => $c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);
        $l = 0.2126 * $linear($r) + 0.7152 * $linear($g) + 0.0722 * $linear($b);
        return $l > 0.5 ? '#0f172a' : '#ffffff';
    }

    /**
     * Take a user-submitted hex colour and return it in the canonical
     * "#rrggbb" lower-case form, or null if the value is blank.  Tolerant
     * of missing "#" prefix and mixed casing.
     */
    public static function normaliseColor(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = trim($value);
        if ($v === '') {
            return null;
        }
        if ($v[0] !== '#') {
            $v = '#' . $v;
        }
        return strtolower($v);
    }
}