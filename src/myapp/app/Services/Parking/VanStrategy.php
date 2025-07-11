<?php

namespace App\Services\Parking;

use App\Exceptions\CannotAllocateException;
use App\Models\ParkingSession;
use App\Models\ParkingSpot;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Log;

class VanStrategy implements AllocatorStrategy
{
    /**
     * @param Vehicle     $vehicle
     * @param ParkingSpot $spot
     *
     * @return ParkingSession
     */
    public function allocate(Vehicle $vehicle, ParkingSpot $spot): ParkingSession
    {
        $start    = (int)$spot->spot_number;
        $required = [
            (string)$start,
            (string)($start + 1),
            (string)($start + 2),
        ];

        $contiguous = $spot->parkingLot
            ->parkingSpots()
            ->whereIn('spot_number', $required)
            ->where('type', 'regular')
            ->where('is_active', true)
            ->whereNull('current_session_id')
            ->get();

        if ($contiguous->count() < 3) {
            Log::channel('parking')->info("CannotAllocateException - A van requires 3 contiguous regular spots.", [
                'lot_id'           => $spot->parking_lot_id,
                'spot_number'      => $spot->spot_number,
                'vehicle_id'       => $vehicle->id,
                'vehicle_type'     => $vehicle->type,
                'timestamp'        => now()->toIso8601String(),
            ]);
            throw new CannotAllocateException('A van requires 3 contiguous regular spots.');
        }

        $session = ParkingSession::create([
            'vehicle_id' => $vehicle->id,
            'started_at' => now(),
        ]);

        // assign all three spots
        $contiguous->each(fn(ParkingSpot $s) => $s->update(['current_session_id' => $session->id]));

        return $session;
    }
}
