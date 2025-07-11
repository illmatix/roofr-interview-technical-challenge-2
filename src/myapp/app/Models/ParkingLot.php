<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingLot extends Model
{
    use HasFactory;

    protected $fillable   = ['name', 'is_active'];

    public function parkingSpots(): HasMany
    {
        return $this->hasMany(ParkingSpot::class);
    }
}
