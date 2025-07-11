<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ParkingSession>
 */
class ParkingSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'started_at' => now(),
            'ended_at'   => null,
        ];
    }

    public function ended(): self
    {
        return $this->state([
            'ended_at' => now(),
        ]);
    }
}
