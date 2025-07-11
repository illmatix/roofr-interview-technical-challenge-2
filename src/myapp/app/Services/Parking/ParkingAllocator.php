<?php

namespace App\Services\Parking;

use App\Exceptions\CannotAllocateException;
use App\Models\ParkingSession;
use App\Models\ParkingSpot;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Log;

class ParkingAllocator
{
    protected array $strategies = [
        'motorcycle' => MotorcycleStrategy::class,
        'car'        => CarStrategy::class,
        'van'        => VanStrategy::class,
    ];

    /**
     * @param Vehicle     $vehicle
     * @param ParkingSpot $spot
     *
     * @return ParkingSession
     * @throws CannotAllocateException
     */
    public function allocate(Vehicle $vehicle, ParkingSpot $spot): ParkingSession
    {
        $type = $vehicle->type;

        if (!isset($this->strategies[$type])) {
            Log::channel('parking')->info("CannotAllocateException - Unknown vehicle type: {$type}", [
                'lot_id'           => $spot->parking_lot_id,
                'spot_number'      => $spot->spot_number,
                'vehicle_id'       => $vehicle->id,
                'vehicle_type'     => $vehicle->type,
                'timestamp'        => now()->toIso8601String(),
            ]);
            throw new CannotAllocateException("Unknown vehicle type: {$type}");
        }

        /** @var AllocatorStrategy $strategy */
        $strategy = app($this->strategies[$type]);
        // now calls the right signature
        $session = $strategy->allocate($vehicle, $spot);

        // Centralized logging, once per allocation
        Log::channel('parking')->info('Parking started', [
            'lot_id'           => $spot->parking_lot_id,
            'spot_number'      => $spot->spot_number,
            'vehicle_id'       => $vehicle->id,
            'vehicle_type'     => $vehicle->type,
            'session_id'       => $session->id,
            'timestamp'        => now()->toIso8601String(),
        ]);

        return $session;
    }
}
