<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\MainController;


Route::domain('booking.' . env('APP_DOMAIN'))->group(function () {
    Route::controller(ScheduleController::class)->group(function () {
        Route::get('/', 'index')->name('index');
    });
});

Route::controller(MainController::class)->group(function () {
    Route::get('/', function () {
        return 'index page';
    });
});

Route::prefix('api')->group(function () {
    require_once 'api.php';
});
