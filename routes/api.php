<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlaceController;

// Example POST route
Route::post('/suggest', [PlaceController::class, 'suggest'])->name('place.suggest');
