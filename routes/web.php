<?php

use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TaxCategoryController;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\PagesController;
use Illuminate\Support\Facades\Route;

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

        /* Dashboard */

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        /* Bolt kezelés - Értékesítés */

        // Rendelések
        Route::get('/ertekesites/rendelesek', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/ertekesites/rendelesek/data', [OrderController::class, 'data'])->name('orders.data');
        Route::post('/ertekesites/rendelesek', [OrderController::class, 'store'])->name('orders.store');
        Route::put('/ertekesites/rendelesek/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::delete('/ertekesites/rendelesek/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

        // Kuponok
        Route::get('/ertekesites/kuponok', [CouponController::class, 'index'])->name('coupons.index');
        Route::get('/ertekesites/kuponok/data', [CouponController::class, 'data'])->name('coupons.data');
        Route::post('/ertekesites/kuponok', [CouponController::class, 'store'])->name('coupons.store');
        Route::put('/ertekesites/kuponok/{order}', [CouponController::class, 'update'])->name('coupons.update');
        Route::delete('/ertekesites/kuponok/{order}', [CouponController::class, 'destroy'])->name('coupons.destroy');

        // Vevők és partnerek
        Route::get('/ertekesites/vevok', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/ertekesites/vevok/data', [CustomerController::class, 'data'])->name('customers.data');
        Route::post('/ertekesites/vevok', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/ertekesites/vevok/{order}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/ertekesites/vevok/{order}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        /* Bolt kezelés - Termékek */

        // Összes termék
        Route::get('/termekek', [ProductController::class, 'index'])->name('products.index');
        Route::get('/termekek/data', [ProductController::class, 'data'])->name('products.data');
        Route::get('/termekek/meta', [ProductController::class, 'meta'])->name('products.meta');
        Route::get('/termekek/{id}', [ProductController::class, 'show'])->name('products.get');
        Route::post('/termekek', [ProductController::class, 'store'])->name('products.store');
        Route::put('/termekek/{id}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/termekek/update-photo-alt', [ProductController::class, 'updateProductPhotoAlt'])->name('products.update_product_photo_alt');
        Route::patch('/termekek/set-primary-photo', [ProductController::class, 'setPrimaryProductPhoto'])->name('products.set_primary_product_photo');
        Route::delete('/termekek/delete-photo', [ProductController::class, 'deleteProductPhoto'])->name('products.delete_product_photo');
        Route::delete('/termekek/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Kategóriák
        Route::get('/kategoriak', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/kategoriak/data', [CategoryController::class, 'data'])->name('categories.data');

        /* Beállítások - Pénzügyi beállítások */

        // Adó osztályok
        Route::get('/beallitasok/ado-osztalyok', [TaxCategoryController::class, 'index'])->name('tax-categories.index');
        Route::get('/beallitasok/ado-osztalyok/data', [TaxCategoryController::class, 'data'])->name('tax-categories.data');
        Route::post('/beallitasok/ado-osztalyok', [TaxCategoryController::class, 'store'])->name('tax-categories.store');
        Route::put('/beallitasok/ado-osztalyok/{tax}', [TaxCategoryController::class, 'update'])->name('tax-categories.update');
        Route::delete('/beallitasok/ado-osztalyok/{tax}', [TaxCategoryController::class, 'destroy'])->name('tax-categories.destroy');
    });
});


