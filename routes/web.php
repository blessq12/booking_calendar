<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\MainController;


Route::domain('booking.' . env('APP_URL'))->group(function () {
    Route::controller(ScheduleController::class)->group(function () {
        Route::get('/', 'index')->name('index');
    });
});

Route::get('/booking', [ScheduleController::class, 'index']);

Route::controller(MainController::class)->group(function () {
    Route::get('/', 'index')->name('index');
});

Route::prefix('api')->group(function () {
    require_once 'api.php';
});
