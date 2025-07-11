<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkingSpot extends Model
{
    use HasFactory;

    protected $fillable = [
        'parking_lot_id',
        'spot_number',
        'type',
        'is_active',
        'current_session_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata'  => 'array',
    ];

    // Automatically include this in every JSON representation
    protected $appends = ['is_occupied'];

    /**
     * A spot belongs to a lot.
     */
    public function parkingLot(): BelongsTo
    {
        return $this->belongsTo(ParkingLot::class);
    }

    /**
     * The current active parking session, if any.
     */
    public function parkingSession(): BelongsTo
    {
        return $this->belongsTo(ParkingSession::class, 'current_session_id');
    }

    /**
     * Determine if the spot is currently occupied.
     */
    public function getIsOccupiedAttribute(): bool
    {
        return $this->parkingSession && is_null($this->parkingSession->ended_at);
    }
}
