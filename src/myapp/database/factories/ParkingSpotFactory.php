<?php

namespace Database\Factories;

use App\Models\ParkingLot;
use App\Models\ParkingSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ParkingSpot>
 */
class ParkingSpotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parking_lot_id'     => ParkingLot::factory(),
            'spot_number'        => $this->faker->unique()->numerify('###'),
            'is_active'          => true,
            'current_session_id' => null,
        ];
    }

    /**
     * Mark the spot as occupied by spinning up a session.
     */
    public function occupied(): self
    {
        return $this->state(function (array $attributes) {
            $session = ParkingSession::factory()->create();

            return [
                'is_occupied'        => true,
                'current_session_id' => $session->id,
            ];
        });
    }
}
