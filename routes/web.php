<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\AuthController;

// Маршруты аутентификации
Route::prefix('auth')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/authorize', [AuthController::class, 'authorize'])->name('authorize');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Публичные маршруты
Route::get('/', [MainController::class, 'index'])->name('index');

// Защищенные маршруты
Route::middleware('auth')->group(function () {

    Route::get('/booking', [ScheduleController::class, 'index'])->name('booking');
});

// API маршруты
Route::prefix('api')->middleware('web')->group(function () {
    require __DIR__ . '/api.php';
});
