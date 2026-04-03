<?php

use App\Http\Controllers\SeatController;
use App\Http\Controllers\StopController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/stops', [StopController::class, 'index']);
    Route::post('/search', [TripController::class, 'search']);
    Route::post('/seats', [SeatController::class, 'index']);
});
