<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CannotAllocateException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ParkVehicleRequest;
use App\Http\Requests\UnparkVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\ParkingSpotResource;
use App\Http\Resources\VehicleResource;
use App\Models\ParkingSpot;
use App\Models\Vehicle;
use App\Services\Parking\ParkingAllocator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ParkingSpotController extends Controller
{
    /**
     * Park a vehicle in one or more spots.
     *
     * @param ParkVehicleRequest $request
     * @param int                $spotId
     * @param ParkingAllocator   $allocator
     *
     * @return JsonResponse
     */
    public function park(ParkVehicleRequest $request, int $spotId, ParkingAllocator $allocator): JsonResponse
    {
        // 1) Issues with Loading via {spot} param - manually load the spot being requested.
        $spot = ParkingSpot::findOrFail($spotId);

        // 2) validate + extract request data.
        $data = $request->validated();

        // 3) find or create the vehicle by plate.
        $vehicle = Vehicle::firstOrCreate(
            ['license_plate' => $data['license_plate']],
            [
                'type'  => $data['vehicle_type'],
                'make'  => $data['make'] ?? null,
                'model' => $data['model'] ?? null,
                'color' => $data['color'] ?? null,
            ]
        );

        // 4) try and allocate the parking spot to the vehicle.
        try {
            $session = $allocator->allocate($vehicle, $spot);
        } catch (CannotAllocateException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }

        // 5) refresh the spot model to reflect possible changes.
        $spot->refresh()->load('parkingSession.vehicle');

        // 6) clear the cache tag so the next request will rebuild it
        Cache::tags("lot:{$spot->parking_lot_id}")->flush();

        // 7) return success json
        return (new ParkingSpotResource($spot))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Unpark (end) a session and free spot(s).
     *
     * @param UnparkVehicleRequest $request
     * @param int                  $spotId
     *
     * @return JsonResponse
     */
    public function unpark(UnparkVehicleRequest $request, int $spotId)
    {
        // 1) Issues with Loading via {spot} param - manually load the spot being requested.
        $spot = ParkingSpot::findOrFail($spotId);

        // 2) Get the current spots' session
        $session = $spot->parkingSession;
        if (!$session || $session->ended_at) {
            return response()->json([
                'message' => 'No active parking session found for this spot.',
            ], 404);
        }

        // 3) Set this session as ended.
        $session->update(['ended_at' => now()]);

        // 4) Free up all spots for the ended session.
        $freed = ParkingSpot::where('current_session_id', $session->id)
                            ->get()
                            ->each(fn(ParkingSpot $s) => $s->update([
                                'current_session_id' => null,
                            ]));

        // 5) Reload the spot for data changes, and get relationships.
        $spot = $spot->refresh()->load('parkingSession.vehicle');

        // 6) clear the cache tag so the next request will rebuild it
        Cache::tags("lot:{$spot->parking_lot_id}")->flush();

        // 7) return the just-unparked spot
        return ParkingSpotResource::collection(
            $freed->fresh()->load('parkingSession.vehicle')
        )->response()->setStatusCode(200);
    }

    /**
     * Update vehicle details (make, model, color) via AI-integrated camera system
     */
    public function update(
        UpdateVehicleRequest $request,
        int                  $spotId
    ): JsonResponse
    {
        // 1) Load the requested spot or 404
        $spot = ParkingSpot::findOrFail($spotId);

        // 2) Get its active parking session
        $session = $spot->parkingSession;
        if (! $session || $session->ended_at) {
            return response()->json([
                'message' => 'No active parking session found for this spot.',
            ], 404);
        }

        // 3) Get the vehicle instance
        $vehicle = $session->vehicle;

        // 4) Update its attributes
        $vehicle->update($request->validated());

        // 5) Return the updated VehicleResource
        return (new VehicleResource($vehicle->refresh()))
            ->response()
            ->setStatusCode(200);
    }
}
