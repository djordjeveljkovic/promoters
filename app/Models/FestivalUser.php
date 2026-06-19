<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}