<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SaunaController;
use App\Http\Controllers\Api\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {

    Route::get('/saunas', [SaunaController::class, 'index']);

    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::post('/', [BookingController::class, 'store']);
        Route::put('/{booking}', [BookingController::class, 'update']);
        Route::delete('/{booking}', [BookingController::class, 'destroy']);
    });

    Route::get('/clients/{client}/bookings', [BookingController::class, 'getClientBookings']);
});
