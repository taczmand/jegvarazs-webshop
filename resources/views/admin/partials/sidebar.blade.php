<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-custom sidebar sidebar-dark accordion sidebar-rounded" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="d-flex align-items-center justify-content-center" href="{{ route('index') }}" target="_blank" style="background-color: #f8f9fc; border-radius: 1rem; margin: 0.25rem">
        <img class="" src="{{ asset('storage/' . $basicmedia['default_logo']) }}" alt="" style="max-width: 100%">
    </a>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProfil" aria-expanded="false">
            <i class="fa-solid fa-user"></i>
            <span>{{ optional(Auth::guard('admin')->user())->name }}</span>
        </a>
        <div id="collapseProfil" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ url('admin/profil') }}">Profil</a>
                <a class="collapse-item" href="{{ url('admin/kijelentkezes') }}">Kijelentkezés</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider my-0">

    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-calendar"></i>
            <span>Naptár</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Bolt kezelés
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSale" aria-expanded="false">
            <i class="fa-solid fa-money-bill-transfer"></i>
            <span>Értékesítés <span id="new_sale" class="badge badge-secondary ml-2 d-none">0</span></span>
        </a>
        <div id="collapseSale" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.orders.index') }}">Rendelések<span id="new_order_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.customers.index') }}">Vevők és partnerek<span id="new_customer_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProducts" aria-expanded="false">
            <i class="fa-solid fa-list"></i>
            <span>Termékek <span id="new_product" class="badge badge-secondary ml-2 d-none">0</span></span>
        </a>
        <div id="collapseProducts" class="collapse" data-bs-parent="#accordionSidebar">
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
            <span>Tartalomkezelés</span>
        </a>
        <div id="collapseCMS" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.blog.index') }}">Blog bejegyzések</a>
                <a class="collapse-item" href="{{ route('admin.settings.downloads.index') }}">Letöltések</a>
                <a class="collapse-item" href="{{ route('admin.settings.regulations.index') }}">Szabályzatok</a>
                <a class="collapse-item" href="{{ route('admin.settings.sites.index') }}">Telephelyek</a>
                <a class="collapse-item" href="{{ route('admin.settings.employees.index') }}">Munkatársak</a>
                <a class="collapse-item" href="{{ route('admin.settings.media.index') }}">Média</a>
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
            <span>Ügyviteli folyamatok <span id="new_business" class="badge badge-secondary ml-2 d-none">0</span></span>
        </a>
        <div id="collapseCustomerProcesses" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.offers.index') }}">Ajánlatok<span id="new_offer_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.contracts.index') }}">Szerződések<span id="new_contract_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.appointments.index') }}">Időpontfoglalások<span id="new_appointment_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.worksheets.index') }}">Munkalapok<span id="new_worksheet_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.leads.index') }}">Érdeklődők<span id="new_lead_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                <a class="collapse-item" href="{{ route('admin.clients.index') }}">Ügyfelek</a>
                <a class="collapse-item" href="{{ route('admin.automated-emails.index') }}">E-mail automatizáció</a>
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
        <div id="collapseWebshopSettings" class="collapse" data-bs-parent="#accordionSidebar">
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
        <div id="collapseSystemSettings" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.settings.general.index') }}">Általános</a>
                <a class="collapse-item" href="{{ route('admin.settings.users.index') }}">Felhasználók</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Jelentések
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseWebshopAnalytics" aria-expanded="false">
            <i class="fa-solid fa-chart-simple"></i>
            <span>Webshop</span>
        </a>
        <div id="collapseWebshopAnalytics" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.stats.watched_products') }}">Megtekintett termékek</a>
                <a class="collapse-item" href="{{ route('admin.stats.purchased_products') }}">Vásárolt termékek</a>
                <a class="collapse-item" href="{{ route('admin.stats.searched_products') }}">Keresések</a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCRMAnalytics" aria-expanded="false">
            <i class="fa-solid fa-user-group"></i>
            <span>Ügyviteli</span>
        </a>
        <div id="collapseCRMAnalytics" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.stats.contacts') }}">Kapcsolatok</a>
                <a class="collapse-item" href="{{ route('admin.stats.installations') }}">Szerelések</a>
            </div>
        </div>
    </li>

    @if(auth('admin')->user() && auth('admin')->user()->can('view-sensor-reports'))
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSensorAnalytics" aria-expanded="false">
                <i class="fa-solid fa-microchip"></i>
                <span>Szenzorok</span>
            </a>
            <div id="collapseSensorAnalytics" class="collapse" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('admin.stats.sensors') }}">Összes eszköz</a>
                    @if(isset($sensorDeviceIds) && count($sensorDeviceIds) > 0)
                        @foreach($sensorDeviceIds as $deviceId)
                            <a class="collapse-item" href="{{ route('admin.stats.sensors.device', ['deviceId' => $deviceId]) }}">{{ $deviceId }}</a>
                        @endforeach
                    @endif
                </div>
            </div>
        </li>
    @endif

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSystemAnalytics" aria-expanded="false">
            <i class="fa-solid fa-gear"></i>
            <span>Általános</span>
        </a>
        <div id="collapseSystemAnalytics" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('admin.stats.admin_logs') }}">Admin tevékenységek</a>
                @if(auth('admin')->user() && auth('admin')->user()->can('view-admin-logs'))
                    <a class="collapse-item" href="{{ route('admin.stats.laravel_logs') }}">Laravel logok</a>
                @endif
            </div>
        </div>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
