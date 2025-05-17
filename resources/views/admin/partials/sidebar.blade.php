<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
        <img class="img-fluid" src="{{ asset('static_media/logo.jpg') }}" alt="">
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item">
        <a class="nav-link" href="/admin/dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading - Bolt kezelés -->
    <div class="sidebar-heading">
        Bolt kezelés
    </div>

    <!-- Nav Item - Értékesítés -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSale"
           aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-circle"></i>
            <span>Értékesítés</span>
        </a>
        <div id="collapseSale" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">Rendelések</a>
                <a class="collapse-item" href="#">Kuponok</a>
                <a class="collapse-item" href="#">Vevők és partnerek</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Termékek -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseProducts"
           aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-circle"></i>
            <span>Termékek</span>
        </a>
        <div id="collapseProducts" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">Összes termék</a>
                <a class="collapse-item" href="#">Kategóriák</a>
                <a class="collapse-item" href="#">Egyedi tulajdonságok</a>
                <a class="collapse-item" href="#">Címkék</a>
                <a class="collapse-item" href="#">Gyártók</a>
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading - Ügyvitel -->
    <div class="sidebar-heading">
        Ügyvitel
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCustomerProcesses"
           aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-circle"></i>
            <span>Ügyfél folyamatok</span>
        </a>
        <div id="collapseCustomerProcesses" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">Ajánlatok</a>
                <a class="collapse-item" href="#">Szerződések</a>
                <a class="collapse-item" href="#">Időpontfoglalások</a>
                <a class="collapse-item" href="#">Munkalapok</a>
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading - Beállítások -->
    <div class="sidebar-heading">
        Beállítások
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseWebshopSettings"
           aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-circle"></i>
            <span>Webshop</span>
        </a>
        <div id="collapseWebshopSettings" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">Általános</a>
                <a class="collapse-item" href="#">Letöltések</a>
                <a class="collapse-item" href="#">Admin felhasználók</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseOrderSettings"
           aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-circle"></i>
            <span>Rendelés</span>
        </a>
        <div id="collapseOrderSettings" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">Szállítási módok</a>
                <a class="collapse-item" href="#">Fizetési módok</a>
                <a class="collapse-item" href="#">Raktári állapotok</a>
                <a class="collapse-item" href="#">Rendelési állapotok</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFinancialSettings"
           aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-circle"></i>
            <span>Pénzügyi beállítások</span>
        </a>
        <div id="collapseFinancialSettings" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="/admin/beallitasok/ado-osztalyok">Adó osztályok</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSEOSettings"
           aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-circle"></i>
            <span>SEO és analítika</span>
        </a>
        <div id="collapseSEOSettings" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">SEO beállítások</a>
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading - Jelentések -->
    <div class="sidebar-heading">
        Jelentések
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAnalytics"
           aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-circle"></i>
            <span>Aktivitás</span>
        </a>
        <div id="collapseAnalytics" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="#">Megnézett termékek</a>
                <a class="collapse-item" href="#">Vásárolt termékek</a>
                <a class="collapse-item" href="#">Keresések</a>
                <a class="collapse-item" href="#">Admin tevékenységek</a>
            </div>
        </div>
    </li>


    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
