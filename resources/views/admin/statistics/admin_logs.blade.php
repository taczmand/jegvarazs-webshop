@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <style>
            #adminTable th:nth-child(6),
            #adminTable td:nth-child(6) {
                min-width: 520px;
            }

            #adminTable td.details-control {
                white-space: nowrap;
            }

            #adminTable tr.dt-hasChild > td {
                border-bottom: none;
            }
        </style>

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Admin tevékenységek</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-admin-logs'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Felhasználónév" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="filter-input form-select" data-column="2">
                            <option value="">Entitás (összes)</option>
                            <option value="users">Felhasználók</option>
                            <option value="customers">Vevők</option>
                            <option value="orders">Rendelések</option>
                            <option value="products">Termékek</option>
                            <option value="categories">Kategóriák</option>
                            <option value="blog_posts">Blog bejegyzések</option>
                            <option value="basic_data">Alapadatok</option>
                            <option value="basic_media">Média</option>
                            <option value="automated_emails">E-mail automatizáció</option>
                            <option value="contracts">Szerződések</option>
                            <option value="contract_products">Szerződés termékek</option>
                            <option value="offers">Ajánlatok</option>
                            <option value="offer_products">Ajánlat termékek</option>
                            <option value="appointments">Időpontok</option>
                            <option value="appointment_photos">Időpont képek</option>
                            <option value="worksheets">Munkalapok</option>
                            <option value="worksheet_images">Munkalap képek</option>
                            <option value="worksheet_products">Munkalap termékek</option>
                            <option value="worksheet_workers">Munkalap munkatársak</option>
                            <option value="brands">Gyártók</option>
                            <option value="tags">Címkék</option>
                            <option value="attributes">Tulajdonságok</option>
                            <option value="clients">Ügyfelek</option>
                            <option value="client_addresses">Ügyfél címek</option>
                            <option value="company_sites">Telephelyek</option>
                            <option value="coupons">Kuponok</option>
                            <option value="downloads">Letöltések</option>
                            <option value="employees">Munkatársak</option>
                            <option value="leads">Érdeklődők</option>
                            <option value="newsletter_subscriptions">Hírlevél feliratkozások</option>
                            <option value="carts">Kosarak</option>
                            <option value="cart_items">Kosár tételek</option>
                            <option value="order_histories">Rendelés előzmények</option>
                            <option value="order_items">Rendelés tételek</option>
                            <option value="partner_products">Partner termékek</option>
                            <option value="product_photos">Termék képek</option>
                            <option value="searched">Keresések</option>
                            <option value="watched_products">Megtekintett termékek</option>
                            <option value="shipping_methods">Szállítási módok</option>
                            <option value="payment_methods">Fizetési módok</option>
                            <option value="stock_statuses">Raktári állapotok</option>
                            <option value="order_statuses">Rendelési állapotok</option>
                            <option value="tax_categories">Adó osztályok</option>
                        </select>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="filter-input form-select" data-column="3">
                            <option value="">Akció (összes)</option>
                            <option value="created">Létrehozott</option>
                            <option value="updated">Frissített</option>
                            <option value="deleted">Törölt</option>
                        </select>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Rekord" class="filter-input form-control" data-column="4">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Adat" class="filter-input form-control" data-column="5">
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Felhasználónév</th>
                        <th data-priority="2">Entitás</th>
                        <th data-priority="3">Akció</th>
                        <th data-priority="4">Rekord</th>
                        <th data-priority="5">Adat</th>
                        <th data-priority="5">Időpont</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod az admin tevékenységek megtekintésére.
                </div>
            @endif
        </div>
    </div>


@endsection

@section('scripts')
    <script type="module">

        document.addEventListener('DOMContentLoaded', () => {
            const table = initCrud({
                tableId: 'adminTable',
                dataUrl: '{{ route('admin.stats.admin_logs.data') }}',
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                columns: [
                    { data: 'id' },
                    { data: 'user_name' },
                    { data: 'model' },
                    { data: 'action' },
                    { data: 'record' },
                    {
                        data: 'data',
                        className: 'details-control',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                if (data && typeof data === 'object' && Array.isArray(data.changes)) {
                                    const changes = data.changes;
                                    if (changes.length === 0) {
                                        return `<span class="text-muted">-</span>`;
                                    }

                                    const preview = changes.slice(0, 2).map(c => `${c.field}: ${formatValue(c.old)} → ${formatValue(c.new)}`).join(' | ');
                                    const more = changes.length > 2 ? ` (+${changes.length - 2})` : '';

                                    return `
                                        <div class="d-flex align-items-center gap-2" style="width: 100%;">
                                            <span class="text-truncate" style="max-width: 100%;">${escapeHtml(preview)}${escapeHtml(more)}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary toggle-details" data-id="${escapeHtml(row?.id ?? '')}">Részletek</button>
                                        </div>
                                    `;
                                }

                                const stringData = typeof data === 'string' ? data : JSON.stringify(data ?? '');
                                const shortText = stringData.length > 80 ? stringData.substring(0, 77) + '...' : stringData;

                                return `<span>${escapeHtml(shortText)}</span>`;
                            }
                            return data;
                        }
                    },
                    { data: 'created_at' }
                ]
            });

            $('#adminTable').on('click', 'button.toggle-details', function () {
                const tr = $(this).closest('tr');
                const row = table.row(tr);
                const rowData = row.data();

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    return;
                }

                const html = buildDetailsHtml(rowData?.data);
                row.child(html).show();
                tr.addClass('shown');
            });
        });

        function formatValue(v) {
            if (v === null || typeof v === 'undefined') return '-';
            if (typeof v === 'boolean') return v ? 'Igen' : 'Nem';
            if (typeof v === 'object') return JSON.stringify(v);
            return String(v);
        }

        function escapeHtml(unsafe) {
            return String(unsafe)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function buildDetailsHtml(data) {
            if (!data || typeof data !== 'object' || !Array.isArray(data.changes) || data.changes.length === 0) {
                const stringData = typeof data === 'string' ? data : JSON.stringify(data ?? '');
                return `<div class="p-3">${escapeHtml(stringData || '-')}</div>`;
            }

            const rowsHtml = data.changes.map(c => {
                return `
                    <tr>
                        <td style="width: 30%; white-space: nowrap;"><strong>${escapeHtml(c.field)}</strong></td>
                        <td style="width: 35%; color: #6b7280;">${escapeHtml(formatValue(c.old))}</td>
                        <td style="width: 35%;">${escapeHtml(formatValue(c.new))}</td>
                    </tr>
                `;
            }).join('');

            return `
                <div class="p-3 bg-white">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Mező</th>
                                <th>Eredeti</th>
                                <th>Új</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>
                </div>
            `;
        }


    </script>
@endsection
