<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\BasicDataController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanySiteController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DownloadsController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\OrderController As AdminOrderController;
use App\Http\Controllers\Admin\OrderStatusesController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\RegulationController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\StockStatusesController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TaxCategoryController;
use App\Http\Controllers\Admin\WorksheetController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShopCustomerController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ProductController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

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
        Route::get('/ertekesites/rendelesek', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/ertekesites/rendelesek/data', [AdminOrderController::class, 'data'])->name('orders.data');
        Route::get('/ertekesites/rendeles/{id}', [AdminOrderController::class, 'show'])->name('orders.order');
        Route::get('/ertekesites/rendeles/{id}/items', [AdminOrderController::class, 'items'])->name('orders.items');
        Route::get('/ertekesites/rendeles/{id}/history', [AdminOrderController::class, 'history'])->name('orders.history');
        //Route::post('/ertekesites/rendelesek', [AdminOrderController::class, 'store'])->name('orders.store');
        Route::put('/ertekesites/rendelesek/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
        Route::delete('/ertekesites/rendelesek/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');

        // Kuponok
        Route::get('/ertekesites/kuponok', [CouponController::class, 'index'])->name('coupons.index');
        Route::get('/ertekesites/kuponok/data', [CouponController::class, 'data'])->name('coupons.data');
        Route::post('/ertekesites/kuponok', [CouponController::class, 'store'])->name('coupons.store');
        Route::put('/ertekesites/kuponok/{order}', [CouponController::class, 'update'])->name('coupons.update');
        Route::delete('/ertekesites/kuponok/{order}', [CouponController::class, 'destroy'])->name('coupons.destroy');

        // Vevők és partnerek
        Route::get('/ertekesites/vevok', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/ertekesites/vevok/data', [CustomerController::class, 'data'])->name('customers.data');
        Route::get('/ertekesites/vevo/{id}', [CustomerController::class, 'show'])->name('customers.customer');
        Route::post('/ertekesites/vevo', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/ertekesites/vevok/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::delete('/ertekesites/vevo/kosar/torol/{id}', [CustomerController::class, 'deleteCartItem'])->name('customers.cart.delete_item');
        Route::post('/ertekesites/vevo/szallitasi-cim', [CustomerController::class, 'updateShippingAddress'])->name('customers.update_shipping_address');
        Route::post('/ertekesites/vevo/szamlazasi-cim', [CustomerController::class, 'updateBillingAddress'])->name('customers.update_billing_address');
        Route::delete('/ertekesites/vevo/szallitasi-cim/torol', [CustomerController::class, 'destroyShippingAddress'])->name('customers.destroy_shipping_address');
        Route::delete('/ertekesites/vevo/szamlazasi-cim/torol', [CustomerController::class, 'destroyBillingAddress'])->name('customers.destroy_billing_address');

        Route::get('/ertekesites/partner/arazo/{id}', [CustomerController::class, 'showProductsToPartner'])->name('customers.show_products_to_partner');
        Route::post('/ertekesites/partner/arazo', [CustomerController::class, 'setProductPriceToPartner'])->name('customers.set_product_price_to_partner');
        Route::post('/ertekesites/partner/szazalek', [CustomerController::class, 'setProductPricePercentToPartner'])->name('customers.set_product_price_percent_to_partner');
        Route::delete('/ertekesites/partner/arazo', [CustomerController::class, 'destroyProductPriceToPartner'])->name('customers.destroy_product_price_to_partner');
        Route::delete('/ertekesites/partner/arazo/torol', [CustomerController::class, 'destroyAllProductPriceToPartner'])->name('customers.destroy_all_product_price_to_partner');


        /* Bolt kezelés - Termékek */

        // Összes termék
        Route::get('/termekek', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/termekek/kategoriakkal', [AdminProductController::class, 'fetchWithCategories'])->name('products.list-with-categories');
        Route::get('/termekek/data', [AdminProductController::class, 'data'])->name('products.data');
        Route::get('/termekek/meta', [AdminProductController::class, 'meta'])->name('products.meta');
        Route::get('/termekek/{id}', [AdminProductController::class, 'show'])->name('products.get');
        Route::post('/termekek', [AdminProductController::class, 'store'])->name('products.store');
        Route::put('/termekek/{id}', [AdminProductController::class, 'update'])->name('products.update');
        Route::patch('/termekek/update-photo-alt', [AdminProductController::class, 'updateProductPhotoAlt'])->name('products.update_product_photo_alt');
        Route::patch('/termekek/set-primary-photo', [AdminProductController::class, 'setPrimaryProductPhoto'])->name('products.set_primary_product_photo');
        Route::delete('/termekek/delete-photo', [AdminProductController::class, 'deleteProductPhoto'])->name('products.delete_product_photo');
        Route::delete('/termekek/{id}', [AdminProductController::class, 'destroy'])->name('products.destroy');

        // Kategóriák
        Route::get('/kategoriak', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/kategoriak/data', [CategoryController::class, 'data'])->name('categories.data');
        Route::get('/kategoriak/fetch', [CategoryController::class, 'fetch'])->name('categories.fetch');
        Route::post('/kategoriak', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/kategoriak/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/kategoriak/{id}', [CategoryController::class, 'destroy'])->name('products.destroy');

        // Egyedi tulajdonságok
        Route::get('/tulajdonsagok', [AttributeController::class, 'index'])->name('attributes.index');
        Route::get('/tulajdonsagok/data', [AttributeController::class, 'data'])->name('attributes.data');
        Route::post('/tulajdonsagok', [AttributeController::class, 'store'])->name('attributes.store');
        Route::put('tulajdonsagok/{id}', [AttributeController::class, 'update'])->name('attributes.update');
        Route::delete('/tulajdonsagok/{id}', [AttributeController::class, 'destroy'])->name('attributes.destroy');

        // Címkék
        Route::get('/cimkek', [TagController::class, 'index'])->name('tags.index');
        Route::get('/cimkek/data', [TagController::class, 'data'])->name('tags.data');
        Route::post('/cimkek', [TagController::class, 'store'])->name('tags.store');
        Route::put('cimkek/{id}', [TagController::class, 'update'])->name('tags.update');
        Route::delete('/cimkek/{id}', [TagController::class, 'destroy'])->name('tags.destroy');

        // Gyártók
        Route::get('/gyartok', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/gyartok/data', [BrandController::class, 'data'])->name('brands.data');
        Route::post('/gyartok', [BrandController::class, 'store'])->name('brands.store');
        Route::put('gyartok/{id}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/gyartok/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');

        /* Ügyvitel - Ügyfél folyamatok */

        // Ajánlatok
        Route::get('/ajanlatok', [OfferController::class, 'index'])->name('offers.index');
        Route::get('/ajanlatok/data', [OfferController::class, 'data'])->name('offers.data');
        Route::get('/ajanlatok/termekek/{id}', [OfferController::class, 'showProductsToOffer'])->name('offers.show_products_to_offer');
        Route::post('/ajanlatok', [OfferController::class, 'store'])->name('offers.store');
        Route::delete('/ajanlatok/{id}', [OfferController::class, 'destroy'])->name('offers.destroy');

        // Szerződések
        Route::get('/szerzodesek', [ContractController::class, 'index'])->name('contracts.index');
        Route::get('/szerzodesek/data', [ContractController::class, 'data'])->name('contracts.data');
        Route::get('/szerzodesek/verzio/{id}', [ContractController::class, 'getVersionJson'])->name('contracts.version.json');
        //Route::get('/contracts/create', [ContractController::class, 'create'])->name('contracts.create');
        Route::post('/szerzodesek', [ContractController::class, 'store'])->name('contracts.store');
        Route::get('/szerzodesek/v1', function () {
            return view('pdf.contract_v1');
        })->name('contracts.create_v1');

        // Munkalapok
        Route::get('/munkalapok', [WorksheetController::class, 'index'])->name('worksheets.index');

        /* Beállítások - Webshop */

        // Általános
        Route::get('/beallitasok/altalanos', [BasicDataController::class, 'index'])->name('settings.general.index');
        Route::get('/beallitasok/altalanos/data', [BasicDataController::class, 'data'])->name('settings.general.data');
        Route::put('/beallitasok/altalanos/{id}', [BasicDataController::class, 'update'])->name('settings.general.update');

        // Letöltések

        Route::get('/letoltesek', [DownloadsController::class, 'index'])->name('settings.downloads.index');
        Route::get('/letoltesek/data', [DownloadsController::class, 'data'])->name('settings.downloads.data');
        Route::post('/letoltesek', [DownloadsController::class, 'store'])->name('settings.downloads.store');
        Route::put('/letoltesek/{id}', [DownloadsController::class, 'update'])->name('settings.downloads.update');
        Route::delete('/letoltesek/{id}', [DownloadsController::class, 'destroy'])->name('settings.downloads.destroy');

        // Szabályzatok

        Route::get('/szabalyzatok', [RegulationController::class, 'index'])->name('settings.regulations.index');
        Route::get('/szabalyzatok/data', [RegulationController::class, 'data'])->name('settings.regulations.data');
        Route::post('/szabalyzatok', [RegulationController::class, 'store'])->name('settings.regulations.store');
        Route::put('/szabalyzatok/{id}', [RegulationController::class, 'update'])->name('settings.regulations.update');
        Route::delete('/szabalyzatok/{id}', [RegulationController::class, 'destroy'])->name('settings.regulations.destroy');

        // Telephelyek

        Route::get('/telephelyek', [CompanySiteController::class, 'index'])->name('settings.sites.index');
        Route::get('/telephelyek/data', [CompanySiteController::class, 'data'])->name('settings.sites.data');
        Route::post('/telephelyek', [CompanySiteController::class, 'store'])->name('settings.sites.store');
        Route::put('/telephelyek/{id}', [CompanySiteController::class, 'update'])->name('settings.sites.update');
        Route::delete('/telephelyek/{id}', [CompanySiteController::class, 'destroy'])->name('settings.sites.destroy');

        /* Beállítások - Rendelés */

        //Szállítási módok configból

        Route::get('/szallitasi-modok', [ShippingMethodController::class, 'index'])->name('shipping-methods.index');

        //Fizetési módok configból

        Route::get('/fizetesi-modok', [PaymentMethodController::class, 'index'])->name('payment-methods.index');

        //Raktári állapotok configból

        Route::get('/raktari-allapotok', [StockStatusesController::class, 'index'])->name('stock-statuses.index');

        //Rendelési állapotok configból

        Route::get('/rendelesi-allapotok', [OrderStatusesController::class, 'index'])->name('order-statuses.index');

        /* Beállítások - Pénzügyi beállítások */

        // Adó osztályok
        Route::get('/beallitasok/ado-osztalyok', [TaxCategoryController::class, 'index'])->name('tax-categories.index');
        Route::get('/beallitasok/ado-osztalyok/data', [TaxCategoryController::class, 'data'])->name('tax-categories.data');
        Route::post('/beallitasok/ado-osztalyok', [TaxCategoryController::class, 'store'])->name('tax-categories.store');
        Route::put('/beallitasok/ado-osztalyok/{tax}', [TaxCategoryController::class, 'update'])->name('tax-categories.update');
        Route::delete('/beallitasok/ado-osztalyok/{tax}', [TaxCategoryController::class, 'destroy'])->name('tax-categories.destroy');

        /* Fájlok elérése */

        // Ajánlatok PDF fájlok elérése
        Route::get('offers/{filename}', function ($filename) {
            $path = storage_path('app/private/offers/' . $filename);

            if (!file_exists($path)) {
                abort(404);
            }

            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]);
        })->name('offers.pdf');
    });
});

Route::get('/bejelentkezes', [ShopCustomerController::class, 'showLoginForm'])->name('login');
Route::post('/bejelentkezes', [ShopCustomerController::class, 'login']);
Route::get('/kijelentkezes', [ShopCustomerController::class, 'logout'])->name('logout');
Route::post('/elfelejtett-jelszo', [ShopCustomerController::class, 'passwordReset'])->name('password.reset');
Route::get('/regisztracio', [ShopCustomerController::class, 'showRegistrationForm'])->name('registration');
Route::post('/regisztracio', [ShopCustomerController::class, 'register']);
Route::view('/regisztracio/sikeres', 'pages.partner_reg_success')->name('customer.register.success');

Route::get('/', [PagesController::class, 'index'])->name('index');
Route::get('/rolunk', [PagesController::class, 'about'])->name('about');
Route::get('/kapcsolat', [PagesController::class, 'contact'])->name('contact');
Route::get('/idopontfoglalas', [PagesController::class, 'appointment'])->name('appointment');
Route::post('/idopontfoglalas', [PagesController::class, 'addAppointment'])->name('appointment.post');
Route::get('/letoltesek', [PagesController::class, 'downloads'])->name('downloads');

Route::middleware(['auth:customer'])->group(function () {
    // Kosár
    Route::get('/kosar', [CartController::class, 'index'])->name('cart');
    Route::post('/kosar/hozzaadas', [CartController::class, 'add']);
    Route::get('/kosar/osszesito', [CartController::class, 'fetchSummary'])->name('cart.summary');
    Route::post('/kosar/torles', [CartController::class, 'removeItemFromCart'])->name('cart.item.delete');
    Route::post('/kosar/mennyiseg-valtoztatas', [CartController::class, 'changeItemQty'])->name('cart.item.change_qty');

    // Pénztár
    Route::get('/penztar', [CheckoutController::class, 'index'])->name('checkout');

    // Rendelés
    Route::post('/order', [OrderController::class, 'store'])->name('order.store');
    Route::get('/order/success/{order}', function (Order $order) {
        return view('pages.order_success', compact('order'));
    })->name('order.success');

    // Fizetési módok
    Route::get('/simplepay/redirect/{order}', function (Order $order) {
        // Itt jönne a SimplePay redirect logika
        return "Redirecting to SimplePay for order #" . $order->id;
    })->name('simplepay.redirect');
});



Route::get('/termekek', [ProductController::class, 'index'])->name('products.index');
Route::get('/termekek/{slugs}', [ProductController::class, 'resolve'])
    ->where('slugs', '^(?!admin).*$') // ne kezdődjön admin-nal
    ->name('products.resolve');







