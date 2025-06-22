<?php

use App\Http\Controllers\PostalCodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/postal-codes/search', [PostalCodeController::class, 'searchPostalCodes']);
