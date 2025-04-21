<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\AuthController;

// Route::domain('booking.' . env('APP_URL'))
//     ->middleware('auth')
//     ->group(function () {
//         Route::controller(ScheduleController::class)->group(function () {
//             Route::get('/', 'index')->name('index');
//         });
//     });

Route::middleware('auth')->group(function () {
    Route::get('/booking', [ScheduleController::class, 'index'])->name('booking');
});

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/authorize', 'authorize')->name('authorize');
    Route::get('/logout', 'logout')->name('logout');
});

Route::controller(MainController::class)->group(function () {
    Route::get('/', 'index')->name('index');
});

Route::prefix('api')->group(function () {
    require_once 'api.php';
});
