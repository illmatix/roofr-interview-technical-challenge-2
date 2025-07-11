<?php

namespace App\Services\Parking;

use App\Exceptions\CannotAllocateException;
use App\Models\ParkingSession;
use App\Models\ParkingSpot;
use App\Models\Vehicle;

interface AllocatorStrategy
{
    /**
     * @throws CannotAllocateException
     */
    public function allocate(Vehicle $vehicle, ParkingSpot $spot): ParkingSession;
}
