<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/admin">
        <img class="img-fluid" src="{{ asset('static_media/logo.jpg') }}" alt="">
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item">
        <a class="nav-link" href="{{ route('index') }}" target="_blank">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            <span>Ugrás a boltra</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Vezérlőpult</span></a>
    </li>


    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Bolt kezelés
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSale" aria-expanded="false">
            <i class="fa-solid fa-money-bill-transfer"></i>
            <span>Értékesítés <span id="new_sale" class="badge badge-secondary ml-2 d-none">0</span></span></span>
        </a>
        <div id="collapseSale" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.orders.index') }}">Rendelések<span id="new_order_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.coupons.index') }}">Kuponok<span id="new_coupon_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.customers.index') }}">Vevők és partnerek<span id="new_customer_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProducts" aria-expanded="false">
            <i class="fa-solid fa-list"></i>
            <span>Termékek <span id="new_product" class="badge badge-secondary ml-2 d-none">0</span></span></span>
        </a>
        <div id="collapseProducts" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.products.index') }}">Összes termék<span id="new_product_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.categories.index') }}">Kategóriák<span id="new_product_category_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.attributes.index') }}">Egyedi tulajdonságok<span id="new_attribute_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.tags.index') }}">Címkék<span id="new_tag_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.brands.index') }}">Gyártók<span id="new_brand_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCMS" aria-expanded="false">
            <i class="fa-solid fa-file-lines"></i>
            <span>Tartalomkezelés <span id="new_product" class="badge badge-secondary ml-2 d-none">0</span></span></span>
        </a>
        <div id="collapseCMS" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.blog.index') }}">Blog bejegyzések</a>
                <a class="collapse-item" href="{{ route('admin.settings.downloads.index') }}">Letöltések</a>
                <a class="collapse-item" href="{{ route('admin.settings.regulations.index') }}">Szabályzatok</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Ügyvitel
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCustomerProcesses" aria-expanded="false">
            <i class="fa-solid fa-business-time"></i>
            <span>Ügyfél folyamatok <span id="new_business" class="badge badge-secondary ml-2 d-none">0</span></span>
        </a>
        <div id="collapseCustomerProcesses" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.offers.index') }}">Ajánlatok<span id="new_offer_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.contracts.index') }}">Szerződések<span id="new_contract_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.appointments.index') }}">Időpontfoglalások<span id="new_appointment_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.worksheets.index') }}">Munkalapok<span id="new_worksheet_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Beállítások
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseWebshopSettings" aria-expanded="false">
            <i class="fa-solid fa-gears"></i>
            <span>Webshop</span>
        </a>
        <div id="collapseWebshopSettings" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.shipping-methods.index') }}">Szállítási módok</a>
                <a class="collapse-item" href="{{ route('admin.payment-methods.index') }}">Fizetési módok</a>
                <a class="collapse-item" href="{{ route('admin.stock-statuses.index') }}">Raktári állapotok</a>
                <a class="collapse-item" href="{{ route('admin.order-statuses.index') }}">Rendelési állapotok</a>
                <a class="collapse-item" href="{{ route('admin.tax-categories.index') }}">Adó osztályok</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSystemSettings" aria-expanded="false">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            <span>Rendszer</span>
        </a>
        <div id="collapseSystemSettings" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.settings.general.index') }}">Általános</a>
                <a class="collapse-item" href="{{ route('admin.settings.users.index') }}">Felhasználók</a>
                <a class="collapse-item" href="{{ route('admin.settings.sites.index') }}">Telephelyek</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Jelentések
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseAnalytics" aria-expanded="false">
            <i class="fa-solid fa-chart-simple"></i>
            <span>Aktivitás</span>
        </a>
        <div id="collapseAnalytics" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">Megnézett termékek</a>
                <a class="collapse-item" href="#">Vásárolt termékek</a>
                <a class="collapse-item" href="#">Keresések</a>
                <a class="collapse-item" href="#">Admin tevékenységek</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
