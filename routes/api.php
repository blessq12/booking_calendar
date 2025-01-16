<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SaunaController;
use App\Http\Controllers\Api\ScheduleController;

use App\Http\Controllers\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('api')->group(function () {
    Route::get('/saunas', [SaunaController::class, 'index']);
    Route::get('/schedules', [ScheduleController::class, 'index']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
    Route::get('/clients/{client}/bookings', [BookingController::class, 'getClientBookings']);
});
