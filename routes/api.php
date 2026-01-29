<?php

use App\Http\Controllers\PostalCodeController;
use App\Http\Controllers\SensorEventController;
use App\Http\Controllers\StreetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/postal-codes/search', [PostalCodeController::class, 'searchPostalCodes']);

Route::get('/streets/search', [StreetController::class, 'search']);

Route::middleware('api.key')->group(function () {
    Route::post('/sensor-events', [SensorEventController::class, 'store']);
});
