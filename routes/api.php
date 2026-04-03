<?php

use App\Http\Controllers\StopController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::get('/stops', [StopController::class, 'index']);
