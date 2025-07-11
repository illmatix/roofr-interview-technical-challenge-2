<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ParkingSpotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'spot_number' => $this->spot_number,
            'is_occupied' => (bool) $this->is_occupied,

            // only include 'vehicle' when there's an active session
            'vehicle' => $this->when($this->parkingSession, function() {
                $v = $this->parkingSession->vehicle;
                return [
                    'id'            => $v->id,
                    'type'          => $v->type,
                    'license_plate' => $v->license_plate,
                ];
            }),

            'started_at' => optional($this->parkingSession)->started_at,
            'ended_at'   => optional($this->parkingSession)->ended_at,
        ];
    }
}
