<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('storage/' . $basicmedia['favicon']) }}" type="image/x-icon">
    <title>@yield('title', 'Admin')</title>
    @vite('resources/sass/admin.scss')
    <script>
        window.appConfig = {
            APP_URL: "{{ config('app.url') }}"
        };



        document.addEventListener('click', function (e) {
            const editBtn = e.target.closest('.edit');
            if (editBtn) {
                showLoader();
            }
        });

        document.addEventListener('click', function (e) {
            const viewBtn = e.target.closest('.view');
            if (viewBtn) {
                showLoader();
            }
        });

        document.addEventListener('shown.bs.modal', function (e) {
            hideLoader();
        });

        function showLoader() {
            const loader = document.getElementById('global-loader');
            loader.style.display = 'flex';
        }

        function hideLoader() {
            const loader = document.getElementById('global-loader');
            loader.style.display = 'none';
        }

        function checkForNewRecords() {
            fetch(`${window.appConfig.APP_URL}admin/beallitasok/uj-adatok`)
                .then(response => response.json())
                .then(data => {
                    // Konfigurációs tábla menü szintű badge-ekkel
                    const menuConfig = {
                        sales: {
                            globalBadgeId: 'new_sale',
                            models: {
                                orders: { label: 'rendelés', badgeId: 'new_order_badge' },
                                coupons: { label: 'kupon', badgeId: 'new_coupon_badge' },
                                customers: { label: 'vevő', badgeId: 'new_customer_badge' },
                            }
                        },
                        products: {
                            globalBadgeId: 'new_product',
                            models: {
                                products: { label: 'termék', badgeId: 'new_product_badge' },
                                categories: { label: 'kategória', badgeId: 'new_product_category_badge' },
                                attributes: { label: 'tulajdonság', badgeId: 'new_attribute_badge' },
                                tags: { label: 'címke', badgeId: 'new_tag_badge' },
                                brands: { label: 'gyártó', badgeId: 'new_brand_badge' }
                            }
                        },
                        business: {
                            globalBadgeId: 'new_business',
                            models: {
                                appointments: { label: 'időpontfoglalás', badgeId: 'new_appointment_badge' },
                                offers: { label: 'ajánlat', badgeId: 'new_offer_badge' },
                                contracts: { label: 'szerződés', badgeId: 'new_contract_badge' },
                                worksheets: { label: 'munkalap', badgeId: 'new_worksheet_badge' },
                                leads: { label: 'eredklődő', badgeId: 'new_lead_badge' },
                            }
                        }
                    };

                    // Alap: az összes badge d-none lesz
                    Object.values(menuConfig).forEach(menu => {
                        const globalBadge = document.getElementById(menu.globalBadgeId);
                        if (globalBadge) globalBadge.classList.add('d-none');

                        Object.values(menu.models).forEach(model => {
                            const badge = document.getElementById(model.badgeId);
                            if (badge) badge.classList.add('d-none');
                        });
                    });

                    // Most kezeljük a beérkező új adatokat
                    const perMenuCount = {}; // főmenü összesítők

                    if (data && data.length > 0) {
                        data.forEach(item => {
                            for (const [menuKey, menu] of Object.entries(menuConfig)) {
                                if (menu.models[item.model]) {
                                    const config = menu.models[item.model];
                                    const badge = document.getElementById(config.badgeId);
                                    const count = item.count || 0;

                                    if (badge) {
                                        badge.textContent = count;
                                        if (count > 0) {
                                            badge.classList.remove('d-none');
                                            perMenuCount[menu.globalBadgeId] = (perMenuCount[menu.globalBadgeId] || 0) + count;
                                        }
                                    }
                                }
                            }
                        });

                        // Frissítjük a főmenü badge-eket (összesített)
                        for (const [globalBadgeId, count] of Object.entries(perMenuCount)) {
                            const globalBadge = document.getElementById(globalBadgeId);
                            if (globalBadge) {
                                globalBadge.textContent = count;
                                if (count > 0) {
                                    globalBadge.classList.remove('d-none');
                                } else {
                                    globalBadge.classList.add('d-none');
                                }
                            }
                        }
                    }
                });
        }


        // Azonnali meghívás betöltéskor
        checkForNewRecords();

        // Ismétlés 10 másodpercenként
        setInterval(checkForNewRecords, 30000);


    </script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.5/css/responsive.dataTables.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
</head>

<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">

    @include('admin.partials.sidebar') <!-- oldalsó menü -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>
            {{-- @include('admin.partials.topbar') --}}


            <!-- Begin Page Content -->
            <div class="container-fluid pt-3 page-content">
                @yield('content')
                <div id="global-loader" style="
                        display: none;
                        position: fixed;
                        top: 0; left: 0; right: 0; bottom: 0;
                        background: rgba(255,255,255,0.7);
                        z-index: 9999;
                        justify-content: center;
                        align-items: center;
                    ">
                    <div class="spinner"></div>
                </div>
            </div>

        </div>

        {{--@include('admin.partials.footer')--}}

</div>

</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
<div id="globalToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
<div class="d-flex">
    <div id="globalToastMessage" class="toast-body">
        Művelet sikeres!
    </div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Bezárás"></button>
</div>
</div>
</div>


<!-- Vendor Scripts -->
<script src="{{ asset('vendor/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('vendor/js/datatables.min.js') }}"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.5/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.5/js/responsive.dataTables.js"></script>

<!-- Custom Scripts with Vite load -->
@vite('resources/js/admin.js')
@yield('scripts')
</body>

</html>
