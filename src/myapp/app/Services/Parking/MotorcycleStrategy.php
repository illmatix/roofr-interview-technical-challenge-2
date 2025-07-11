<?php

namespace App\Services\Parking;

use App\Models\ParkingSession;
use App\Models\ParkingSpot;
use App\Models\Vehicle;

class MotorcycleStrategy implements AllocatorStrategy
{
    /**
     * @param Vehicle     $vehicle
     * @param ParkingSpot $spot
     *
     * @return ParkingSession
     */
    public function allocate(Vehicle $vehicle, ParkingSpot $spot): ParkingSession
    {
        $session = ParkingSession::create([
            'vehicle_id' => $vehicle->id,
            'started_at' => now(),
        ]);

        $spot->update(['current_session_id' => $session->id]);

        return $session;
    }
}
