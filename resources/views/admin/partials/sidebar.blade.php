<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
        <img class="img-fluid" src="{{ asset('static_media/logo.jpg') }}" alt="">
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Bolt kezelés
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSale" aria-expanded="false">
            <i class="fas fa-fw fa-circle"></i>
            <span>Értékesítés</span>
        </a>
        <div id="collapseSale" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.orders.index') }}">Rendelések</a>
                <a class="collapse-item" href="{{ route('admin.coupons.index') }}">Kuponok</a>
                <a class="collapse-item" href="{{ route('admin.customers.index') }}">Vevők és partnerek</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProducts" aria-expanded="false">
            <i class="fas fa-fw fa-circle"></i>
            <span>Termékek</span>
        </a>
        <div id="collapseProducts" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.products.index') }}">Összes termék</a>
                <a class="collapse-item" href="{{ route('admin.categories.index') }}">Kategóriák</a>
                <a class="collapse-item" href="{{ route('admin.attributes.index') }}">Egyedi tulajdonságok</a>
                <a class="collapse-item" href="{{ route('admin.tags.index') }}">Címkék</a>
                <a class="collapse-item" href="{{ route('admin.brands.index') }}">Gyártók</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Ügyvitel
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCustomerProcesses" aria-expanded="false">
            <i class="fas fa-fw fa-circle"></i>
            <span>Ügyfél folyamatok</span>
        </a>
        <div id="collapseCustomerProcesses" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">Ajánlatok</a>
                <a class="collapse-item" href="#">Szerződések</a>
                <a class="collapse-item" href="#">Időpontfoglalások</a>
                <a class="collapse-item" href="#">Munkalapok</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Beállítások
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseWebshopSettings" aria-expanded="false">
            <i class="fas fa-fw fa-circle"></i>
            <span>Webshop</span>
        </a>
        <div id="collapseWebshopSettings" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.settings.general.index') }}">Általános</a>
                <a class="collapse-item" href="#">Letöltések</a>
                <a class="collapse-item" href="#">Admin felhasználók</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseOrderSettings" aria-expanded="false">
            <i class="fas fa-fw fa-circle"></i>
            <span>Rendelés</span>
        </a>
        <div id="collapseOrderSettings" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">Szállítási módok</a>
                <a class="collapse-item" href="#">Fizetési módok</a>
                <a class="collapse-item" href="#">Raktári állapotok</a>
                <a class="collapse-item" href="#">Rendelési állapotok</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseFinancialSettings" aria-expanded="false">
            <i class="fas fa-fw fa-circle"></i>
            <span>Pénzügyi beállítások</span>
        </a>
        <div id="collapseFinancialSettings" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.tax-categories.index') }}">Adó osztályok</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSEOSettings" aria-expanded="false">
            <i class="fas fa-fw fa-circle"></i>
            <span>SEO és analítika</span>
        </a>
        <div id="collapseSEOSettings" class="collapse">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">SEO beállítások</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Jelentések
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseAnalytics" aria-expanded="false">
            <i class="fas fa-fw fa-circle"></i>
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
