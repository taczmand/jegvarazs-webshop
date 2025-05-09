<?php

use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\CustomerLoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesController;

Route::get('/', [PagesController::class, 'index'])->name('index');
Route::get('/about', [PagesController::class, 'about'])->name('about');

Route::get('/bejelentkezes', [CustomerLoginController::class, 'showLoginForm'])->name('login');
Route::post('/bejelentkezes', [CustomerLoginController::class, 'login']);
Route::middleware(['auth:customer'])->group(function () {
    //Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
});

// Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/bejelentkezes', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/bejelentkezes', [AdminLoginController::class, 'login']);
    Route::get('/kijelentkezes', [AdminLoginController::class, 'logout']);

    Route::middleware(['admin.auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
});


