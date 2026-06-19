<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    protected $fillable = [
        'name',
        'price',
        'photo_path',
        'qr_coordinates',
    ];

    protected $casts = [
        'qr_coordinates' => 'array',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(TicketCommission::class);
    }

    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }


    public function getCommissionForSoldCount(int $soldCount): float
    {
        $threshold = $this->commissions()
            ->where('min_sold', '<=', $soldCount)
            ->where(function ($query) use ($soldCount) {
                $query->where('max_sold', '>', $soldCount)
                    ->orWhereNull('max_sold');
            })
            ->orderBy('min_sold', 'desc')
            ->first();

        return $threshold ? $threshold->commission_amount : 0.0;
    }
}
