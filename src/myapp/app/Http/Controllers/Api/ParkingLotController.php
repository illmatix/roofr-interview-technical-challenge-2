<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ParkingLotResource;
use App\Models\ParkingLot;
use Illuminate\Support\Facades\Cache;

class ParkingLotController extends Controller
{
    /**
     * @param int $lotId
     *
     * @return ParkingLotResource
     */
    public function show(int $lotId): ParkingLotResource
    {
        // 1) Load the lot with its spots & their sessions
        $lot = ParkingLot::findOrFail($lotId)
                         ->load('parkingSpots.parkingSession');

        // 2) Cache the summary counts as before
        $summary = Cache::tags("lot:{$lot->id}")
                        ->remember("lot:{$lot->id}:summary", 60, function() use ($lot) {
                            return [
                                'total_capacity'  => $lot->parkingSpots()->count(),
                                'available_spots' => $lot->parkingSpots()
                                                         ->whereNull('current_session_id')
                                                         ->count(),
                            ];
                        });

        // 3) Return the resource *with* the spots loaded
        return (new ParkingLotResource($lot))
            ->additional(['meta' => $summary]);
    }
}
