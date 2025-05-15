<?php

use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Settings\TaxCategoryController;
use App\Http\Controllers\Auth\CustomerLoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesController;

Route::get('/', [PagesController::class, 'index'])->name('index');
Route::get('/rolunk', [PagesController::class, 'about'])->name('about');
Route::get('/kapcsolat', [PagesController::class, 'contact'])->name('contact');
Route::get('/idoponfoglalas', [PagesController::class, 'appointment'])->name('appointment');
Route::get('/letoltesek', [PagesController::class, 'downloads'])->name('downloads');

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
        Route::get('/beallitasok/ado-osztalyok', [TaxCategoryController::class, 'index'])->name('tax-categories.index');
        Route::get('/beallitasok/ado-osztalyok/data', [TaxCategoryController::class, 'data'])->name('tax-categories.data');
    });
});


