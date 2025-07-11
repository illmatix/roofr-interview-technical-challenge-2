<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'license_plate',
        'make',
        'model',
        'color',
    ];

    /**
     * Get all parking sessions for this vehicle.
     *
     * @return HasMany
     */
    public function parkingSessions(): HasMany
    {
        return $this->hasMany(ParkingSession::class);
    }
}
