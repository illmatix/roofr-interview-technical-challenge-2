<?php

namespace Tests\Feature;

use App\Models\ParkingLot;
use App\Models\ParkingSpot;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkingLotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Arrange: create the parking lot
        $this->lot = ParkingLot::factory()->create();
    }

    /** @test */
    public function it_reports_total_capacity_and_available_spaces()
    {
        // Arrange
        $numberSpots = 100;
        ParkingSpot::factory()
                   ->count($numberSpots)
                   ->create([
                       'parking_lot_id' => $this->lot->id,
                       'type'           => 'regular',
                   ]);

        // Act
        $response = $this->getJson(route('parking.lot.show', ['lot' => $this->lot->id]));

        // Assert: total_capacity & available_spots live under "meta" from cache
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                        'name',
                        'spot_details'
                     ],
                     'meta' => [
                         'total_capacity',
                         'available_spots',
                     ],
                 ]);

        // And verify the values in meta
        $this->assertEquals($numberSpots, $response->json('meta.total_capacity'));
        $this->assertEquals($numberSpots, $response->json('meta.available_spots'));
    }


    /** @test */
    public function motorcycle_can_park_in_any_available_spot(): void
    {
        // Arrange: create a regular and a small spot
        $regular = ParkingSpot::factory()->create([
            'parking_lot_id' => $this->lot->id,
            'spot_number'    => '1',
            'type'           => 'regular',
        ]);
        $small = ParkingSpot::factory()->create([
            'parking_lot_id' => $this->lot->id,
            'spot_number'    => '2',
            'type'           => 'small',
        ]);

        // Act & Assert: motorcycle parks in each
        foreach ([$regular, $small] as $spot) {
            $payload = [
                'vehicle_type'  => 'motorcycle',
                'license_plate' => "MOTO-{$spot->spot_number}",
            ];

            $response = $this->postJson(
                route('parking.spot.park', ['spot' => $spot->id]),
                $payload
            );

            $response->assertStatus(201)
                     ->assertJsonPath('data.spot_number', $spot->spot_number)
                     ->assertJsonPath('data.is_occupied', true)
                     ->assertJsonPath('data.vehicle.license_plate', $payload['license_plate']);
        }

        // Verify that 2 spots are now occupied
        $after = $this->getJson(route('parking.lot.show', ['lot' => $this->lot->id]));
        $this->assertEquals(
            0,
            $after->json('available_spots')
        );
    }

    /** @test */
    public function car_can_only_park_in_regular_spots(): void
    {
        // Arrange: create a regular and a small spot
        $regular = ParkingSpot::factory()->create([
            'parking_lot_id' => $this->lot->id,
            'spot_number'    => '1',
            'type'           => 'regular',
        ]);
        $small = ParkingSpot::factory()->create([
            'parking_lot_id' => $this->lot->id,
            'spot_number'    => '2',
            'type'           => 'small',
        ]);

        // Car should fail on small spot
        $bad = $this->postJson(
            route('parking.spot.park', ['spot' => $small->id]),
            ['vehicle_type' => 'car', 'license_plate' => 'CAR-001']
        );
        $bad->assertStatus(400)
            ->assertJsonFragment([
                'message' => 'Cars may only park in regular spots.'
            ]);

        // Car succeeds on regular spot
        $good = $this->postJson(
            route('parking.spot.park', ['spot' => $regular->id]),
            ['vehicle_type' => 'car', 'license_plate' => 'CAR-002']
        );
        $good->assertStatus(201)
             ->assertJsonPath('data.spot_number', $regular->spot_number)
             ->assertJsonPath('data.is_occupied', true);
    }

    /** @test */
    public function van_requires_three_contiguous_regular_spots(): void
    {
        // Arrange: create 5 regular spots with numbers 10-14
        $spots = ParkingSpot::factory()
                            ->count(5)
                            ->state(new Sequence(
                                ['spot_number' => '10', 'parking_lot_id' => $this->lot->id, 'type' => 'regular'],
                                ['spot_number' => '11', 'parking_lot_id' => $this->lot->id, 'type' => 'regular'],
                                ['spot_number' => '12', 'parking_lot_id' => $this->lot->id, 'type' => 'regular'],
                                ['spot_number' => '13', 'parking_lot_id' => $this->lot->id, 'type' => 'regular'],
                                ['spot_number' => '14', 'parking_lot_id' => $this->lot->id, 'type' => 'regular'],
                            ))
                            ->create();

        $startSpot = $spots->firstWhere('spot_number', '10');

        // Happy: park a van starting at spot 10 (uses 10,11,12)
        $happy = $this->postJson(
            route('parking.spot.park', ['spot' => $startSpot->id]),
            ['vehicle_type' => 'van', 'license_plate' => 'VAN-123']
        );
        $happy->assertStatus(201)
              ->assertJsonPath('data.spot_number', $startSpot->spot_number)
              ->assertJsonPath('data.is_occupied', true);

        // Sad: occupy spot 11 with a car so van cannot fit
        $blocked = $spots->firstWhere('spot_number', '11');
        $this->postJson(
            route('parking.spot.park', ['spot' => $blocked->id]),
            ['vehicle_type' => 'car', 'license_plate' => 'CAR-XYZ']
        );

        $fail = $this->postJson(
            route('parking.spot.park', ['spot' => $startSpot->id]),
            ['vehicle_type' => 'van', 'license_plate' => 'VAN-456']
        );
        $fail->assertStatus(400)
             ->assertJsonFragment([
                 'message' => 'A van requires 3 contiguous regular spots.'
             ]);
    }

    /** @test */
    public function unpark_frees_up_spots(): void
    {
        // Arrange: create two spots and park a car
        $spot = ParkingSpot::factory()->create([
            'parking_lot_id' => $this->lot->id,
            'spot_number'    => '20',
            'type'           => 'regular',
        ]);

        $parked = $this->postJson(
            route('parking.spot.park', ['spot' => $spot->id]),
            ['vehicle_type' => 'car', 'license_plate' => 'CAR-UPK']
        );
        $parked->assertStatus(201);

        // Act: unpark
        $response = $this->postJson(
            route('parking.spot.unpark', ['spot' => $spot->id])
        );

        // Assert: spot is freed
        $response->assertStatus(200)
                 ->assertJsonPath('data.0.is_occupied', false)
                 ->assertJsonPath('data.0.spot_number', '20');

        // and available spots increments
        $after = $this->getJson(route('parking.lot.show', ['lot' => $this->lot->id]));
        $after->assertJsonPath('meta.available_spots', 1);
    }

    /** @test */
    public function vehicle_can_be_updated(): void
    {
        // 1) Seed one spot and park a car into it
        $spot = ParkingSpot::factory()->create([
            'parking_lot_id' => $this->lot->id,
            'spot_number'    => '1',
            'type'           => 'regular',
        ]);

        $parked = $this->postJson(
            route('parking.spot.park', ['spot' => $spot->id]),
            ['vehicle_type' => 'car', 'license_plate' => 'CAR-UPK']
        )->assertStatus(201);

        // 2) Grab the vehicle ID out of that response
        $vehicleId = $parked->json('data.vehicle.id');

        // 3) Prepare the payload for updating make/model/color
        $payload = [
            'make'  => 'Honda',
            'model' => 'Civic',
            'color' => 'Red',
        ];

        // 4) Call your patch endpoint (spot/{spot}/update)
        $response = $this->patchJson(
            route('parking.spot.update', ['spot' => $spot->id]),
            $payload
        );

        // 5) Assert you got a 200 and the JSON has the new values
        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $vehicleId)
                 ->assertJsonPath('data.make', 'Honda')
                 ->assertJsonPath('data.model', 'Civic')
                 ->assertJsonPath('data.color', 'Red');

        // 6) And finally, check the database really saved them
        $this->assertDatabaseHas('vehicles', [
            'id'    => $vehicleId,
            'make'  => 'Honda',
            'model' => 'Civic',
            'color' => 'Red',
        ]);
    }
}
