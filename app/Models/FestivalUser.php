<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pivot model for the `festival_user` table.
 *
 * Carries the per-festival role plus audit fields (assigned_by, assigned_at).
 */
class FestivalUser extends Model
{
    protected $table = 'festival_user';

    protected $fillable = [
        'festival_id',
        'user_id',
        'role_in_festival',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /** True when this pivot marks the user as a "promoter manager" on the festival. */
    public function isPromoterManager(): bool
    {
        return $this->role_in_festival === 'promoter_manager';
    }

    /** True when this pivot marks the user as a plain "promoter" on the festival. */
    public function isRegularPromoter(): bool
    {
        return $this->role_in_festival === 'promoter';
    }

    /** True when this pivot marks the user as a sub-promoter on the festival. */
    public function isSubPromoter(): bool
    {
        return $this->role_in_festival === 'sub_promoter';
    }

    public function managerCommissions(): HasMany
    {
        return $this->hasMany(ManagerCommission::class);
    }

    public function subPromoterCommissions(): HasMany
    {
        return $this->hasMany(SubPromoterCommission::class);
    }
}
