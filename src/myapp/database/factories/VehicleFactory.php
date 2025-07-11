<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type'          => fake()->randomElement(['motorcycle', 'car', 'van']),
            'license_plate' => strtoupper($this->faker->bothify('???-###')),
            'make'          => $this->faker->company(),
            'model'         => $this->faker->word(),
            'color'         => $this->faker->safeColorName(),
        ];
    }

    /**
     * Optional states to spin up a specific vehicle type when needed
     */
    public function motorcycle(): self
    {
        return $this->state([
            'type' => 'motorcycle',
        ]);
    }

    public function car(): self
    {
        return $this->state([
            'type' => 'car',
        ]);
    }

    public function van(): self
    {
        return $this->state([
            'type' => 'van',
        ]);
    }
}
