<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-custom sidebar sidebar-dark accordion sidebar-rounded" id="accordionSidebar">

    @php
        $adminUser = auth('admin')->user();

        $canViewOrders = (bool) ($adminUser && $adminUser->can('view-orders'));
        $canViewCustomers = (bool) ($adminUser && $adminUser->can('view-customers'));
        $canViewSales = $canViewOrders || $canViewCustomers;

        $canViewProducts = (bool) ($adminUser && $adminUser->can('view-products'));
        $canViewCategories = (bool) ($adminUser && $adminUser->can('view-categories'));
        $canViewAttributes = (bool) ($adminUser && $adminUser->can('view-attributes'));
        $canViewTags = (bool) ($adminUser && $adminUser->can('view-tags'));
        $canViewBrands = (bool) ($adminUser && $adminUser->can('view-brands'));
        $canViewProductSection = $canViewProducts || $canViewCategories || $canViewAttributes || $canViewTags || $canViewBrands;

        $canViewBlogs = (bool) ($adminUser && $adminUser->can('view-blogs'));
        $canViewSettings = (bool) ($adminUser && $adminUser->can('view-settings'));
        $canViewCompanies = (bool) ($adminUser && $adminUser->can('view-companies'));
        $canViewCMS = $canViewBlogs || $canViewSettings || $canViewCompanies;

        $canViewOffers = (bool) ($adminUser && $adminUser->can('view-offers'));
        $canViewContracts = (bool) ($adminUser && $adminUser->can('view-contracts'));
        $canViewAppointments = (bool) ($adminUser && $adminUser->can('view-appointments'));
        $canViewWorksheets = (bool) ($adminUser && $adminUser->can('view-worksheets'));
        $canViewCashReceipts = (bool) ($adminUser && $adminUser->can('view-cash-receipts'));
        $canViewLeads = (bool) ($adminUser && $adminUser->can('view-leads'));
        $canViewClients = (bool) ($adminUser && $adminUser->can('view-clients'));
        $canViewAutomatedEmails = (bool) ($adminUser && $adminUser->can('view-automated-emails'));
        $canViewBulkEmails = (bool) ($adminUser && $adminUser->can('view-bulk-emails'));
        $canViewBusiness = $canViewOffers || $canViewContracts || $canViewAppointments || $canViewWorksheets || $canViewCashReceipts || $canViewLeads || $canViewClients || $canViewAutomatedEmails || $canViewBulkEmails;

        $canViewVehicles = (bool) ($adminUser && $adminUser->can('view-vehicles'));

        $canViewViewedProducts = (bool) ($adminUser && $adminUser->can('view-viewed-products'));
        $canViewPurchasedProducts = (bool) ($adminUser && $adminUser->can('view-purchased-products'));
        $canViewSearchedProducts = (bool) ($adminUser && $adminUser->can('view-searched-products'));
        $canViewWebshopAnalytics = $canViewViewedProducts || $canViewPurchasedProducts || $canViewSearchedProducts;

        $canViewInstallations = (bool) ($adminUser && $adminUser->can('view-installations'));
        $canViewLeadConversion = (bool) ($adminUser && $adminUser->can('view-lead-conversion-report'));
        $canViewContractProductsReport = (bool) ($adminUser && ($adminUser->can('view-contracts') || $adminUser->can('view-own-contracts')));
        $canViewWorksheetProductsByWorkerReport = (bool) ($adminUser && $adminUser->can('view-worksheet-products-by-worker-report'));
        $canViewCRMAnalytics = $canViewInstallations || $canViewLeadConversion || $canViewContractProductsReport || $canViewWorksheetProductsByWorkerReport;

        $canViewAdminLogs = (bool) ($adminUser && $adminUser->can('view-admin-logs'));
        $canViewSystemAnalytics = $canViewAdminLogs;
    @endphp

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

    @if($canViewSales)
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSale" aria-expanded="false">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <span>Értékesítés <span id="new_sale" class="badge badge-secondary ml-2 d-none">0</span></span>
            </a>
            <div id="collapseSale" class="collapse" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @if($canViewOrders)
                        <a class="collapse-item" href="{{ route('admin.orders.index') }}">Rendelések<span id="new_order_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                    @endif
                    @if($canViewCustomers)
                        <a class="collapse-item" href="{{ route('admin.customers.index') }}">Vevők és partnerek<span id="new_customer_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                    @endif
                </div>
            </div>
        </li>
    @endif

    @if($canViewProductSection)
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProducts" aria-expanded="false">
                <i class="fa-solid fa-list"></i>
                <span>Termékek <span id="new_product" class="badge badge-secondary ml-2 d-none">0</span></span>
            </a>
            <div id="collapseProducts" class="collapse" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @if($canViewProducts)
                        <a class="collapse-item" href="{{ route('admin.products.index') }}">Összes termék<span id="new_product_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                    @endif
                    @if($canViewCategories)
                        <a class="collapse-item" href="{{ route('admin.categories.index') }}">Kategóriák<span id="new_product_category_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                    @endif
                    @if($canViewAttributes)
                        <a class="collapse-item" href="{{ route('admin.attributes.index') }}">Egyedi tulajdonságok<span id="new_attribute_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                    @endif
                    @if($canViewTags)
                        <a class="collapse-item" href="{{ route('admin.tags.index') }}">Címkék<span id="new_tag_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                    @endif
                    @if($canViewBrands)
                        <a class="collapse-item" href="{{ route('admin.brands.index') }}">Gyártók<span id="new_brand_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                    @endif
                </div>
            </div>
        </li>
    @endif

    @if($canViewCMS)
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCMS" aria-expanded="false">
                <i class="fa-solid fa-file-lines"></i>
                <span>Tartalomkezelés</span>
            </a>
            <div id="collapseCMS" class="collapse" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @if($canViewBlogs)
                        <a class="collapse-item" href="{{ route('admin.blog.index') }}">Blog bejegyzések</a>
                    @endif
                    @if($canViewSettings)
                        <a class="collapse-item" href="{{ route('admin.settings.downloads.index') }}">Letöltések</a>
                        <a class="collapse-item" href="{{ route('admin.settings.regulations.index') }}">Szabályzatok</a>
                        <a class="collapse-item" href="{{ route('admin.settings.sites.index') }}">Telephelyek</a>
                        @if($canViewCompanies)
                            <a class="collapse-item" href="{{ route('admin.settings.companies.index') }}">Cégek</a>
                        @endif
                        <a class="collapse-item" href="{{ route('admin.settings.employees.index') }}">Munkatársak</a>
                        <a class="collapse-item" href="{{ route('admin.settings.media.index') }}">Média</a>
                    @endif
                </div>
            </div>
        </li>
    @endif

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Ügyvitel
    </div>

    @if($canViewBusiness)
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCustomerProcesses" aria-expanded="false">
                <i class="fa-solid fa-business-time"></i>
                <span>Ügyviteli folyamatok <span id="new_business" class="badge badge-secondary ml-2 d-none">0</span></span>
            </a>
            <div id="collapseCustomerProcesses" class="collapse" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @if($canViewOffers)
                        <a class="collapse-item" href="{{ route('admin.offers.index') }}">Ajánlatok<span id="new_offer_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                        <a class="collapse-item" href="{{ route('admin.partner-offers.index') }}">Partner ajánlatok</a>
                    @endif
                    @if($canViewContracts)
                        <a class="collapse-item" href="{{ route('admin.contracts.index') }}">Szerződések<span id="new_contract_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                    @endif
                    @if($canViewAppointments)
                        <a class="collapse-item" href="{{ route('admin.appointments.index') }}">Időpontfoglalások<span id="new_appointment_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                    @endif
                    @if($canViewWorksheets)
                        <a class="collapse-item" href="{{ route('admin.worksheets.index') }}">Munkalapok<span id="new_worksheet_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                    @endif
                    @if($canViewCashReceipts)
                        <a class="collapse-item" href="{{ route('admin.cash-receipts.index') }}">Készpénz tételek</a>
                    @endif
                    @if($canViewLeads)
                        <a class="collapse-item" href="{{ route('admin.leads.index') }}">Érdeklődések<span id="new_lead_badge" class="badge badge-secondary ml-2 d-none">0</span></a>
                    @endif
                    @if($canViewClients)
                        <a class="collapse-item" href="{{ route('admin.clients.index') }}">Ügyfelek</a>
                    @endif
                    @if($canViewAutomatedEmails)
                        <a class="collapse-item" href="{{ route('admin.automated-emails.index') }}">E-mail automatizáció</a>
                    @endif
                    @if($canViewBulkEmails)
                        <a class="collapse-item" href="{{ route('admin.bulk-emails.index') }}">Tömeges e-mail</a>
                    @endif
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseBusinessDocuments" aria-expanded="false">
                <i class="fa-solid fa-file-invoice"></i>
                <span>Bizonylatok</span>
            </a>
            <div id="collapseBusinessDocuments" class="collapse" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @if($adminUser && $adminUser->can('view-sales-invoices'))
                        <a class="collapse-item" href="{{ route('admin.documents.sales-invoices.index') }}">Kimenő számlák</a>
                    @endif
                    <a class="collapse-item" href="#">Bejövő számlák</a>
                    <a class="collapse-item" href="#">Szállítólevelek</a>
                    <a class="collapse-item" href="#">Bevételezések</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseWarehouse" aria-expanded="false">
                <i class="fa-solid fa-warehouse"></i>
                <span>Raktározás</span>
            </a>
            <div id="collapseWarehouse" class="collapse" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="#">Raktárkészletek</a>
                    <a class="collapse-item" href="#">Leltár</a>
                </div>
            </div>
        </li>
    @endif

    @if($canViewVehicles)
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.vehicles.index') }}">
                <i class="fa-solid fa-car"></i>
                <span>Járműtörzs <span id="vehicles_attention_badge" class="badge badge-secondary ml-2 d-none">0</span></span>
            </a>
        </li>
    @endif

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Beállítások
    </div>

    @if($canViewSettings)
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
                    <a class="collapse-item" href="{{ route('admin.units.index') }}">Mértékegységek</a>
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
    @endif

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Jelentések
    </div>

    @if($canViewWebshopAnalytics)
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseWebshopAnalytics" aria-expanded="false">
                <i class="fa-solid fa-chart-simple"></i>
                <span>Webshop</span>
            </a>
            <div id="collapseWebshopAnalytics" class="collapse" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @if($canViewViewedProducts)
                        <a class="collapse-item" href="{{ route('admin.stats.watched_products') }}">Megtekintett termékek</a>
                    @endif
                    @if($canViewPurchasedProducts)
                        <a class="collapse-item" href="{{ route('admin.stats.purchased_products') }}">Vásárolt termékek</a>
                    @endif
                    @if($canViewSearchedProducts)
                        <a class="collapse-item" href="{{ route('admin.stats.searched_products') }}">Keresések</a>
                    @endif
                </div>
            </div>
        </li>
    @endif

    @if($canViewCRMAnalytics)
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCRMAnalytics" aria-expanded="false">
                <i class="fa-solid fa-user-group"></i>
                <span>Ügyviteli</span>
            </a>
            <div id="collapseCRMAnalytics" class="collapse" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @if($canViewInstallations)
                        <a class="collapse-item" href="{{ route('admin.stats.installations') }}">Szerelések</a>
                    @endif
                    @if($canViewLeadConversion)
                        <a class="collapse-item" href="{{ route('admin.stats.lead_conversion') }}">Érdeklődő konverzió</a>
                    @endif
                    @if($canViewContractProductsReport)
                        <a class="collapse-item" href="{{ route('admin.stats.contract_products') }}">Szerződések termék db</a>
                    @endif
                    @if($canViewWorksheetProductsByWorkerReport)
                        <a class="collapse-item" href="{{ route('admin.stats.worksheet_products_by_worker') }}">Termékmennyiségek</a>
                    @endif
                </div>
            </div>
        </li>
    @endif

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

    @if($canViewSystemAnalytics)
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSystemAnalytics" aria-expanded="false">
                <i class="fa-solid fa-gear"></i>
                <span>Általános</span>
            </a>
            <div id="collapseSystemAnalytics" class="collapse" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @if($canViewAdminLogs)
                        <a class="collapse-item" href="{{ route('admin.stats.admin_logs') }}">Admin tevékenységek</a>
                        <a class="collapse-item" href="{{ route('admin.stats.laravel_logs') }}">Laravel logok</a>
                    @endif
                </div>
            </div>
        </li>
    @endif

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
