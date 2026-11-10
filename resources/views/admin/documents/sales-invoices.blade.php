@extends('layouts.admin')

@section('content')

    <style>
        #invoice_items_table {
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        #invoice_items_table td,
        #invoice_items_table th {
            border: none !important;
        }
        #invoice_items_table tbody tr td {
            background: #f4f5f7;
            padding-top: 6px;
            padding-bottom: 6px;
            vertical-align: middle;
        }
        #invoice_items_table tbody tr td:first-child {
            border-radius: 8px 0 0 8px;
        }
        #invoice_items_table tbody tr td:last-child {
            border-radius: 0 8px 8px 0;
        }
        #invoice_items_table tbody tr td .form-control,
        #invoice_items_table tbody tr td .form-select {
            background: #fff;
        }
        .item-thumb {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            background: #fff;
            flex: 0 0 auto;
            transition: transform .15s ease;
            cursor: zoom-in;
        }
        .item-thumb:hover {
            transform: scale(4);
            transform-origin: left center;
            position: relative;
            z-index: 10;
        }
        .item-thumb-placeholder {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px dashed #ced4da;
            color: #adb5bd;
            font-size: 1rem;
            flex: 0 0 auto;
        }
        .admin-fieldset__legend {
            background: #e9ecef;
            padding: .25rem .75rem;
            border-radius: .375rem;
        }
    </style>

    <div class="container-fluid px-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyvitel / Bizonylatok / Kimenő számlák</h2>
            @if(auth('admin')->user()->can('create-sales-invoice'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új számla</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-sales-invoices'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Számlaszám" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Partner" class="filter-input form-control" data-column="2">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="10">
                            <option value="">Fizetés (összes)</option>
                            <option value="unpaid">Nincs kifizetve</option>
                            <option value="partially_paid">Részben van kifizetve</option>
                            <option value="paid">Kifizetve</option>
                            <option value="overdue">Lejárt</option>
                        </select>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="9">
                            <option value="">Állapot (összes)</option>
                            <option value="draft">Piszkozat</option>
                            <option value="issued">Kiállítva</option>
                            <option value="cancelled">Érvénytelenítve</option>
                        </select>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="11">
                            <option value="">Típus (összes)</option>
                            <option value="storno">Sztornó</option>
                            <option value="normal">Papír</option>
                            <option value="electronic">E-számla</option>
                            <option value="correction">Helyesbítő</option>
                        </select>
                    </div>

                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Számlaszám</th>
                        <th>Partner</th>
                        <th>Fiz.mód</th>
                        <th>Kelt</th>
                        <th>Határidő</th>
                        <th>Bruttó</th>
                        <th>Kintlévőség</th>
                        <th>Létrehozva</th>
                        <th>Állapot</th>
                        <th>Fizetés</th>
                        <th>Típus</th>
                        <th>Megj.</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Nincs jogosultságod a kimenő számlák megtekintéséhez.
                </div>
            @endif
        </div>
    </div>


    <x-admin.document-modal id="salesInvoiceModal" title="Kimenő számla" form-id="salesInvoiceForm" save-button-id="saveDraftSalesInvoice" pane-left="25%" pane-mid="75%">
        <x-slot:left>
            <input type="hidden" id="invoice_id" name="id">
            <input type="hidden" id="correction_of_sales_invoice_id" name="correction_of_sales_invoice_id">

            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Számla kiállító adatai</legend>
                <div class="mb-3">
                    <select class="form-select" id="company_id" name="company_id" required>
                        @foreach(($companies ?? []) as $c)
                            <option value="{{ $c->id }}" @selected(isset($defaultCompanyId) && (int) $c->id === (int) $defaultCompanyId)>
                                {{ $c->name }}@if($c->is_default) (alapértelmezett)@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-0">
                    <label for="company_invoice_prefix_id" class="form-label">Számla előtag</label>
                    <select class="form-select" id="company_invoice_prefix_id" name="company_invoice_prefix_id">
                        <option value="">(nincs)</option>
                    </select>
                </div>
                <input type="hidden" id="invoice_number" name="invoice_number" value="">


            </fieldset>

            <fieldset class="admin-fieldset mt-3">
                <legend class="admin-fieldset__legend">Vevő</legend>

                <div class="row g-2">
                    <div class="col-12">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100" id="selectPartnerBtn">
                            <i class="fas fa-user-tag me-1"></i> Vevő kiválasztása
                        </button>
                    </div>
                    <div class="col-12">
                        <label for="partner_name" class="form-label">Név*</label>
                        <input type="text" class="form-control" id="partner_name" name="partner_name" required autocomplete="off">
                    </div>
                    <div class="col-12">
                        <label for="partner_tax_number" class="form-label">Adószám</label>
                        <input type="text" class="form-control" id="partner_tax_number" name="partner_tax_number">
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12 col-md-4">
                        <label for="partner_country" class="form-label">Ország</label>
                        <select class="form-select" id="partner_country" name="partner_country" required>
                            @foreach(config('countries') as $code => $name)
                                <option value="{{ $code }}" {{ $code === 'HU' ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="partner_zip_code" class="form-label">Irányítószám</label>
                        <input type="text" class="form-control" id="partner_zip_code" name="partner_zip_code">
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="partner_city" class="form-label">Város</label>
                        <input type="text" class="form-control" id="partner_city" name="partner_city">
                    </div>
                </div>
                <div class="row g-2 mt-1">
                    <div class="col-12">
                        <label for="partner_address_line" class="form-label">Cím</label>
                        <input type="text" class="form-control" id="partner_address_line" name="partner_address_line">
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12">
                        <label for="partner_email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="partner_email" name="partner_email">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="send_email"
                                   name="send_email"
                                   value="1">
                            <label for="send_email"
                                   class="form-check-label"
                                   title="Amennyiben ez be van pipálva, az elkészült számla kiküldésre kerül a partner e-mail címére">
                                Számla küldése emailben?
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row g-2 mt-1">
                    <div class="col-12">
                        <label for="partner_phone" class="form-label">Telefon</label>
                        <input type="text" class="form-control" id="partner_phone" name="partner_phone">
                    </div>
                </div>
            </fieldset>

            <fieldset class="admin-fieldset mb-3 mt-3">
                <legend class="admin-fieldset__legend">Fizetés</legend>

                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <label for="payment_method" class="form-label">Fizetési mód*</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="bank_transfer">Átutalás</option>
                                <option value="cash">Készpénz</option>
                                <option value="cod">Utánvét</option>
                                <option value="credit_card">Bankkártya</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="currency" class="form-label">Pénznem</label>
                            <select class="form-select" id="currency" name="currency" required>
                                <option value="HUF">HUF</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="invoice_type" class="form-label">Számla típusa</label>
                            <select class="form-select" id="invoice_type" name="invoice_type" required>
                                <option value="normal">Papír</option>
                                <option value="electronic">Elektronikus számla</option>
                                <option value="correction">Helyesbítő</option>
                            </select>
                        </div>
                    </div>


                    <div class="mt-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <label for="issued_at" class="form-label mb-0">Számla kelte</label>
                            <input type="date" class="form-control form-control-sm ms-auto" id="issued_at" name="issued_at" max="2099-12-31" readonly style="pointer-events:none; background-color: #e9ecef; max-width: 180px;">
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <label for="fulfilled_at" class="form-label mb-0">Teljesítés dátuma</label>
                            <input type="date" class="form-control form-control-sm ms-auto" id="fulfilled_at" name="fulfilled_at" max="2099-12-31" style="max-width: 180px;">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="due_at" class="form-label mb-0">Fizetési határidő</label>
                            <input type="date" class="form-control form-control-sm ms-auto" id="due_at" name="due_at" max="2099-12-31" style="max-width: 180px;">
                        </div>
                    </div>
            </fieldset>

            <fieldset class="admin-fieldset mb-3 mt-3">
                <legend class="admin-fieldset__legend">Megjegyzés</legend>

                <div class="mb-0">
                    <label for="note" class="form-label">Belső megjegyzés</label>
                    <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                </div>

                <div class="mb-3 mt-3">
                    <label for="note_for_document" class="form-label">Megjegyzés a bizonylatra</label>
                    <textarea class="form-control" id="note_for_document" name="note_for_document" rows="3"></textarea>
                </div>

            </fieldset>
        </x-slot:left>

        <x-slot:middle>
            <fieldset class="admin-fieldset mb-3 mb-3">
                <legend class="admin-fieldset__legend">Tételek</legend>

                <div class="mb-2">
                    <label for="product_search" class="form-label">Új tétel rögzítése</label>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="favorite_products" style="font-size: 0.85rem; flex: 0 0 20%;">
                            <option value="">Kedvencek…</option>
                        </select>
                        <input type="text" class="form-control form-control-sm" id="product_search" placeholder="Kezdj el gépelni..." style="font-size: 0.85rem; flex: 1;">
                    </div>
                </div>

                <div class="list-group mb-3" id="product_search_results" style="max-height: 240px; overflow:auto; font-size: 0.85rem;"></div>

                <div class="table-responsive" style="overflow-x:auto;">
                    <table class="table table-sm align-middle" id="invoice_items_table" style="font-size: 0.85rem; min-width: 1100px;">
                        <thead>
                        <tr>
                            <th>Megnevezés</th>
                            <th>Raktár</th>
                            <th class="text-end">Mennyiség</th>
                            <th class="text-center">Mee.</th>
                            <th class="text-end">Kedvezmény%</th>
                            <th class="text-end">Bruttó egységár</th>
                            <th>Áfakulcs</th>
                            <th class="text-end">Nettó</th>
                            <th class="text-end">ÁFA</th>
                            <th class="text-end">Bruttó</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <input type="hidden" name="items_json" id="items_json" value="[]">
            </fieldset>
        </x-slot:middle>

        <x-slot:footer>
            <button type="button" class="btn btn-outline-primary" id="previewSalesInvoice">Előnézet</button>
            <button type="button" class="btn btn-primary saveDraftSalesInvoice">Piszkozat mentése</button>
        </x-slot:footer>
    </x-admin.document-modal>

    <div class="modal fade" id="salesInvoicePreviewModal" tabindex="-1" aria-labelledby="salesInvoicePreviewModalLabel" aria-hidden="true" style="z-index: 2000;">
        <div class="modal-dialog modal-fullscreen m-0 p-0">
            <div class="modal-content" style="height: 100vh; border-radius: 0; display: flex; flex-direction: column;">
                <div class="modal-header">
                    <h5 class="modal-title" id="salesInvoicePreviewModalLabel">Kimenő számla előnézet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <div class="modal-body p-0" style="flex: 1 1 auto;">
                    <iframe id="sales_invoice_preview_iframe" title="PDF előnézet" style="width: 100%; height: 100%; border: 0; display:block;"></iframe>
                </div>
                <div class="modal-footer" id="salesInvoicePreviewModalFooter">
                    <button type="button" class="btn btn-success" id="createSalesInvoice">
                        Számla létrehozása
                    </button>

                    <button type="button" class="btn btn-primary saveDraftSalesInvoice">
                        Piszkozat mentése
                    </button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Mégse
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modális ablak vevő kiválasztására / létrehozására -->
    <div class="modal fade admin-modal-soft" id="partnerSelectModal" tabindex="-1" aria-labelledby="partnerSelectModalLabel" aria-hidden="true" style="z-index: 2100;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-custom">
                    <h5 class="modal-title" id="partnerSelectModalLabel">Vevő kiválasztása</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <div class="modal-body">

                    <div id="partnerSearchPane">
                        <div class="mb-2">
                            <label for="partner_search_input" class="form-label">Keresés (név, e-mail, telefon)</label>
                            <input type="text" class="form-control" id="partner_search_input" placeholder="Kezdj el gépelni..." autocomplete="off">
                        </div>
                        <div id="partner_select_results" style="max-height: 360px; overflow-y: auto;"></div>
                    </div>

                    <div id="partnerCreatePane" style="display:none;">
                        <div id="nav_taxpayer_results" class="mb-2"></div>
                        <form id="partnerCreateForm">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label for="new_client_name" class="form-label">Név*</label>
                                    <input type="text" class="form-control" id="new_client_name" name="name" required>
                                </div>
                                <div class="col-12">
                                    <label for="new_client_client_name" class="form-label">Cégnév</label>
                                    <input type="text" class="form-control" id="new_client_client_name" name="address_client_name">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="new_client_tax_number" class="form-label">Adószám</label>
                                    <input type="text" class="form-control" id="new_client_tax_number" name="address_tax_number">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="new_client_phone" class="form-label">Telefon</label>
                                    <input type="text" class="form-control" id="new_client_phone" name="phone">
                                </div>
                                <div class="col-12">
                                    <label for="new_client_email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="new_client_email" name="email">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="new_client_country" class="form-label">Ország*</label>
                                    <select class="form-select" id="new_client_country" name="address_country" required>
                                        @foreach(config('countries') as $code => $name)
                                            <option value="{{ $code }}" {{ $code === 'HU' ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="new_client_zip_code" class="form-label">Irányítószám*</label>
                                    <input type="text" class="form-control" id="new_client_zip_code" name="address_zip_code" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="new_client_city" class="form-label">Város*</label>
                                    <input type="text" class="form-control" id="new_client_city" name="address_city" required>
                                </div>
                                <div class="col-12">
                                    <label for="new_client_address_line" class="form-label">Cím*</label>
                                    <input type="text" class="form-control" id="new_client_address_line" name="address_address_line" required>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="partnerCreateBackBtn" style="display:none;">
                        <i class="fas fa-arrow-left me-1"></i> Vissza a kereséshez
                    </button>
                    <button type="button" class="btn btn-success" id="partnerCreateToggleBtn">
                        <i class="fas fa-plus me-1"></i> Új vevő létrehozása
                    </button>
                    <button type="button" class="btn btn-primary" id="partnerCreateSaveBtn" style="display:none;">
                        Vevő mentése és kiválasztása
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modális ablak fizetésekre -->
    <div class="modal fade admin-modal-soft" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="paymentForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-custom">
                        <h5 class="modal-title" id="paymentModalLabel">Pénzügyek</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="sales_invoice_id" name="sales_invoice_id">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Dátum</th>
                                    <th>Összeg</th>
                                    <th>Fizetési mód</th>
                                    <th>Tranzakció azonosító</th>
                                    <th class="text-end">Művelet</th>
                                </tr>
                                </thead>
                                <tbody id="salesPaymentsTableBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <small><i>Fizetési tétel törlésére a Szamlazz.hu-n sajnos nincs lehetőség API-n keresztül, így ezt manuálisan kell megtenni. A törlés gombbal csak a rendszer adatbázisából törli az összeget!</i></small>
                        <button type="button" class="btn btn-success" id="addSalesPayment">
                            <i class="fas fa-plus"></i>
                            Új befizetés
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script type="module">

        const companies = @json($companies ?? []);
        const companyInvoicePrefixes = @json($companyInvoicePrefixes ?? []);
        const warehouses = @json($warehouses ?? []);
        const taxCategories = @json($taxCategories ?? []);
        const defaultCompanyId = @json($defaultCompanyId ?? null);

        const modalDOM = document.getElementById('salesInvoiceModal');
        const modal = new bootstrap.Modal(modalDOM);
        const previewModalDOM = document.getElementById('salesInvoicePreviewModal');
        const previewModal = previewModalDOM ? new bootstrap.Modal(previewModalDOM) : null;
        const financesModalDOM = document.getElementById('paymentModal');
        const financesModal = financesModalDOM ? new bootstrap.Modal(financesModalDOM) : null;
        const partnerSelectModalDOM = document.getElementById('partnerSelectModal');
        const partnerSelectModal = partnerSelectModalDOM ? new bootstrap.Modal(partnerSelectModalDOM) : null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        $(document).ready(function() {

            const previewModalFooter = document.getElementById('salesInvoicePreviewModalFooter');

            function setPreviewModalFooterVisible(isVisible) {
                if (!previewModalFooter) return;
                previewModalFooter.style.display = isVisible ? '' : 'none';
            }

            function populateCompanyPrefixes(companyId, selectedId = '') {
                const $select = $('#company_invoice_prefix_id');
                if (!$select.length) return;

                const cid = String(companyId || '').trim();
                const prefixes = (Array.isArray(companyInvoicePrefixes) ? companyInvoicePrefixes : [])
                    .filter(p => String(p.company_id || '') === cid && Boolean(p.is_active));

                $select.empty();
                $select.append(`<option value="">(nincs)</option>`);

                prefixes.forEach(p => {
                    const id = String(p.id);
                    const label = String(p.prefix || '');
                    $select.append(`<option value="${escapeHtml(id)}">${escapeHtml(label)}${p.is_default ? ' (alapértelmezett)' : ''}</option>`);
                });

                const sel = String(selectedId || '').trim();
                if (sel !== '') {
                    $select.val(sel);
                    return;
                }

                const def = prefixes.find(p => Boolean(p.is_default));
                if (def) {
                    $select.val(String(def.id));
                }
            }

            $('#company_id').on('change', function () {
                populateCompanyPrefixes($(this).val(), '');
            });

            if (previewModalDOM) {
                previewModalDOM.addEventListener('shown.bs.modal', function () {
                    if (modalDOM) {
                        modalDOM.style.visibility = 'hidden';
                    }
                });

                previewModalDOM.addEventListener('hidden.bs.modal', function () {
                    if (modalDOM) {
                        modalDOM.style.visibility = '';
                    }
                    resetPreview();
                    setPreviewModalFooterVisible(true);
                });
            }

            $('#adminTable').on('click', '.pdf', async function () {
                const invoiceId = String($(this).data('id') || '').trim();
                if (!invoiceId) {
                    showToast('Nem található a bizonylat azonosítója.', 'danger');
                    return;
                }

                const src = `{{ route('admin.documents.sales-invoices.pdf', ['id' => '__ID__']) }}`.replace('__ID__', String(invoiceId));
                $('#sales_invoice_preview_iframe').attr('src', src);
                setPreviewModalFooterVisible(false);
                if (previewModal) previewModal.show();
            });

            $('#adminTable').on('click', '.storno', async function () {
                const invoiceId = String($(this).data('id') || '').trim();
                if (!invoiceId) {
                    showToast('Nem található a bizonylat azonosítója.', 'danger');
                    return;
                }

                if (!confirm('Biztosan sztornózod ezt a számlát?')) {
                    return;
                }

                try {
                    const url = `{{ route('admin.documents.sales-invoices.storno', ['id' => '__ID__']) }}`.replace('__ID__', String(invoiceId));
                    const resp = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!resp.ok) {
                        let msg = 'Hiba történt a sztornózáskor.';
                        try {
                            const json = await resp.json();
                            if (json?.message) msg = json.message;
                        } catch (e) {}
                        throw new Error(msg);
                    }

                    const json = await resp.json().catch(() => ({}));
                    showToast(json?.message || 'Sztornó sikeres.', 'success');
                    table.ajax.reload(null, false);
                    if (previewModal) previewModal.hide();
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                }
            });

            $('#adminTable').on('click', '.correction', async function () {
                const originalId = String($(this).data('id') || '').trim();
                if (!originalId) {
                    showToast('Nem található a bizonylat azonosítója.', 'danger');
                    return;
                }

                resetForm('Helyesbítő számla');
                const saveDraftBtn = document.getElementsByClassName('saveDraftSalesInvoice');
                saveDraftBtn[0].style.display = 'none';


                try {
                    const resp = await fetch(`{{ route('admin.documents.sales-invoices.show', ['id' => '__ID__']) }}`.replace('__ID__', String(originalId)), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (!resp.ok) {
                        let msg = 'Hiba történt a számla betöltésekor.';
                        try {
                            const json = await resp.json();
                            if (json?.message) msg = json.message;
                        } catch (e) {}
                        throw new Error(msg);
                    }

                    const json = await resp.json();
                    const invoice = json?.invoice || {};
                    const invoiceItems = Array.isArray(json?.items) ? json.items : [];

                    $('#invoice_id').val('');
                    $('#correction_of_sales_invoice_id').val(invoice.id || originalId);

                    $('#company_id').val(invoice.company_id || defaultCompanyId);
                    populateCompanyPrefixes(invoice.company_id || defaultCompanyId, invoice.company_invoice_prefix_id || '');
                    $('#invoice_number').val(invoice.invoice_number || '');

                    $('#partner_name').val(invoice.partner_name || '');
                    $('#partner_tax_number').val(invoice.partner_tax_number || '');
                    $('#partner_country').val(invoice.partner_country || 'HU');
                    $('#partner_zip_code').val(invoice.partner_zip_code || '');
                    $('#partner_city').val(invoice.partner_city || '');
                    $('#partner_address_line').val(invoice.partner_address_line || '');
                    $('#partner_email').val(invoice.partner_email || '');
                    $('#partner_phone').val(invoice.partner_phone || '');

                    $('#payment_method').val(invoice.payment_method || 'bank_transfer');
                    if ($('#invoice_type').length) {
                        $('#invoice_type').val('correction');
                    }

                    $('#issued_at').val(todayDate());
                    $('#fulfilled_at').val(todayDate());
                    $('#due_at').val(dueDateForPaymentMethod(invoice.payment_method || 'bank_transfer', todayDate()));

                    $('#currency').val(invoice.currency || 'HUF');
                    if ($('#prices_include_vat').length) {
                        $('#prices_include_vat').prop('checked', Boolean(invoice.prices_include_vat));
                    }

                    $('#status').val('draft');
                    $('#payment_status').val('unpaid');

                    const originalNumber = String(invoice.invoice_number || '').trim();
                    $('#note').val(originalNumber ? `Helyesbítő számla ehhez: ${originalNumber}` : '');
                    $('#note_for_document').val(invoice.note_for_document || '');

                    resetPreview();

                    items.splice(0, items.length);
                    invoiceItems.forEach(it => {
                        const warehouseId = it.warehouse_id ?? null;
                        const wh = warehouseId ? warehouses.find(w => String(w.id) === String(warehouseId)) : null;
                        const qty = Number(it.quantity ?? 0);
                        const negQty = qty === 0 ? 0 : (-1 * Math.abs(qty));

                        const item = {
                            product_id: it.product_id ?? null,
                            image_url: it.image_url ?? null,
                            warehouse_id: warehouseId,
                            warehouse_name: wh?.name ?? '',
                            name: it.name ?? '',
                            quantity: negQty,
                            qty_step: 1,
                            unit_abbreviation: it.unit ?? '',
                            discount_percent: Number(it.discount_percent ?? 0),
                            vat_percent: Number(it.vat_percent ?? 0),
                            vat_code: it.vat_code || taxNameForValue(Number(it.vat_percent ?? 0)),
                            unit_net_price: Number(it.unit_net_price ?? 0),
                            unit_gross_price: Number(it.unit_gross_price ?? 0),
                            net_total: Number(it.net_total ?? 0),
                            vat_total: Number(it.vat_total ?? 0),
                            gross_total: Number(it.gross_total ?? 0),
                            note: it.note ?? '',
                        };

                        recalcRow(item);
                        items.push(item);
                    });

                    renderItems();
                    syncItemsJson();
                    $('#product_search').val('');
                    $('#product_search_results').empty();

                    modal.show();
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                }
            });

            function escapeHtml(value) {
                if (value === null || value === undefined) return '';
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function resetPreview() {
                $('#sales_invoice_preview_iframe').attr('src', 'about:blank');
            }

            async function loadInvoicePdfPreview() {
                setPreviewModalFooterVisible(true);
                const previewBtn = document.getElementById('previewSalesInvoice');
                if (!previewBtn) return;
                const correction_of_sales_invoice_id = $('#correction_of_sales_invoice_id').val();
                const originalText = previewBtn ? previewBtn.innerHTML : null;
                const saveDraftBtn = document.getElementsByClassName('saveDraftSalesInvoice');
                const saveDraftBtnWasDisabled = saveDraftBtn ? saveDraftBtn.disabled : false;
                if (previewBtn) {
                    previewBtn.disabled = true;
                    previewBtn.innerHTML = 'Betöltés...';
                }
                if (saveDraftBtn) {
                    saveDraftBtn.disabled = true;
                }

                if (correction_of_sales_invoice_id) {
                    saveDraftBtn[1].style.display = 'none';
                } else {
                    saveDraftBtn[1].style.display = 'block';
                }

                try {
                    syncItemsJson();
                    const form = document.getElementById('salesInvoiceForm');
                    const formData = new FormData(form);

                    const resp = await fetch('{{ route('admin.documents.sales-invoices.preview-invoice-pdf') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    if (!resp.ok) {
                        let msg = 'Hiba történt a PDF előnézet generálásakor.';
                        try {
                            const json = await resp.json();
                            if (json?.message) msg = json.message;
                        } catch (e) {}
                        throw new Error(msg);
                    }

                    const blob = await resp.blob();
                    const blobUrl = URL.createObjectURL(blob);
                    $('#sales_invoice_preview_iframe').attr('src', blobUrl);
                    if (previewModal) previewModal.show();
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                } finally {
                    if (previewBtn) {
                        previewBtn.disabled = false;
                        if (originalText !== null) previewBtn.innerHTML = originalText;
                    }
                    if (saveDraftBtn) {
                        saveDraftBtn.disabled = saveDraftBtnWasDisabled;
                    }
                }
            }

            let partnerClientSearchDebounce = null;
            let partnerCustomerId = null;
            let selectedPartnerName = '';

            function clearPartnerClientResults() {
                $('#partner_select_results').empty();
            }

            function fillPartnerFromClient({ name = '', clientName = '', tax = '', email = '', phone = '', country = 'HU', zip = '', city = '', line = '', customerId = null } = {}) {
                partnerCustomerId = (customerId !== null && customerId !== '') ? Number(customerId) : null;
                const invoiceName = String(clientName || '').trim() || String(name || '').trim();
                selectedPartnerName = invoiceName;

                $('#partner_name').val(invoiceName);
                $('#partner_tax_number').val(tax || '');
                $('#partner_email').val(email || '');
                $('#partner_phone').val(phone || '');

                $('#partner_country').val(country || 'HU');
                $('#partner_zip_code').val(zip || '');
                $('#partner_city').val(city || '');
                $('#partner_address_line').val(line || '');
            }

            // Ha a felhasználó kézzel módosítja a nevet, a korábban kiválasztott vevő
            // kapcsolat (partner árazás) törlődik.
            $('#partner_name').on('input', function () {
                if (selectedPartnerName !== '' && ($(this).val() || '').trim() !== selectedPartnerName) {
                    partnerCustomerId = null;
                    selectedPartnerName = '';
                }
            });

            function showPartnerCreatePane(show) {
                $('#partnerSearchPane').toggle(!show);
                $('#partnerCreatePane').toggle(show);
                $('#partnerCreateToggleBtn').toggle(!show);
                $('#partnerCreateSaveBtn').toggle(show);
                $('#partnerCreateBackBtn').toggle(show);
            }

            $('#selectPartnerBtn').on('click', function () {
                showPartnerCreatePane(false);
                $('#partner_search_input').val('');
                clearPartnerClientResults();
                if (partnerSelectModal) partnerSelectModal.show();
            });

            if (partnerSelectModalDOM) {
                partnerSelectModalDOM.addEventListener('shown.bs.modal', function () {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    const lastBackdrop = backdrops.length ? backdrops[backdrops.length - 1] : null;
                    if (lastBackdrop) {
                        lastBackdrop.style.zIndex = '2095';
                    }
                    $('#partner_search_input').trigger('focus');
                });

                partnerSelectModalDOM.addEventListener('hidden.bs.modal', function () {
                    showPartnerCreatePane(false);
                    $('#partnerCreateForm')[0].reset();
                    $('#partner_search_input').val('');
                    clearPartnerClientResults();
                    clearNavTaxpayerResults();
                });
            }

            $('#partner_search_input').on('input', function () {
                const q = ($(this).val() || '').trim();
                clearTimeout(partnerClientSearchDebounce);
                clearPartnerClientResults();

                if (q.length < 2) {
                    return;
                }

                partnerClientSearchDebounce = setTimeout(() => {
                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/bizonylatok/partner-kereses?q=${encodeURIComponent(q)}`,
                        method: 'GET',
                        success: function (response) {
                            const clients = response?.partners || [];
                            const $list = $('#partner_select_results');
                            $list.empty();

                            if (clients.length) {
                                clients.forEach(c => {
                                    const name = c?.name || '';
                                    const taxNumber = c?.tax_number || '';
                                    const email = c?.email || '';
                                    const phone = c?.phone || '';
                                    const source = c?.source || '';
                                    const isPartnerCustomer = source === 'customer' && Boolean(c?.is_partner);
                                    const addresses = Array.isArray(c?.addresses) ? c.addresses : [];

                                    const isCustomer = source === 'customer';
                                    const sourceLabel = isCustomer ? 'Vásárló' : 'Ügyfél';
                                    const sourceBadge = `<span class="badge ${isCustomer ? 'bg-success' : 'bg-primary'} ms-1">${sourceLabel}</span>`;
                                    const partnerBadge = isPartnerCustomer
                                        ? '<span class="badge bg-warning text-dark ms-1">Szerelő</span>'
                                        : '';

                                    const initials = (name || email || '?')
                                        .split(' ')
                                        .filter(Boolean)
                                        .map(w => w[0])
                                        .slice(0, 2)
                                        .join('')
                                        .toUpperCase();
                                    const avatarClass = isCustomer ? 'bg-success' : 'bg-primary';

                                    const metaParts = [];
                                    if (taxNumber) metaParts.push(`<span class="me-3"><i class="fas fa-id-card me-1"></i>${escapeHtml(taxNumber)}</span>`);
                                    if (email) metaParts.push(`<span class="me-3"><i class="fas fa-envelope me-1"></i>${escapeHtml(email)}</span>`);
                                    if (phone) metaParts.push(`<span><i class="fas fa-phone me-1"></i>${escapeHtml(phone)}</span>`);
                                    const metaHtml = metaParts.length
                                        ? `<div class="small text-muted mt-1">${metaParts.join('')}</div>`
                                        : '';

                                    let addressesHtml = '';
                                    if (!addresses.length) {
                                        addressesHtml = `
                                            <button type="button" class="list-group-item list-group-item-action d-flex align-items-center client-no-address"
                                                data-name="${escapeHtml(name)}"
                                                data-tax="${escapeHtml(taxNumber)}"
                                                data-email="${escapeHtml(email)}"
                                                data-phone="${escapeHtml(phone)}"
                                                data-cid="${isPartnerCustomer ? c.id : ''}">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                <span class="small">Kiválasztás — nincs rögzített cím</span>
                                            </button>
                                        `;
                                    } else {
                                        addresses.forEach(a => {
                                            const addrText = `${a?.zip_code || ''} ${a?.city || ''}, ${a?.address_line || ''}`.trim();
                                            const addrClientName = a?.client_name || '';
                                            const addrTax = a?.tax_number || taxNumber;
                                            addressesHtml += `
                                                <button type="button" class="list-group-item list-group-item-action d-flex align-items-center client-address-item"
                                                    data-name="${escapeHtml(name)}"
                                                    data-client-name="${escapeHtml(addrClientName)}"
                                                    data-tax="${escapeHtml(addrTax)}"
                                                    data-email="${escapeHtml(email)}"
                                                    data-phone="${escapeHtml(phone)}"
                                                    data-country="${escapeHtml(a?.country || 'HU')}"
                                                    data-zip="${escapeHtml(a?.zip_code || '')}"
                                                    data-city="${escapeHtml(a?.city || '')}"
                                                    data-line="${escapeHtml(a?.address_line || '')}"
                                                    data-cid="${isPartnerCustomer ? c.id : ''}">
                                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                                    <span>
                                                        ${addrClientName ? `<span class="d-block fw-semibold">${escapeHtml(addrClientName)}</span>` : ''}
                                                        <span class="small ${addrClientName ? 'text-muted' : 'fw-semibold'}">${escapeHtml(addrText || 'Cím nélkül')}${a?.is_default ? ' (alapértelmezett)' : ''}</span>
                                                        ${addrTax ? `<span class="d-block small text-muted"><i class="fas fa-id-card me-1"></i>${escapeHtml(addrTax)}</span>` : ''}
                                                    </span>
                                                </button>
                                            `;
                                        });
                                    }

                                    $list.append(`
                                        <div class="card partner-card mb-2 shadow-sm">
                                            <div class="card-body p-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle ${avatarClass} text-white d-flex align-items-center justify-content-center fw-bold me-2" style="width:40px;height:40px;flex:0 0 40px;font-size:.9rem;">${escapeHtml(initials)}</div>
                                                    <div class="flex-grow-1" style="min-width:0;">
                                                        <div class="fw-bold">${escapeHtml(name || email || 'N/A')}${sourceBadge}${partnerBadge}</div>
                                                        ${metaHtml}
                                                    </div>
                                                </div>
                                                <div class="list-group list-group-flush mt-2">
                                                    ${addressesHtml}
                                                </div>
                                            </div>
                                        </div>
                                    `);
                                });
                            } else {
                                $list.append(`
                                    <div class="py-2 px-1 small text-muted">Nincs találat.</div>
                                `);
                            }
                        },
                        error: function () {
                            const $list = $('#partner_select_results');
                            $list.empty();
                            $list.append(`
                                <div class="py-2 px-1 small text-muted">A keresés sikertelen volt.</div>
                            `);
                        }
                    });
                }, 300);
            });

            $('#partner_select_results').on('click', '.client-address-item', function () {
                const $btn = $(this);
                fillPartnerFromClient({
                    name: $btn.data('name') || '',
                    clientName: $btn.data('clientName') || '',
                    tax: $btn.data('tax') || '',
                    email: $btn.data('email') || '',
                    phone: $btn.data('phone') || '',
                    country: $btn.data('country') || 'HU',
                    zip: $btn.data('zip') || '',
                    city: $btn.data('city') || '',
                    line: $btn.data('line') || '',
                    customerId: $btn.data('cid') || null,
                });
                if (partnerSelectModal) partnerSelectModal.hide();
            });

            $('#partner_select_results').on('click', '.client-no-address', function () {
                const $btn = $(this);
                fillPartnerFromClient({
                    name: $btn.data('name') || '',
                    clientName: $btn.data('clientName') || '',
                    tax: $btn.data('tax') || '',
                    email: $btn.data('email') || '',
                    phone: $btn.data('phone') || '',
                    country: 'HU',
                    zip: '',
                    city: '',
                    line: '',
                    customerId: $btn.data('cid') || null,
                });
                if (partnerSelectModal) partnerSelectModal.hide();
                setTimeout(() => {
                    $('#partner_zip_code').trigger('focus');
                }, 0);
            });

            $('#partnerCreateToggleBtn').on('click', function () {
                const q = ($('#partner_search_input').val() || '').trim();
                if (q !== '' && !$('#new_client_name').val()) {
                    $('#new_client_name').val(q);
                }
                showPartnerCreatePane(true);
                setTimeout(() => {
                    $('#new_client_name').trigger('focus');
                }, 0);
            });

            $('#partnerCreateBackBtn').on('click', function () {
                showPartnerCreatePane(false);
            });

            // --- NAV adózó-kereső (szamlazz.hu) az új vevő űrlaphoz ---
            let navTaxpayerDebounce = null;
            let navTaxpayerRequestSeq = 0;

            function clearNavTaxpayerResults() {
                $('#nav_taxpayer_results').empty();
            }

            function fillPartnerCreateFromTaxpayer(t) {
                const taxpayerName = t?.name || '';
                if (taxpayerName) {
                    $('#new_client_name').val(taxpayerName);
                    $('#new_client_client_name').val(taxpayerName);
                }
                if (t?.tax_number) {
                    $('#new_client_tax_number').val(t.tax_number);
                }
                $('#new_client_country').val(t?.country || 'HU');
                $('#new_client_zip_code').val(t?.zip_code || '');
                $('#new_client_city').val(t?.city || '');
                $('#new_client_address_line').val(t?.address_line || '');
            }

            function lookupNavTaxpayer() {
                const seq = ++navTaxpayerRequestSeq;
                const raw = ($('#new_client_tax_number').val() || '') + ' ' + ($('#new_client_name').val() || '');
                const digits = raw.replace(/\D/g, '').slice(0, 8);

                if (digits.length !== 8) {
                    return;
                }

                const companyId = $('#company_id').val() || '';
                $.ajax({
                    url: `${window.appConfig.APP_URL}admin/bizonylatok/adozo-kereses?q=${encodeURIComponent(digits)}&company_id=${encodeURIComponent(companyId)}`,
                    method: 'GET',
                    success: function (response) {
                        if (seq !== navTaxpayerRequestSeq) {
                            return;
                        }

                        const $results = $('#nav_taxpayer_results');
                        $results.empty();

                        if (response?.error) {
                            $results.append(`<div class="small text-muted"><i class="fas fa-info-circle me-1"></i>${escapeHtml(response.error)}</div>`);
                            return;
                        }

                        if (response?.invalid) {
                            $results.append(`<div class="small text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Érvénytelen adószám a NAV nyilvántartásban.</div>`);
                            return;
                        }

                        const taxpayers = response?.taxpayers || [];
                        if (!taxpayers.length) {
                            return;
                        }

                        const $list = $('<div class="list-group"></div>');
                        taxpayers.forEach(t => {
                            const addrText = `${t?.zip_code || ''} ${t?.city || ''}, ${t?.address_line || ''}`.trim();
                            const typeBadge = t?.address_type_label
                                ? `<span class="badge bg-secondary ms-1">${escapeHtml(t.address_type_label)}</span>`
                                : '';
                            const $btn = $(`
                                <button type="button" class="list-group-item list-group-item-action py-1 px-2 small nav-taxpayer-item">
                                    <div class="fw-semibold"><i class="fas fa-building me-1 text-primary"></i>${escapeHtml(t?.name || 'N/A')}${typeBadge}</div>
                                    <div class="text-muted">
                                        <span class="me-2"><i class="fas fa-id-card me-1"></i>${escapeHtml(t?.tax_number || '')}</span>
                                        ${addrText ? `<span><i class="fas fa-map-marker-alt me-1"></i>${escapeHtml(addrText)}</span>` : ''}
                                    </div>
                                </button>
                            `);
                            $btn.on('click', function () {
                                fillPartnerCreateFromTaxpayer(t);
                                clearNavTaxpayerResults();
                            });
                            $list.append($btn);
                        });
                        $results.append($list);
                    },
                });
            }

            function scheduleNavTaxpayerLookup() {
                clearTimeout(navTaxpayerDebounce);
                navTaxpayerDebounce = setTimeout(lookupNavTaxpayer, 500);
            }

            $('#new_client_name').on('input', function () {
                const digits = ($(this).val() || '').replace(/\D/g, '');
                if (digits.length >= 8) {
                    scheduleNavTaxpayerLookup();
                }
            });

            $('#new_client_tax_number').on('input', function () {
                const digits = ($(this).val() || '').replace(/\D/g, '');
                if (digits.length >= 8) {
                    scheduleNavTaxpayerLookup();
                } else {
                    clearNavTaxpayerResults();
                }
            });

            $('#partnerCreateSaveBtn').on('click', async function () {
                const form = document.getElementById('partnerCreateForm');
                if (!form) return;

                if (!form.reportValidity()) {
                    return;
                }

                const formData = new FormData(form);
                const saveBtn = $(this);
                const originalHtml = saveBtn.html();
                saveBtn.prop('disabled', true).html('Mentés...');

                try {
                    const resp = await fetch(`${window.appConfig.APP_URL}admin/ugyfelek`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });

                    if (!resp.ok) {
                        let msg = 'Hiba történt a vevő mentésekor.';
                        try {
                            const json = await resp.json();
                            if (json?.message) msg = json.message;
                            if (json?.errors && typeof json.errors === 'object') {
                                const first = Object.values(json.errors).flat()[0];
                                if (first) msg = String(first);
                            }
                        } catch (e) {}
                        throw new Error(msg);
                    }

                    const json = await resp.json().catch(() => ({}));
                    const client = json?.client || {};

                    fillPartnerFromClient({
                        name: client.name || formData.get('name') || '',
                        clientName: formData.get('address_client_name') || '',
                        tax: formData.get('address_tax_number') || '',
                        email: client.email || formData.get('email') || '',
                        phone: client.phone || formData.get('phone') || '',
                        country: formData.get('address_country') || 'HU',
                        zip: formData.get('address_zip_code') || '',
                        city: formData.get('address_city') || '',
                        line: formData.get('address_address_line') || '',
                    });

                    showToast(json?.message || 'Vevő mentve.', 'success');
                    if (partnerSelectModal) partnerSelectModal.hide();
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                } finally {
                    saveBtn.prop('disabled', false).html(originalHtml);
                }
            });
            const table = $('#adminTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.documents.sales-invoices.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'invoice_number' },
                    { data: 'partner_name' },
                    { data: 'payment_method' },
                    { data: 'issued_at' },
                    { data: 'due_at' },
                    { data: 'gross_total' },
                    { data: 'outstanding_amount' },
                    { data: 'created' },
                    { data: 'status' },
                    { data: 'payment_status' },
                    { data: 'invoice_type' },
                    { data: 'note' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            $('#addButton').on('click', async function () {
                resetForm('Új kimenő számla');
                const companyId = defaultCompanyId || $('#company_id').val();
                if (companyId) {
                    $('#company_id').val(companyId);
                }

                populateCompanyPrefixes($('#company_id').val(), '');


                modal.show();
            });

            $('#previewSalesInvoice').on('click', function () {
                loadInvoicePdfPreview();
            });

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Kimenő számla szerkesztése');

                const btnInvoiceId = String($(this).data('id') || '').trim();
                const tableApi = $('#adminTable').DataTable();
                let rowData = null;
                try {
                    let $tr = $(this).closest('tr');
                    if ($tr.hasClass('child')) {
                        $tr = $tr.prev();
                    }
                    rowData = tableApi.row($tr).data() || null;
                } catch (e) {
                    rowData = null;
                }

                const invoiceId = btnInvoiceId !== '' ? btnInvoiceId : String(rowData?.id || '').trim();
                if (!invoiceId) {
                    showToast('Nem található a bizonylat azonosítója.', 'danger');
                    return;
                }

                try {
                    const resp = await fetch(`{{ route('admin.documents.sales-invoices.show', ['id' => '__ID__']) }}`.replace('__ID__', String(invoiceId)), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (!resp.ok) {
                        let msg = 'Hiba történt a számla betöltésekor.';
                        try {
                            const json = await resp.json();
                            if (json?.message) msg = json.message;
                        } catch (e) {}
                        throw new Error(msg);
                    }

                    const json = await resp.json();
                    const invoice = json?.invoice || {};
                    const invoiceItems = Array.isArray(json?.items) ? json.items : [];

                    $('#invoice_id').val(invoice.id || invoiceId);
                    $('#correction_of_sales_invoice_id').val(invoice.correction_of_sales_invoice_id || '');

                    $('#company_id').val(invoice.company_id || defaultCompanyId);
                    populateCompanyPrefixes(invoice.company_id || defaultCompanyId, invoice.company_invoice_prefix_id || '');
                    $('#invoice_number').val(invoice.invoice_number || '');

                    $('#partner_name').val(invoice.partner_name || '');
                    $('#partner_tax_number').val(invoice.partner_tax_number || '');
                    $('#partner_country').val(invoice.partner_country || 'HU');
                    $('#partner_zip_code').val(invoice.partner_zip_code || '');
                    $('#partner_city').val(invoice.partner_city || '');
                    $('#partner_address_line').val(invoice.partner_address_line || '');
                    $('#partner_email').val(invoice.partner_email || '');
                    $('#partner_phone').val(invoice.partner_phone || '');

                    $('#payment_method').val(invoice.payment_method || 'bank_transfer');

                    if ($('#invoice_type').length) {
                        $('#invoice_type').val(invoice.invoice_type || 'normal');
                    }

                    $('#issued_at').val(invoice.issued_at || todayDate());
                    $('#fulfilled_at').val(invoice.fulfilled_at || todayDate());
                    $('#due_at').val(invoice.due_at || dueDateForPaymentMethod(invoice.payment_method || 'bank_transfer', todayDate()));

                    $('#currency').val(invoice.currency || 'HUF');
                    if ($('#prices_include_vat').length) {
                        $('#prices_include_vat').prop('checked', Boolean(invoice.prices_include_vat));
                    }

                    $('#status').val(invoice.status || 'draft');
                    $('#payment_status').val(invoice.payment_status || 'unpaid');

                    $('#note').val(invoice.note || '');
                    $('#note_for_document').val(invoice.note_for_document || '');

                    const pdfPath = invoice.pdf_path;
                    if (pdfPath) {
                        const src = `{{ route('admin.documents.sales-invoices.pdf', ['id' => '__ID__']) }}`.replace('__ID__', String(invoiceId));
                        $('#sales_invoice_preview_iframe').attr('src', src);
                    } else {
                        resetPreview();
                    }

                    items.splice(0, items.length);
                    invoiceItems.forEach(it => {
                        const warehouseId = it.warehouse_id ?? null;
                        const wh = warehouseId ? warehouses.find(w => String(w.id) === String(warehouseId)) : null;
                        const item = {
                            product_id: it.product_id ?? null,
                            image_url: it.image_url ?? null,
                            warehouse_id: warehouseId,
                            warehouse_name: wh?.name ?? '',
                            name: it.name ?? '',
                            quantity: Number(it.quantity ?? 0),
                            qty_step: 1,
                            unit_abbreviation: it.unit ?? '',
                            discount_percent: Number(it.discount_percent ?? 0),
                            vat_percent: Number(it.vat_percent ?? 0),
                            vat_code: it.vat_code || taxNameForValue(Number(it.vat_percent ?? 0)),
                            unit_net_price: Number(it.unit_net_price ?? 0),
                            unit_gross_price: Number(it.unit_gross_price ?? 0),
                            net_total: Number(it.net_total ?? 0),
                            vat_total: Number(it.vat_total ?? 0),
                            gross_total: Number(it.gross_total ?? 0),
                            note: it.note ?? '',
                        };

                        recalcRow(item);
                        items.push(item);
                    });

                    renderItems();
                    syncItemsJson();
                    $('#product_search').val('');
                    $('#product_search_results').empty();

                    modal.show();
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                }
            });

            modalDOM.addEventListener('hidden.bs.modal', function () {
                $('#product_search_results').empty();
                resetPreview();
                clearPartnerClientResults();
            });

            // Számla létrehozása
            $('#createSalesInvoice').on('click', function (e) {
                e.preventDefault();

                syncItemsJson();

                const formData = new FormData(document.getElementById('salesInvoiceForm'));
                formData.append('_token', csrfToken);

                const saveBtn = $('#createSalesInvoice');
                const originalSaveButtonHtml = saveBtn.html();
                saveBtn.html('Létrehozás...').prop('disabled', true);

                const saveDraftBtn = $('.saveDraftSalesInvoice');
                const originalSaveDraftButtonHtml = saveDraftBtn.length ? saveDraftBtn.html() : null;
                if (saveDraftBtn.length) saveDraftBtn.prop('disabled', true);

                const previewBtn = $('#previewSalesInvoice');
                const originalPreviewButtonHtml = previewBtn.length ? previewBtn.html() : null;
                if (previewBtn.length) previewBtn.prop('disabled', true);

                const cancelBtn = $('#salesInvoicePreviewModalFooter button[data-bs-dismiss="modal"]');
                if (cancelBtn.length) cancelBtn.prop('disabled', true);

                //const invoiceId = $('#invoice_id').val();

                let url = '{{ route('admin.documents.sales-invoices.store') }}';
                let method = 'POST';

                /*if (invoiceId) {
                    url = `${window.appConfig.APP_URL}admin/bizonylatok/kimeno-szamlak/${invoiceId}`;
                    formData.append('_method', 'PUT');
                }*/

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success(response) {
                        const savedId = response?.invoice?.id;
                        if (!savedId) {
                            showToast('Sikeres mentés, de hiányzik a bizonylat azonosítója.', 'warning');
                            table.ajax.reload(null, false);
                            saveBtn.html(originalSaveButtonHtml).prop('disabled', false);
                            if (previewBtn.length) previewBtn.html(originalPreviewButtonHtml).prop('disabled', false);
                            return;
                        }

                        const issueUrl = `{{ route('admin.documents.sales-invoices.issue-invoice-pdf', ['id' => '__ID__']) }}`.replace('__ID__', String(savedId));

                        syncItemsJson();
                        const issueForm = document.getElementById('salesInvoiceForm');
                        const issueFormData = new FormData(issueForm);

                        fetch(issueUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: issueFormData,
                        }).then(async (resp) => {
                            if (!resp.ok) {
                                let msg = 'Hiba történt a számla kiállításakor.';
                                try {
                                    const json = await resp.json();
                                    if (json?.message) msg = json.message;
                                } catch (e) {}
                                throw new Error(msg);
                            }

                            const blob = await resp.blob();
                            const blobUrl = URL.createObjectURL(blob);
                            $('#sales_invoice_preview_iframe').attr('src', blobUrl);

                            showToast('Számla kiállítva.', 'success');
                            table.ajax.reload(null, false);
                            modal.hide();
                            if (previewModal) previewModal.hide();

                        }).catch((err) => {
                            showToast(err?.message || 'Hiba!', 'danger');
                            table.ajax.reload(null, false);
                        }).finally(() => {
                            saveBtn.html(originalSaveButtonHtml).prop('disabled', false);
                            if (previewBtn.length) previewBtn.html(originalPreviewButtonHtml).prop('disabled', false);
                            if (saveDraftBtn.length) saveDraftBtn.html(originalSaveDraftButtonHtml).prop('disabled', false);
                            if (cancelBtn.length) cancelBtn.prop('disabled', false);
                        });
                    },
                    error(xhr) {
                        let msg = 'Hiba!';
                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');

                        saveBtn.html(originalSaveButtonHtml).prop('disabled', false);
                        if (previewBtn.length) previewBtn.html(originalPreviewButtonHtml).prop('disabled', false);
                        if (saveDraftBtn.length) saveDraftBtn.html(originalSaveDraftButtonHtml).prop('disabled', false);
                        if (cancelBtn.length) cancelBtn.prop('disabled', false);
                    },
                    complete: () => {}
                });
            });

            // Piszkozat mentése gomb megnyomása
            $('.saveDraftSalesInvoice').on('click', function (e) {
                e.preventDefault();
                saveDraftInvoice();
            });

            $('#adminTable').on('click', '.finances', async function () {
                const btnInvoiceId = String($(this).data('id') || '').trim();
                $('#sales_invoice_id').val(btnInvoiceId);
                const payments = await loadSalesPayments(btnInvoiceId);

                const $tbody = $('#salesPaymentsTableBody');
                $tbody.empty();

                if (!payments || payments.length === 0) {
                    $tbody.append(`
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Nincs rögzített befizetés.
                            </td>
                        </tr>
                    `);
                } else {
                    payments.forEach(payment => {
                        const paidAt = payment.paid_at
                            ? new Date(payment.paid_at).toLocaleDateString('hu-HU')
                            : '';

                        const amount = Number(payment.amount || 0).toLocaleString('hu-HU');

                        $tbody.append(`
                            <tr>
                                <td>${paidAt}</td>
                                <td>${amount} ${payment.currency || ''}</td>
                                <td>${payment.payment_method || '-'}</td>
                                <td>${payment.transaction_id || '-'}</td>
                                <td class="text-end">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger delete-payment"
                                        data-id="${payment.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }

                financesModal.show();
            });

            async function loadSalesPayments(invoice_id) {
                let url = `${window.appConfig.APP_URL}admin/bizonylatok/kimeno-szamlak/payments/${invoice_id}`;
                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    if (!response.ok) {
                        throw new Error('Hiba a befieztések lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Lekérdezési hiba:', error);
                    return [];
                }
            }

            $('#addSalesPayment').on('click', function () {
                const $tbody = $('#salesPaymentsTableBody');

                // Ha már van szerkesztés alatt álló új sor, ne lehessen még egyet hozzáadni
                if ($tbody.find('.new-payment-row').length) {
                    return;
                }

                $tbody.prepend(`
                    <tr class="new-payment-row">
                        <td>
                            <input
                                type="date"
                                class="form-control form-control-sm"
                                id="newPaymentDate"
                                value="${new Date().toISOString().slice(0, 10)}">
                        </td>

                        <td>
                            <input
                                type="number"
                                class="form-control form-control-sm"
                                id="newPaymentAmount"
                                min="0"
                                step="1"
                                placeholder="Összeg">
                        </td>

                        <td>
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                id="newPaymentMethod"
                                placeholder="Fizetési mód">
                        </td>

                        <td>
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                id="newPaymentTransactionId"
                                placeholder="Tranzakció azonosító">
                        </td>

                        <td class="text-end text-nowrap">
                            <button
                                type="button"
                                class="btn btn-sm btn-success save-payment"
                                title="Mentés">
                                <i class="fas fa-check"></i>
                            </button>

                            <button
                                type="button"
                                class="btn btn-sm btn-secondary cancel-new-payment"
                                title="Mégse">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `);

                $('#newPaymentAmount').trigger('focus');
            });

            $('#salesPaymentsTableBody').on('click', '.save-payment', async function () {
                const $row = $(this).closest('tr');

                const paidAt = $row.find('#newPaymentDate').val();
                const amount = $row.find('#newPaymentAmount').val();
                const paymentMethod = $row.find('#newPaymentMethod').val();
                const transactionId = $row.find('#newPaymentTransactionId').val();

                if (!paidAt) {
                    alert('A dátum megadása kötelező.');
                    return;
                }

                if (!amount || Number(amount) <= 0) {
                    alert('Az összeg megadása kötelező.');
                    return;
                }

                let url = `${window.appConfig.APP_URL}admin/bizonylatok/kimeno-szamlak/addpayments`;

                const currentSalesInvoiceId = $('#sales_invoice_id').val();

                try {
                    const payment = await $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            sales_invoice_id: currentSalesInvoiceId,
                            paid_at: paidAt,
                            amount: amount,
                            currency: 'HUF',
                            payment_method: paymentMethod || null,
                            transaction_id: transactionId || null,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $row.replaceWith(`
                        <tr>
                            <td>
                                ${new Date(payment.paid_at).toLocaleDateString('hu-HU')}
                            </td>
                            <td>
                                ${Number(payment.amount).toLocaleString('hu-HU')}
                                ${payment.currency || ''}
                            </td>
                            <td>${payment.payment_method || '-'}</td>
                            <td>${payment.transaction_id || '-'}</td>
                            <td class="text-end">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger delete-payment"
                                    data-id="${payment.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);

                } catch (xhr) {
                    alert(xhr.responseJSON?.message || 'A befizetés mentése sikertelen.');
                }
            });

            $('#salesPaymentsTableBody').on('click', '.delete-payment', async function () {
                const paymentId = $(this).data('id');

                if (!confirm('Biztosan törölni szeretnéd ezt a befizetést?')) {
                    return;
                }

                let url = `${window.appConfig.APP_URL}admin/bizonylatok/kimeno-szamlak/payments/${paymentId}`;

                try {
                    await $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    await refreshSalesPaymentsTable();

                } catch (xhr) {
                    alert(
                        xhr.responseJSON?.message ||
                        'A befizetés törlése sikertelen.'
                    );
                }
            });

            async function refreshSalesPaymentsTable() {

                const currentSalesInvoiceId = $('#sales_invoice_id').val();
                const $tbody = $('#salesPaymentsTableBody');

                try {
                    const payments = await loadSalesPayments(currentSalesInvoiceId);

                    $tbody.empty();

                    if (!payments || payments.length === 0) {
                        $tbody.append(`
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Nincs rögzített befizetés.
                                </td>
                            </tr>
                        `);

                        return;
                    }

                    payments.forEach(payment => {
                        const paidAt = payment.paid_at
                            ? new Date(payment.paid_at).toLocaleDateString('hu-HU')
                            : '-';

                        const amount = Number(payment.amount || 0).toLocaleString('hu-HU');

                        $tbody.append(`
                            <tr>
                                <td>${paidAt}</td>

                                <td>
                                    ${amount} ${payment.currency || ''}
                                </td>

                                <td>
                                    ${payment.payment_method || '-'}
                                </td>

                                <td>
                                    ${payment.transaction_id || '-'}
                                </td>

                                <td class="text-end">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger delete-payment"
                                        data-id="${payment.id}"
                                        title="Törlés">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });

                } catch (xhr) {
                    $tbody.html(`
                        <tr>
                            <td colspan="5" class="text-center text-danger">
                                A befizetések betöltése sikertelen.
                            </td>
                        </tr>
                    `);

                    console.error('Sales payments load error:', xhr);
                }
            }

            // Piszkozat mentése (létrehozás vagy frissítés szamlazó nélkül)
            function saveDraftInvoice() {
                syncItemsJson();

                const formData = new FormData(document.getElementById('salesInvoiceForm'));
                formData.append('_token', csrfToken);

                const saveBtn = $('#createSalesInvoice');
                const originalSaveButtonHtml = saveBtn.html();
                if (saveBtn.length) saveBtn.prop('disabled', true);

                const saveDraftBtn = $('.saveDraftSalesInvoice');
                const originalSaveDraftButtonHtml = saveDraftBtn.html();
                saveDraftBtn.html('Mentés...').prop('disabled', true);
                if (saveDraftBtn.length) saveDraftBtn.prop('disabled', true);

                const previewBtn = $('#previewSalesInvoice');
                const originalPreviewButtonHtml = previewBtn.length ? previewBtn.html() : null;
                if (previewBtn.length) previewBtn.prop('disabled', true);

                const invoiceId = $('#invoice_id').val();

                let url = '{{ route('admin.documents.sales-invoices.store') }}';
                let method = 'POST';

                if (invoiceId) {
                    url = `${window.appConfig.APP_URL}admin/bizonylatok/kimeno-szamlak/${invoiceId}`;
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success(response) {
                        const savedId = response?.invoice?.id;
                        if (!savedId) {
                            showToast('Sikeres mentés, de hiányzik a bizonylat azonosítója.', 'warning');
                            table.ajax.reload(null, false);
                            saveDraftBtn.html(originalSaveDraftButtonHtml).prop('disabled', false);
                            if (previewBtn.length) previewBtn.html(originalPreviewButtonHtml).prop('disabled', false);
                            return;
                        }

                        $('#invoice_id').val(savedId);

                        showToast('Sikeres mentés!', 'success');
                        table.ajax.reload(null, false);
                        modal.hide();
                        if (previewModal) previewModal.hide();
                        resetForm(null);

                        saveBtn.html(originalSaveButtonHtml).prop('disabled', false);
                        saveDraftBtn.html(originalSaveDraftButtonHtml).prop('disabled', false);
                        if (previewBtn.length) previewBtn.html(originalPreviewButtonHtml).prop('disabled', false);
                    },
                    error(xhr) {
                        let msg = 'Hiba!';
                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');

                        saveBtn.html(originalSaveButtonHtml).prop('disabled', false);
                        if (previewBtn.length) previewBtn.html(originalPreviewButtonHtml).prop('disabled', false);
                        if (saveDraftBtn.length) saveDraftBtn.html(originalSaveDraftButtonHtml).prop('disabled', false);
                    },
                    complete: () => {}
                });
            }

            const items = [];

            function formatMoney(value) {
                const n = Number(value);
                if (!Number.isFinite(n)) return '0';
                return String(Math.round(n));
            }

            function formatGroupedMoney(value) {
                const n = Number(value);
                if (!Number.isFinite(n)) return '0';
                return Math.round(n).toLocaleString('hu-HU');
            }

            function formatPercent(value) {
                const n = Number(value);
                if (!Number.isFinite(n)) return '0';
                return String(Math.round(n * 100) / 100);
            }

            function vatOptionsHtml(selectedValue) {
                const sel = Number(selectedValue);
                const selNum = Number.isFinite(sel) ? sel : 0;
                let found = false;

                let html = taxCategories.map(t => {
                    const v = Number(t.tax_value);
                    const isSel = Math.abs(v - selNum) < 0.0001;
                    if (isSel) found = true;
                    const name = String(t.tax_name ?? '').trim();
                    const label = `${Math.round(v)}%` + (name !== '' ? ` (${escapeHtml(name)})` : '');
                    return `<option value="${escapeHtml(String(t.tax_value))}" data-tax-name="${escapeHtml(name)}"${isSel ? ' selected' : ''}>${label}</option>`;
                }).join('');

                if (!found) {
                    html += `<option value="${selNum}" selected>${formatPercent(selNum)}%</option>`;
                }

                return html;
            }

            function taxNameForValue(v) {
                const num = Number(v);
                if (!Number.isFinite(num)) return '';
                const t = taxCategories.find(tc => Math.abs(Number(tc.tax_value) - num) < 0.0001);
                return t ? String(t.tax_name ?? '').trim() : '';
            }

            function recalcRow(item) {
                const qty = Number(item.quantity);
                const unitGross = Number(item.unit_gross_price);
                const discountPercent = Number(item.discount_percent);
                const vatPercent = Number(item.vat_percent);

                const q = Number.isFinite(qty) ? qty : 0;
                const ug = Number.isFinite(unitGross) ? unitGross : 0;
                const disc = Number.isFinite(discountPercent) ? discountPercent : 0;
                const vat = Number.isFinite(vatPercent) ? vatPercent : 0;

                const discountedUnitGross = ug * (1 - (disc / 100));
                const unitNet = vat > 0 ? (discountedUnitGross / (1 + vat / 100)) : discountedUnitGross;
                const unitVat = discountedUnitGross - unitNet;

                item.unit_net_price = Math.round(unitNet);
                item.net_total = Math.round(unitNet * q);
                item.gross_total = Math.round(discountedUnitGross * q);
                item.vat_total = item.gross_total - item.net_total;
            }

            const INSTALLATION_SUFFIX = 'BESZERELÉSSEL EGYÜTT';

            function renderItems() {
                const tbody = $('#invoice_items_table tbody');
                tbody.empty();

                items.forEach((item, idx) => {
                    const row = $(
                        `<tr data-idx="${idx}">
                            <td style="min-width: 320px;">
                                <div class="d-flex align-items-start gap-2">
                                    ${item.image_url
                                        ? `<img src="${escapeHtml(item.image_url)}" class="item-thumb" alt="">`
                                        : `<span class="item-thumb-placeholder"><i class="fas fa-image"></i></span>`}
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <input type="text" class="form-control form-control-sm item-name" value="${escapeHtml(item.name)}">
                                        <input type="text" class="form-control form-control-sm item-note mt-1" placeholder="Megjegyzés" value="${escapeHtml(item.note ?? '')}" style="font-size: 0.78rem;">
                                        <button type="button" class="item-installation-add btn btn-link btn-sm p-0 mt-1 d-none" style="font-size: 0.75rem;">+ BESZERELÉSSEL EGYÜTT</button>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>${escapeHtml(item.warehouse_name || '—')}</div>
                            </td>
                            <td style="width: 90px;">
                                <input type="number" min="${item.qty_step ?? 1}" step="${item.qty_step ?? 1}" class="form-control form-control-sm text-end item-qty" value="${item.quantity}">
                            </td>
                            <td class="text-center" style="width: 60px;">
                                <input type="text" class="form-control form-control-sm text-center item-unit-abbr" value="${escapeHtml(item.unit_abbreviation ?? '')}">
                            </td>
                            <td style="width: 90px;">
                                <input type="number" step="0.01" class="form-control form-control-sm text-end item-discount" value="${formatPercent(item.discount_percent)}">
                            </td>
                            <td style="width: 110px;">
                                <input type="number" step="1" class="form-control form-control-sm text-end item-unit" value="${formatMoney(item.unit_gross_price)}">
                            </td>
                            <td style="width: 100px;">
                                <select class="form-select form-select-sm item-vat-key">${vatOptionsHtml(item.vat_percent)}</select>
                            </td>
                            <td class="text-end" style="width: 95px;">
                                <span class="item-net">${formatGroupedMoney(item.net_total)} Ft</span>
                            </td>
                            <td class="text-end" style="width: 95px;">
                                <span class="item-vat">${formatGroupedMoney(item.vat_total)} Ft</span>
                            </td>
                            <td class="text-end" style="width: 100px;">
                                <span class="item-gross">${formatGroupedMoney(item.gross_total)} Ft</span>
                            </td>
                            <td class="text-center" style="width: 40px;">
                                <button type="button" class="btn btn-sm btn-outline-danger item-remove" title="Tétel törlése">&times;</button>
                            </td>
                        </tr>`
                    );

                    tbody.append(row);
                });
            }

            function syncItemsJson() {
                $('#items_json').val(JSON.stringify(items));
            }

            function addProductAsItem(product) {
                const existing = items.find(i => i.product_id === product.id);
                if (existing) {
                    const step = Number(existing.qty_step);
                    const add = Number.isFinite(step) && step > 0 ? step : 1;
                    existing.quantity = Number(existing.quantity) + add;
                    recalcRow(existing);
                    renderItems();
                    syncItemsJson();
                    return;
                }

                const unitPrice = product.effective_gross_price ?? product.gross_price ?? 0;
                const vatPercent = product.tax_value ?? 0;
                const unitAbbrev = product.unit_abbreviation ?? '';
                const qtyStep = (() => {
                    const q = Number(product.unit_qty);
                    return Number.isFinite(q) && q > 1 ? q : 1;
                })();

                const item = {
                    product_id: product.id,
                    image_url: product.image_url ?? null,
                    warehouse_id: product.warehouse_id,
                    warehouse_name: product.warehouse_name,
                    name: product.title,
                    quantity: qtyStep,
                    qty_step: qtyStep,
                    unit_abbreviation: unitAbbrev,
                    discount_percent: 0,
                    vat_percent: vatPercent,
                    vat_code: taxNameForValue(vatPercent),
                    unit_net_price: 0,
                    unit_gross_price: Math.round(Number(unitPrice) || 0),
                    net_total: 0,
                    vat_total: 0,
                    gross_total: 0,
                    note: '',
                };

                recalcRow(item);
                items.push(item);
                renderItems();
                syncItemsJson();
            }

            let searchTimeout = null;

            (function loadFavoriteProducts() {
                $.ajax({
                    url: `${window.appConfig.APP_URL}admin/termekek/search?favorites=1`,
                    method: 'GET',
                    success: function (resp) {
                        const favorites = resp?.products ?? [];
                        const select = $('#favorite_products');

                        select.find('option:not(:first)').remove();

                        favorites.forEach(p => {
                            select.append(
                                `<option value="${p.id}">${escapeHtml(p.title)}</option>`
                            );
                        });
                    }
                });
            })();

            $('#favorite_products').on('change', function () {
                if (!$(this).val()) return;
                const title = $(this).find('option:selected').text();
                $('#product_search').val(title).trigger('input').trigger('focus');
                $(this).val('');
            });

            $('#product_search').on('input', function () {
                const q = $(this).val().trim();

                clearTimeout(searchTimeout);

                if (q.length < 2) {
                    $('#product_search_results').empty();
                    return;
                }

                searchTimeout = setTimeout(() => {
                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/termekek/search?q=${encodeURIComponent(q)}&show_partner_prices=1${partnerCustomerId ? '&customer_id=' + encodeURIComponent(partnerCustomerId) : ''}`,
                        method: 'GET',

                        success: function (resp) {
                            const results = resp?.products ?? [];
                            const container = $('#product_search_results');

                            container.empty();

                            results.forEach(p => {
                                const unitQty = Number(p.unit_qty);

                                const unitText =
                                    (p.unit_abbreviation || p.unit_name)
                                        ? escapeHtml(p.unit_abbreviation || p.unit_name)
                                        : '';

                                const packagingLabel =
                                    Number.isFinite(unitQty) && unitQty > 1
                                        ? ` (kiszerelés: ${escapeHtml(unitQty)}${unitText ? ' ' + unitText : ''})`
                                        : '';

                                const warehouses = Array.isArray(p.warehouses)
                                    ? p.warehouses
                                    : [];

                                const productItem = $(
                                    `<div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    ${p.image_url ? `<img src="${escapeHtml(p.image_url)}" class="item-thumb" alt="">` : ''}
                                    <div>
                                        <strong>${escapeHtml(p.title)}</strong>
                                        ${packagingLabel}
                                    </div>
                                </div>

                                <div class="text-muted">
                                    ${formatMoney(p.effective_gross_price ?? p.gross_price ?? 0)} HUF
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-1 mt-1 product-warehouses">
                            </div>
                        </div>`
                                );

                                const warehousesContainer =
                                    productItem.find('.product-warehouses');

                                const isStockTracked =
                                    p.stock_tracking !== false &&
                                    p.stock_tracking !== 0 &&
                                    p.stock_tracking !== '0';

                                if (!isStockTracked) {
                                    const addButton = $(
                                        `<button
                                type="button"
                                class="btn btn-outline-secondary text-dark"
                                style="font-size: 0.7rem; padding: 0.05rem 0.45rem; line-height: 1.4;"
                            >
                                Hozzáadás
                            </button>`
                                    );

                                    addButton.on('click', function (e) {
                                        e.preventDefault();
                                        e.stopPropagation();

                                        /* Nem követett készletű termék: raktár nélkül megy be. */
                                        addProductAsItem({
                                            ...p,
                                            warehouse_id: null,
                                            warehouse_name: '',
                                            available_quantity: null,
                                        });

                                        $('#product_search').val('');
                                        $('#product_search_results').empty();
                                        $('#product_search').trigger('focus');
                                    });

                                    warehousesContainer.append(addButton);
                                } else {
                                    if (warehouses.length === 0) {
                                        warehousesContainer.append(
                                            `<span class="badge text-bg-secondary">
                                nincs készletinformáció
                            </span>`
                                        );
                                    }

                                    warehouses.forEach(warehouse => {
                                    const quantity = Number(warehouse.quantity ?? 0);

                                    const isAvailable =
                                        Number.isFinite(quantity) &&
                                        quantity > 0;

                                    const quantityText =
                                        Number.isFinite(quantity)
                                            ? String(quantity)
                                            : '0';

                                    const warehouseButton = $(
                                        `<button
                                type="button"
                                class="btn btn-outline-secondary ${isAvailable ? 'text-dark' : ''}"
                                style="font-size: 0.7rem; padding: 0.05rem 0.45rem; line-height: 1.4;"
                                ${
                                            !isAvailable
                                                ? 'disabled aria-disabled="true"'
                                                : ''
                                        }
                            >
                                ${escapeHtml(warehouse.name)}
                                <span class="ms-1 text-muted">
                                    (${escapeHtml(quantityText)})
                                </span>
                            </button>`
                                    );

                                    if (isAvailable) {
                                        warehouseButton.on('click', function (e) {
                                            e.preventDefault();
                                            e.stopPropagation();

                                            /*
                                             * A termékhez hozzárakjuk,
                                             * hogy melyik raktárból választották.
                                             */
                                            addProductAsItem({
                                                ...p,
                                                warehouse_id: warehouse.id,
                                                warehouse_name: warehouse.name,
                                                available_quantity: quantity,
                                            });

                                            $('#product_search').val('');
                                            $('#product_search_results').empty();
                                            $('#product_search').trigger('focus');
                                        });
                                    }

                                        warehousesContainer.append(warehouseButton);
                                    });
                                }

                                container.append(productItem);
                            });
                        },

                        error: function () {
                            $('#product_search_results').empty();
                        }
                    });
                }, 250);
            });

            $('#invoice_items_table').on('input change', '.item-qty, .item-unit, .item-discount, .item-vat-key, .item-name, .item-unit-abbr, .item-note', function () {
                const tr = $(this).closest('tr');
                const idx = Number(tr.data('idx'));
                const item = items[idx];
                if (!item) return;

                const qtyInput = tr.find('.item-qty');
                const qtyRaw = Number(qtyInput.val());
                const unit = Number(tr.find('.item-unit').val());
                const discount = Number(tr.find('.item-discount').val());
                const vat = Number(tr.find('.item-vat-key').val());
                const name = tr.find('.item-name').val();
                const unitAbbr = tr.find('.item-unit-abbr').val();

                const minQty = Number(item.qty_step);
                const min = Number.isFinite(minQty) && minQty > 1 ? minQty : 1;
                const qty = Number.isFinite(qtyRaw) ? qtyRaw : min;
                const clampedQty = qty < min ? min : qty;
                if (clampedQty !== qtyRaw) {
                    qtyInput.val(clampedQty);
                }

                item.quantity = clampedQty;
                item.unit_gross_price = Number.isFinite(unit) ? unit : 0;
                item.discount_percent = Number.isFinite(discount) ? discount : 0;
                item.vat_percent = Number.isFinite(vat) ? vat : 0;
                item.vat_code = String(tr.find('.item-vat-key option:selected').attr('data-tax-name') ?? '');
                const itemName = String(name ?? '').trim();
                item.name = itemName || item.name;

                // „BESZERELÉSSEL EGYÜTT” felirat felajánlása gépelés közben
                const nameHasSuffix = String(item.name).trim().toUpperCase().endsWith(INSTALLATION_SUFFIX);
                tr.find('.item-installation-add').toggleClass('d-none', itemName === '' || nameHasSuffix);

                item.unit_abbreviation = String(unitAbbr ?? '').trim();
                item.note = String(tr.find('.item-note').val() ?? '');
                recalcRow(item);
                tr.find('.item-net').text(`${formatGroupedMoney(item.net_total)} Ft`);
                tr.find('.item-vat').text(`${formatGroupedMoney(item.vat_total)} Ft`);
                tr.find('.item-gross').text(`${formatGroupedMoney(item.gross_total)} Ft`);
                syncItemsJson();
            });

            $('#invoice_items_table').on('click', '.item-installation-add', function () {
                const tr = $(this).closest('tr');
                const idx = Number(tr.data('idx'));
                const item = items[idx];
                if (!item) return;

                const nameInput = tr.find('.item-name');
                const current = String(nameInput.val() ?? '').trim();
                if (current !== '' && !current.toUpperCase().endsWith(INSTALLATION_SUFFIX)) {
                    const newName = `${current} ${INSTALLATION_SUFFIX}`;
                    nameInput.val(newName);
                    item.name = newName;
                    syncItemsJson();
                }
                $(this).addClass('d-none');
            });

            $('#invoice_items_table').on('click', '.item-remove', function () {
                const tr = $(this).closest('tr');
                const idx = Number(tr.data('idx'));
                if (!Number.isFinite(idx)) return;
                items.splice(idx, 1);
                renderItems();
                syncItemsJson();
            });

            (function initSalesInvoiceSplitResize() {
                const split = document.getElementById('salesInvoiceModal_split');
                if (!split) return;

                const leftHandle = split.querySelector('[data-resizer="left"]');
                const rightHandle = split.querySelector('[data-resizer="right"]');
                if (!leftHandle && !rightHandle) return;

                function pxToPercent(px) {
                    const w = split.getBoundingClientRect().width || 1;
                    return (px / w) * 100;
                }

                function setPanes(leftPercent, midPercent, rightPercent) {
                    split.style.setProperty('--pane-left', `${leftPercent}%`);
                    split.style.setProperty('--pane-mid', `${midPercent}%`);
                    split.style.setProperty('--pane-right', `${rightPercent}%`);
                }

                function currentPanes() {
                    const style = getComputedStyle(split);
                    const l = parseFloat(style.getPropertyValue('--pane-left')) || 25;
                    const m = parseFloat(style.getPropertyValue('--pane-mid')) || 50;
                    const r = parseFloat(style.getPropertyValue('--pane-right')) || 25;
                    return { l, m, r };
                }

                function startDrag(which, startEvent) {
                    if (window.innerWidth < 992) return; // lg breakpoint
                    startEvent.preventDefault();

                    const startX = startEvent.clientX;
                    const { l, m, r } = currentPanes();
                    const min = 15; // percent

                    document.body.style.userSelect = 'none';

                    function onMove(e) {
                        const dx = e.clientX - startX;
                        const delta = pxToPercent(dx);

                        if (which === 'left') {
                            let nl = l + delta;
                            let nm = m - delta;
                            if (nl < min) { nm -= (min - nl); nl = min; }
                            if (nm < min) { nl -= (min - nm); nm = min; }
                            if (nl < min) nl = min;
                            if (nm < min) nm = min;
                            setPanes(nl, nm, r);
                            return;
                        }

                        if (which === 'right') {
                            let nm = m + delta;
                            let nr = r - delta;
                            if (nr < min) { nm -= (min - nr); nr = min; }
                            if (nm < min) { nr -= (min - nm); nm = min; }
                            if (nr < min) nr = min;
                            if (nm < min) nm = min;
                            setPanes(l, nm, nr);
                        }
                    }

                    function onUp() {
                        document.body.style.userSelect = '';
                        window.removeEventListener('mousemove', onMove);
                        window.removeEventListener('mouseup', onUp);
                    }

                    window.addEventListener('mousemove', onMove);
                    window.addEventListener('mouseup', onUp);
                }

                if (leftHandle) leftHandle.addEventListener('mousedown', (e) => startDrag('left', e));
                if (rightHandle) rightHandle.addEventListener('mousedown', (e) => startDrag('right', e));
            })();

            function todayDate() {
                const d = new Date();
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            }

            function addDays(yyyyMmDd, days) {
                const [y, m, d] = String(yyyyMmDd).split('-').map(Number);
                const date = new Date(y, (m || 1) - 1, d || 1);
                date.setDate(date.getDate() + Number(days || 0));
                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const dd = String(date.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            }

            function dueDateForPaymentMethod(paymentMethod, baseDate) {
                const base = baseDate || todayDate();
                return ['cash', 'credit_card'].includes(String(paymentMethod)) ? base : addDays(base, 8);
            }

            $(document).on('change', '#payment_method', function () {
                $('#due_at').val(dueDateForPaymentMethod($(this).val(), todayDate()));
            });

            function resetForm(title = null) {
                $('#salesInvoiceForm')[0].reset();
                $('#salesInvoiceModalLabel').text(title);
                $('#invoice_id').val('');
                $('#correction_of_sales_invoice_id').val('');

                if (defaultCompanyId) {
                    $('#company_id').val(defaultCompanyId);
                }

                populateCompanyPrefixes($('#company_id').val(), '');

                $('#currency').val('HUF');
                $('#partner_country').val('HU');
                $('#issued_at').val(todayDate());
                $('#fulfilled_at').val(todayDate());
                $('#due_at').val(dueDateForPaymentMethod($('#payment_method').val(), todayDate()));
                $('#status').val('draft');
                $('#payment_status').val('unpaid');
                if ($('#prices_include_vat').length) {
                    $('#prices_include_vat').prop('checked', true);
                }
                $('#invoice_number').val('');

                resetPreview();

                partnerCustomerId = null;
                selectedPartnerName = '';

                items.splice(0, items.length);
                renderItems();
                syncItemsJson();
                $('#product_search').val('');
                $('#product_search_results').empty();
            }
        });
    </script>
@endsection
