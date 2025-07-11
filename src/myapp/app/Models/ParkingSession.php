<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    /**
     * The vehicle associated with this session.
     *
     * @return BelongsTo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * All spots linked to this session (for multi-spot vehicles).
     * 
     * @return HasMany
     */
    public function parkingSpots(): HasMany
    {
        return $this->hasMany(ParkingSpot::class, 'current_session_id');
    }
}
