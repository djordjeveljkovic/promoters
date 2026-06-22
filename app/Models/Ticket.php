<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $fillable = [
        'festival_id',
        'code',
        'ticket_type_id',
        'ticket_order_id',
        'is_active',
        'scanned_at',
        'user_id',
        'image_path',
        'qr_code_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function ticketOrder(): BelongsTo
    {
        return $this->belongsTo(TicketOrder::class);
    }

    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }
}
