<?php

namespace Database\Seeders;

use App\Models\ParkingLot;
use App\Models\ParkingSpot;
use Illuminate\Database\Seeder;

class ParkingLotSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Create one lot
        $lot = ParkingLot::factory()->create([
            'name' => 'Downtown Garage',
        ]);

        // 2) Seed 100 regular spots
        ParkingSpot::factory()
                   ->count(100)
                   ->create([
                       'parking_lot_id' => $lot->id,
                       'type'           => 'regular',
                   ]);
    }
}
