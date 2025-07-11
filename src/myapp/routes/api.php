<?php

use App\Http\Controllers\Api\ParkingLotController;
use App\Http\Controllers\Api\ParkingSpotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// group under API versioning
Route::prefix('v1/parking')->middleware([
    // throttle to 60 requests per minute per IP
    'throttle:60,1',
    'add.correlation.id'
])->group(function () {
    // GET  /api/v1/parking/lot/{lot}
    Route::get('lot/{lot}', [ParkingLotController::class, 'show'])
        ->name('parking.lot.show');

    // POST /api/v1/parking/spot/{spot}/park
    Route::post('spot/{spot}/park', [ParkingSpotController::class, 'park'])
        ->name('parking.spot.park');

    // POST /api/v1/parking/spot/{spot}/unpark
    Route::post('spot/{spot}/unpark', [ParkingSpotController::class, 'unpark'])
        ->name('parking.spot.unpark');

    // PATCH /api/v1/parking/spot/{spot}/update
    Route::patch('spot/{spot}/update', [ParkingSpotController::class, 'update'])
         ->name('parking.spot.update');
});
