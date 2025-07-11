<?php
namespace App\Services\Parking;

use App\Exceptions\CannotAllocateException;
use App\Models\ParkingSession;
use App\Models\ParkingSpot;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Log;

class CarStrategy implements AllocatorStrategy
{
    /**
     * @param Vehicle     $vehicle
     * @param ParkingSpot $spot
     *
     * @return ParkingSession
     */
    public function allocate(Vehicle $vehicle, ParkingSpot $spot): ParkingSession
    {
        if ($spot->type !== 'regular') {
            Log::channel('parking')->info("CannotAllocateException - Cars may only park in regular spots.", [
                'lot_id'           => $spot->parking_lot_id,
                'spot_number'      => $spot->spot_number,
                'vehicle_id'       => $vehicle->id,
                'vehicle_type'     => $vehicle->type,
                'timestamp'        => now()->toIso8601String(),
            ]);
            throw new CannotAllocateException('Cars may only park in regular spots.');
        }

        $session = ParkingSession::create([
            'vehicle_id' => $vehicle->id,
            'started_at' => now(),
        ]);

        $spot->update(['current_session_id' => $session->id]);

        return $session;
    }
}
