<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;

Route::controller(ScheduleController::class)->group(function () {
    Route::get('/', 'index')->name('index');
});

Route::prefix('api')->group(function () {
    require_once 'api.php';
});
