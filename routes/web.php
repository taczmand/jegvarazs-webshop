<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\BasicDataController;
use App\Http\Controllers\Admin\BasicMediaController;
use App\Http\Controllers\Admin\BlogsController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanySiteController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DownloadsController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrderStatusesController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\RegulationController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\StatController;
use App\Http\Controllers\Admin\StockStatusesController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TaxCategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorksheetController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopCustomerController;
use App\Http\Controllers\SimplePayController;
use App\Http\Middleware\Incognito;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/incognito', [PagesController::class, 'incognito']);

Route::get('/idopontfoglalas', [PagesController::class, 'appointment'])->name('appointment');

Route::get('/reset-carts', [CartController::class, 'resetCarts']);

Route::middleware([Incognito::class])->group(function () {

    // Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/bejelentkezes', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/bejelentkezes', [AdminLoginController::class, 'login']);
        Route::get('/kijelentkezes', [AdminLoginController::class, 'logout']);

        Route::middleware(['admin.auth'])->group(function () {
            Route::get('contract/{id}/pdf', [ContractController::class, 'getPdf'])->name('contract.pdf');
            /* Dashboard */

            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/profil', [UserController::class, 'profil'])->name('profile');


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
            Route::delete('/ertekesites/vevo/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

            Route::get('/ertekesites/partner/arazo/{id}/', [CustomerController::class, 'showProductsToPartner'])->name('customers.show_products_to_partner');
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
            Route::post('/termekek/{id}/upload-photos', [AdminProductController::class, 'uploadProductPhotos'])->name('products.upload_product_photo');

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

            // Statisztika
            Route::get('/statisztika/keresett-termekek', [StatController::class, 'searchedProducts'])->name('stats.searched_products');
            Route::get('/statisztika/keresett-termekek/data', [StatController::class, 'searchedProductsData'])->name('stats.searched_products.data');
            Route::get('/statisztika/megnezett-termekek', [StatController::class, 'watchedProducts'])->name('stats.watched_products');
            Route::get('/statisztika/megnezett-termekek/data', [StatController::class, 'watchedProductsData'])->name('stats.watched_products.data');
            Route::get('/statisztika/admin-tevekenysegek', [StatController::class, 'adminLogs'])->name('stats.admin_logs');
            Route::get('/statisztika/admin-tevekenysegek/data', [StatController::class, 'adminLogsData'])->name('stats.admin_logs.data');
            Route::get('/statisztika/vasarolt-termekek', [StatController::class, 'purchasedProducts'])->name('stats.purchased_products');
            Route::get('/statisztika/vasarolt-termekek/data', [StatController::class, 'purchasedProductsData'])->name('stats.purchased_products.data');


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
            Route::get('/ajanlatok/ajanlat-termekek', [OfferController::class, 'fetchWithCategories'])->name('offers.list-with-categories');

            // Szerződések
            Route::get('/szerzodesek', [ContractController::class, 'index'])->name('contracts.index');
            Route::get('/szerzodesek/data', [ContractController::class, 'data'])->name('contracts.data');
            Route::get('/szerzodesek/verzio/{id}', [ContractController::class, 'getVersionJson'])->name('contracts.version.json');
            //Route::get('/contracts/create', [ContractController::class, 'create'])->name('contracts.create');
            Route::post('/szerzodesek', [ContractController::class, 'store'])->name('contracts.store');
            Route::get('/szerzodesek/v1', function () {
                return view('pdf.contract_v1');
            })->name('contracts.create_v1');
            Route::get('/szerzodesek/szerzodes-termekek', [ContractController::class, 'fetchWithCategories'])->name('contracts.list-with-categories');
            Route::get('/szerzodesek/termekek/{id}', [ContractController::class, 'showProductsToContract'])->name('contracts.show_products_to_contracts');
            Route::delete('/szerzodesek/{id}', [ContractController::class, 'destroy'])->name('contracts.destroy');

            // Munkalapok
            Route::get('/munkalapok', [WorksheetController::class, 'index'])->name('worksheets.index');
            Route::get('/munkalapok/data', [WorksheetController::class, 'data'])->name('worksheets.data');
            Route::get('/munkalapok/byweek', [WorksheetController::class, 'getDataToCalendarByWeek'])->name('worksheets.byweek'); // nem csak worksheets
            Route::post('/munkalapok/update-orderdate', [WorksheetController::class, 'updateItemDateAndOrder'])->name('worksheets.update.order-date'); // nem csak worksheets
            Route::put('/munkalapok/{id}', [WorksheetController::class, 'update'])->name('worksheets.update');
            Route::post('/munkalapok', [WorksheetController::class, 'store'])->name('worksheet.store');
            Route::get('/munkalapok/munkalap-termekek', [WorksheetController::class, 'fetchWithCategories'])->name('worksheets.list-with-categories');
            Route::get('/munkalapok/adatok/{id}', [WorksheetController::class, 'showDataToWorksheet'])->name('worksheets.show_data_to_worksheet');
            Route::delete('/munkalapok/delete-photo', [WorksheetController::class, 'deleteWorksheetPhoto'])->name('worksheets.delete-photo');
            Route::delete('/munkalap-torlese/{id}', [WorksheetController::class, 'destroy'])->name('worksheets.destroy');

            // Időpontfoglalások
            Route::get('/idopontfoglalasok', [AppointmentController::class, 'index'])->name('appointments.index');
            Route::get('/idopontfoglalasok/data', [AppointmentController::class, 'data'])->name('appointments.data');
            Route::post('/idopontfoglalasok', [AppointmentController::class, 'store'])->name('appointments.store');
            Route::put('/idopontfoglalasok/{id}', [AppointmentController::class, 'update'])->name('appointments.update');
            Route::get('/idopontfoglalasok/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
            Route::delete('/idopontfoglalasok/delete-photo', [AppointmentController::class, 'deleteAppointmentPhoto'])->name('appointments.delete_appointment_photo');
            Route::delete('/idopontfoglalasok/{id}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');


            /* Beállítások - Webshop */

            // Általános
            Route::get('/beallitasok/altalanos', [BasicDataController::class, 'index'])->name('settings.general.index');
            Route::get('/beallitasok/altalanos/data', [BasicDataController::class, 'data'])->name('settings.general.data');
            Route::put('/beallitasok/altalanos/{id}', [BasicDataController::class, 'update'])->name('settings.general.update');

            // Média
            Route::get('/beallitasok/media', [BasicMediaController::class, 'index'])->name('settings.media.index');
            Route::get('/beallitasok/media/data', [BasicMediaController::class, 'data'])->name('settings.media.data');
            Route::post('/beallitasok/media', [BasicMediaController::class, 'store'])->name('settings.media.store');
            Route::put('/beallitasok/media/{id}', [BasicMediaController::class, 'update'])->name('settings.media.update');
            Route::delete('/beallitasok/media/{id}', [BasicMediaController::class, 'destroy'])->name('settings.media.destroy');

            // Rendszer - Felhasználók
            Route::get('/fetch-felhasznalok', [UserController::class, 'fetchUsers'])->name('settings.users.fetch');
            Route::get('/felhasznalok', [UserController::class, 'index'])->name('settings.users.index');
            Route::get('/felhasznalo/{id}', [UserController::class, 'fetchWithPermissions'])->name('settings.users.fetch');
            Route::get('/felhasznalok/data', [UserController::class, 'data'])->name('settings.users.data');
            Route::get('/felhasznalok/roles', [UserController::class, 'getRoles'])->name('settings.users.roles');
            Route::get('/felhasznalok/permissions', [UserController::class, 'getPermissions'])->name('settings.users.permissions');
            Route::post('/felhasznalok', [UserController::class, 'store'])->name('settings.users.store');
            Route::put('/felhasznalok/{id}', [UserController::class, 'update'])->name('settings.users.update');
            Route::delete('/felhasznalok/{id}', [UserController::class, 'destroy'])->name('settings.users.destroy');

            // Blog
            Route::get('/blog', [BlogsController::class, 'index'])->name('blog.index');
            Route::get('/blog/data', [BlogsController::class, 'data'])->name('blog.data');
            Route::get('/blog/fetch/{id}', [BlogsController::class, 'fetch'])->name('blog.fetch');
            Route::post('/blog', [BlogsController::class, 'store'])->name('blog.store');
            Route::put('/blog/{id}', [BlogsController::class, 'update'])->name('blog.update');
            Route::delete('/blog/delete-photo', [BlogsController::class, 'deleteBlogPhoto'])->name('blog.delete-photo');
            Route::delete('/blog/{id}', [BlogsController::class, 'destroy'])->name('blog.destroy');


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

            // Munkatársak

            Route::get('/munkatarsak', [EmployeeController::class, 'index'])->name('settings.employees.index');
            Route::get('/munkatarsak/data', [EmployeeController::class, 'data'])->name('settings.employees.data');
            Route::post('/munkatarsak', [EmployeeController::class, 'store'])->name('settings.employees.store');
            Route::put('/munkatarsak/{id}', [EmployeeController::class, 'update'])->name('settings.employees.update');
            Route::delete('/munkatarsak/{id}', [EmployeeController::class, 'destroy'])->name('settings.employees.destroy');


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

            // A még meg nem tekintett rekordok lekérése
            Route::get('/beallitasok/uj-adatok', [BasicDataController::class, 'getNewRecords'])->name('settings.new_records');
            Route::post('/beallitasok/uj-adatok/megtekintes', [BasicDataController::class, 'markAsViewed'])->name('settings.mark_as_viewed');

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
            })->name('offer.pdf');

            // Szerződések PDF fájlok elérése
            Route::get('contracts/{filename}', function ($filename) {
                $path = storage_path('app/private/contracts/' . $filename);

                if (!file_exists($path)) {
                    abort(404);
                }

                return response()->file($path, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"'
                ]);
            })->name('contract.pdf');

            // Aláírások
            Route::get('/szerzodes/alairas/{filename}', function ($filename) {
                $path = storage_path("app/private/signatures/{$filename}");

                if (!file_exists($path)) {
                    abort(404);
                }

                return response()->file($path);
            })->name('contract.signature');

            // Munkalapképek
            Route::get('/worksheets/{filename}', function ($filename) {
                $path = storage_path("app/private/worksheet_images/{$filename}");

                if (!file_exists($path)) {
                    abort(404);
                }

                return response()->file($path);
            })->name('worksheets.image');

            // Időpontfoglalás csatolmányok
            Route::get('/appointment-photos/{filename}', function ($filename) {
                $path = storage_path("app/private/appointment_images/{$filename}");

                if (!file_exists($path)) {
                    abort(404);
                }

                return response()->file($path);
            })->name('appointments.image');
        });
    });

    Route::get('/bejelentkezes', [ShopCustomerController::class, 'showLoginForm'])->name('login');
    Route::post('/bejelentkezes', [ShopCustomerController::class, 'login']);
    Route::get('/kijelentkezes', [ShopCustomerController::class, 'logout'])->name('logout');
    Route::post('/elfelejtett-jelszo', [ShopCustomerController::class, 'passwordReset'])->name('password.reset');
    Route::get('/regisztracio', [ShopCustomerController::class, 'showRegistrationForm'])->name('registration');
    Route::post('/regisztracio', [ShopCustomerController::class, 'register']);
    Route::view('/regisztracio/sikeres', 'pages.partner_reg_success')->name('customer.register.success');

    Route::get('/elfelejtett-jelszo', [ShopCustomerController::class, 'showPasswordRequestForm'])->name('password.request');

    // E-mail küldés
    Route::post('password/email', [ShopCustomerController::class, 'sendResetLinkEmail'])->name('password.email');

    // Új jelszó űrlap tokennel
    Route::get('elfelejtett-jelszo/{token}', [ShopCustomerController::class, 'showResetForm'])->name('password.reset');

    // Új jelszó mentése
    Route::post('elfelejtett-jelszo/reset', [ShopCustomerController::class, 'passwordReset'])->name('password.update');

    Route::get('/kereses', [PagesController::class, 'search'])->name('search');

    Route::get('/', [PagesController::class, 'index'])->name('index');
    Route::get('/rolunk', [PagesController::class, 'about'])->name('about');
    Route::get('/kapcsolat', [PagesController::class, 'contact'])->name('contact');

    Route::post('/idopontfoglalas', [PagesController::class, 'addAppointment'])->name('appointment.post');
    Route::get('/letoltesek', [PagesController::class, 'downloads'])->name('downloads');
    Route::get('/blog', [PagesController::class, 'blog'])->name('blog');
    Route::get('/blog/{slug}', [PagesController::class, 'blogPost'])->name('blog.post');

    // Új feliratkozás
    Route::post('/newsletter/add', [PagesController::class, 'newSubscription'])->name('new_subscription');

    // Írjon nekünk
    Route::post('/contact/add', [PagesController::class, 'newContactForm'])->name('new_contact_form');

    Route::middleware(['auth:customer'])->group(function () {
        // Kosár
        Route::get('/kosar', [CartController::class, 'index'])->name('cart');
        Route::post('/kosar/hozzaadas', [CartController::class, 'add']);
        Route::get('/kosar/osszesito', [CartController::class, 'fetchSummary'])->name('cart.summary');
        Route::post('/kosar/torles', [CartController::class, 'removeItemFromCart'])->name('cart.item.delete');
        Route::post('/kosar/mennyiseg-valtoztatas', [CartController::class, 'changeItemQty'])->name('cart.item.change_qty');

        // Rendelések
        Route::get('/rendelesek', [ShopCustomerController::class, 'orders'])->name('customer.orders');
        Route::get('/rendelesek/{id}', [ShopCustomerController::class, 'orderShow'])->name('customer.order.show');

        // Vásárló profilja
        Route::get('/profil', [ShopCustomerController::class, 'profile'])->name('customer.profile');
        Route::post('/profil', [ShopCustomerController::class, 'profileUpdate'])->name('customer.profile.update');

        // Fizetés újrapróbálása
        Route::get('/rendelesek/fizetes-ujraproba/{id}', [ShopCustomerController::class, 'retryPayment'])->name('customer.order.retry_payment');
        Route::post('/rendelesek/fizetes-ujraproba', [ShopCustomerController::class, 'processRetryPayment'])->name('customer.order.process_retry_payment');

        // Rendelés törlése
        Route::delete('/rendelesek/{id}', [ShopCustomerController::class, 'orderDestroy'])->name('customer.order.destroy');

        // E-mail küldés
        Route::post('/email/send', [ShopCustomerController::class, 'sendEmail'])->name('email.send');


        // Pénztár
        Route::get('/penztar', [CheckoutController::class, 'index'])->name('checkout');

        // Rendelés
        Route::post('/order', [OrderController::class, 'store'])->name('order.store');
        Route::get('/order/success/{order}', function (Order $order) {
            return view('pages.order_success', compact('order'));
        })->name('order.success');

    });



    Route::get('/termekek', [ProductController::class, 'index'])->name('products.index');
    Route::get('/termekek/{slugs}', [ProductController::class, 'resolve'])
        ->where('slugs', '^(?!admin).*$') // ne kezdődjön admin-nal
        ->name('products.resolve');
});

// SimplePay hívja, amikor a fizetés megtörtént (rendelés mentése)
Route::post('/payment/simplepay/callback', [SimplePayController::class, 'callback'])
    ->name('simplepay.callback');

// SimplePay visszatérési oldal (ez már a felhasználó böngészőjében történik)
Route::get('/payment/simplepay/return', [SimplePayController::class, 'return'])->name('simplepay.return');

// SimplePay adattovábbítási nyilatkozat
Route::get('/payment/simplepay/adattovabbitasi-nyilatkozat', [SimplePayController::class, 'adattovabbitasi_nyilatkozat'])->name('simplepay.adattovabbitasi_nyilatkozat');










