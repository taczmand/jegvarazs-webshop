@extends('layouts.admin')

@section('content')

    <style>
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
            <h2 class="color-dark-blue mb-0">Ügyvitel / Bizonylatok / Bevételezések</h2>
            @if(auth('admin')->user()->can('create-goods-receipt'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új bevételezés</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-goods-receipts'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Bizonylatszám" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Partner" class="filter-input form-control" data-column="3">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="8">
                            <option value="">Állapot (összes)</option>
                            <option value="draft">Piszkozat</option>
                            <option value="posted">Kész</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Bizonylatszám</th>
                        <th>Szállítói bizonylat</th>
                        <th>Partner</th>
                        <th>Bevételezés dátuma</th>
                        <th>Nettó</th>
                        <th>ÁFA</th>
                        <th>Bruttó</th>
                        <th>Állapot</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Nincs jogosultságod a bevételezések megtekintéséhez.
                </div>
            @endif
        </div>
    </div>


    <x-admin.document-modal id="goodsReceiptModal" title="Bevételezés" form-id="goodsReceiptForm" save-button-id="saveDraftGoodsReceipt" pane-left="40%" pane-mid="60%">
        <x-slot:left>
            <input type="hidden" id="goods_receipt_id" name="id">
            <input type="hidden" id="goods_receipt_status" name="status" value="">

            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Kiállító adatai</legend>

                <div class="mb-2">
                    <label for="company_id" class="form-label">Cég*</label>
                    <select class="form-select" id="company_id" name="company_id" required>
                        @foreach(($companies ?? []) as $c)
                            <option value="{{ $c->id }}" @selected(isset($defaultCompanyId) && (int) $c->id === (int) $defaultCompanyId)>
                                {{ $c->name }}@if($c->is_default) (alapértelmezett)@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label for="warehouse_id" class="form-label">Raktár*</label>
                    <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                        @foreach(($warehouses ?? []) as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label for="supplier_document_number" class="form-label">Szállítói bizonylatszám</label>
                    <input type="text" class="form-control" id="supplier_document_number" name="supplier_document_number">
                </div>

                <div class="mb-2">
                    <label for="received_at" class="form-label">Bevételezés dátuma</label>
                    <input type="datetime-local" class="form-control" id="received_at" name="received_at" max="2099-12-31T23:59">
                </div>
            </fieldset>

            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Partner</legend>

                <div class="row g-2">
                    <div class="col-12">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100" id="selectPartnerBtn">
                            <i class="fas fa-user-tag me-1"></i> Partner kiválasztása
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
                    <div class="col-12">
                        <label for="partner_country" class="form-label">Ország</label>
                        <select class="form-select" id="partner_country" name="partner_country">
                            @foreach(config('countries') as $code => $name)
                                <option value="{{ $code }}" {{ $code === 'HU' ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12 col-md-3">
                        <label for="partner_zip_code" class="form-label">Irányítószám</label>
                        <input type="text" class="form-control" id="partner_zip_code" name="partner_zip_code">
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="partner_city" class="form-label">Város</label>
                        <input type="text" class="form-control" id="partner_city" name="partner_city">
                    </div>
                    <div class="col-12 col-md-5">
                        <label for="partner_address_line" class="form-label">Cím</label>
                        <input type="text" class="form-control" id="partner_address_line" name="partner_address_line">
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12 col-md-6">
                        <label for="partner_email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="partner_email" name="partner_email">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="partner_phone" class="form-label">Telefon</label>
                        <input type="text" class="form-control" id="partner_phone" name="partner_phone">
                    </div>
                </div>
            </fieldset>

            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Megjegyzések</legend>

                <div class="mb-2">
                    <label for="note_for_document" class="form-label">Megjegyzés a bizonylatra</label>
                    <textarea class="form-control" id="note_for_document" name="note_for_document" rows="2"></textarea>
                </div>
                <div class="mb-0">
                    <label for="note" class="form-label">Megjegyzés</label>
                    <textarea class="form-control" id="note" name="note" rows="2"></textarea>
                </div>
            </fieldset>
        </x-slot:left>

        <x-slot:middle>
            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Tételek</legend>

                <div class="mb-2">
                    <label class="form-label" for="product_search">Termék keresés</label>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" id="product_search" autocomplete="off" placeholder="Kezdj el gépelni..." style="flex: 1;">
                        <button type="button" class="btn btn-outline-primary btn-sm text-nowrap" id="openQuickProductModal" title="Új termék rögzítése">+ Új termék</button>
                    </div>
                    <div id="product_search_results" class="list-group w-100" style="z-index: 1100; display:block; max-height: 260px; overflow-y: auto;"></div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" id="items_table">
                        <thead>
                        <tr>
                            <th style="width: 34%;">Megnevezés</th>
                            <th style="width: 10%;" class="text-end">Menny.</th>
                            <th style="width: 8%;">Mee.</th>
                            <th style="width: 12%;" class="text-end">Egységár</th>
                            <th style="width: 8%;" class="text-end">ÁFA%</th>
                            <th style="width: 12%;" class="text-end">Nettó</th>
                            <th style="width: 12%;" class="text-end">Bruttó</th>
                            <th style="width: 4%;"></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <input type="hidden" id="items_json" name="items_json" value="[]">
            </fieldset>
        </x-slot:middle>

        <!--<x-slot:right>
            <div class="d-flex flex-column h-100" style="min-height: 0;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold">PDF előnézet</div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="previewGoodsReceipt">Előnézet</button>
                </div>
                <div class="border rounded flex-grow-1" style="min-height: 0; overflow: hidden;">
                    <iframe id="goods_receipt_preview_iframe" src="about:blank" style="width:100%; height:100%; border:0;"></iframe>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-primary w-100" id="issueGoodsReceiptPdf">PDF kiállítás + készlet növelés</button>
                </div>
            </div>
        </x-slot:right>-->

        <x-slot:footer>
            <!--<button type="button" class="btn btn-outline-secondary" id="openGoodsReceiptPdf" style="display:none;">Megnyitás PDF</button>-->
            <button type="button" class="btn btn-outline-primary" id="previewGoodsReceipt">Előnézet</button>
            <button type="button" class="btn btn-primary saveDraftGoodsReceipt">Piszkozat mentése</button>
        </x-slot:footer>
    </x-admin.document-modal>


    <!-- Gyors termék létrehozás -->
    <div class="modal fade" id="quickProductModal" tabindex="-1" aria-hidden="true" style="z-index: 2200;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Új termék rögzítése</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <form id="quickProductForm" novalidate>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="quickProductErrors"></div>
                        <div class="mb-2">
                            <label class="form-label">Megnevezés *</label>
                            <input type="text" class="form-control form-control-sm" name="title" required maxlength="255">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">Bruttó ár (Ft) *</label>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="gross_price" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">ÁFA *</label>
                                <select class="form-select form-select-sm" name="tax_id" id="quickProductTax" required></select>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Partneri bruttó ár (Ft)</label>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="partner_gross_price" placeholder="">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Mértékegység</label>
                                <select class="form-select form-select-sm" name="unit_id" id="quickProductUnit">
                                    <option value="">—</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="stock_tracking" value="1" id="quickProductStockTracking" checked>
                            <label class="form-check-label" for="quickProductStockTracking">Készletkövetés</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Mégse</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="quickProductSubmit">Létrehozás és hozzáadás</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="goodsReceiptPreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 2000;">
        <div class="modal-dialog modal-fullscreen m-0 p-0">
            <div class="modal-content" style="height: 100vh; border-radius: 0; display: flex; flex-direction: column;">
                <div class="modal-header">
                    <h5 class="modal-title">Bevételezés PDF előnézet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <div class="modal-body p-0" style="flex: 1 1 auto;">
                    <iframe id="goods_receipt_preview_iframe_modal" src="about:blank" style="width: 100%; height: 100%; border: 0; display:block;"></iframe>
                </div>
                <div class="modal-footer" id="goodsReceiptPreviewModalFooter">
                    <button type="button" class="btn btn-success" id="createGoodsReceipt">
                        Bizonylat létrehozása
                    </button>

                    <button type="button" class="btn btn-primary saveDraftGoodsReceipt">
                        Piszkozat mentése
                    </button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Mégse
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modális ablak partner kiválasztására / létrehozására -->
    <div class="modal fade admin-modal-soft" id="partnerSelectModal" tabindex="-1" aria-labelledby="partnerSelectModalLabel" aria-hidden="true" style="z-index: 2100;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-custom">
                    <h5 class="modal-title" id="partnerSelectModalLabel">Partner kiválasztása</h5>
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
                        <i class="fas fa-plus me-1"></i> Új partner létrehozása
                    </button>
                    <button type="button" class="btn btn-primary" id="partnerCreateSaveBtn" style="display:none;">
                        Partner mentése és kiválasztása
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script type="module">

        const companies = @json($companies ?? []);
        const warehouses = @json($warehouses ?? []);
        const defaultCompanyId = @json($defaultCompanyId ?? null);

        const modalDOM = document.getElementById('goodsReceiptModal');
        const modal = new bootstrap.Modal(modalDOM);
        const previewModalDOM = document.getElementById('goodsReceiptPreviewModal');
        const previewModal = previewModalDOM ? new bootstrap.Modal(previewModalDOM) : null;
        const partnerSelectModalDOM = document.getElementById('partnerSelectModal');
        const partnerSelectModal = partnerSelectModalDOM ? new bootstrap.Modal(partnerSelectModalDOM) : null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        $(document).ready(function() {

            function escapeHtml(value) {
                if (value === null || value === undefined) return '';
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function todayDate() {
                const d = new Date();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${d.getFullYear()}-${m}-${day}`;
            }

            function nowDateTimeLocal() {
                const d = new Date();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                const hh = String(d.getHours()).padStart(2, '0');
                const mm = String(d.getMinutes()).padStart(2, '0');
                return `${d.getFullYear()}-${m}-${day}T${hh}:${mm}`;
            }

            function setPreviewModalFooterVisible(isVisible = true) {
                const footer = document.getElementById('goodsReceiptPreviewModalFooter');
                if (!footer) return;
                footer.style.display = isVisible ? '' : 'none';
            }

            function resetPreview() {
                $('#goods_receipt_preview_iframe').attr('src', 'about:blank');
                $('#goods_receipt_preview_iframe_modal').attr('src', 'about:blank');
            }

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

            const items = [];

            function fmtMoney(v) {
                const n = Number(v || 0);
                if (!Number.isFinite(n)) return '0';
                return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            }

            function calcRow(row) {
                const qty = Number(row.quantity || 0);
                const unitNet = Number(row.unit_net_price || 0);
                const vat = Number(row.vat_percent || 0);

                const net = Math.round(unitNet * qty);
                const vatVal = Math.round(net * (vat / 100));
                const gross = net + vatVal;

                return { net, gross };
            }

            function renderItems() {
                const $tbody = $('#items_table tbody');
                $tbody.empty();

                items.forEach((row, idx) => {
                    const { net, gross } = calcRow(row);
                    const qtyVal = (row.quantity ?? 1);

                    $tbody.append(`
                        <tr data-idx="${idx}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    ${row.image_url
                                        ? `<img src="${escapeHtml(row.image_url)}" class="item-thumb" alt="">`
                                        : `<span class="item-thumb-placeholder"><i class="fas fa-image"></i></span>`}
                                    <div class="fw-semibold">${escapeHtml(row.name || '')}</div>
                                </div>
                            </td>
                            <td class="text-end">
                                <input type="number" step="any" min="0" class="form-control form-control-sm item-qty" value="${escapeHtml(qtyVal)}">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm item-unit" value="${escapeHtml(row.unit || 'db')}">
                            </td>
                            <td class="text-end">
                                <input type="number" step="1" class="form-control form-control-sm item-unit-net" value="${escapeHtml(row.unit_net_price ?? 0)}">
                            </td>
                            <td class="text-end">
                                <input type="number" step="1" class="form-control form-control-sm item-vat" value="${escapeHtml(row.vat_percent ?? 0)}">
                            </td>
                            <td class="text-end item-net-cell">${fmtMoney(net)}</td>
                            <td class="text-end item-gross-cell">${fmtMoney(gross)}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger item-remove">×</button>
                            </td>
                        </tr>
                    `);
                });

                syncItemsJson();
            }

            function syncItemsJson() {
                $('#items_json').val(JSON.stringify(items));
            }

            function addQuickProductAsItem(p) {
                const vatPercent = Number(p.tax_value ?? 0);
                const gross = Number(p.effective_gross_price ?? p.gross_price ?? 0);
                const unitNet = vatPercent > 0 ? gross / (1 + vatPercent / 100) : gross;
                const packQty = Number(p.unit_qty || 0);
                const defaultQty = Number.isFinite(packQty) && packQty > 0 ? packQty : 1;

                items.push({
                    product_id: p.id,
                    image_url: p.image_url || null,
                    name: p.title,
                    unit: p.unit_abbreviation || p.unit_name || 'db',
                    unit_qty: packQty,
                    quantity: defaultQty,
                    unit_net_price: Number.isFinite(unitNet) ? Math.round(unitNet) : 0,
                    vat_percent: vatPercent,
                });
                renderItems();
                syncItemsJson();
                scheduleDraftAutoSave();
            }

            let quickProductMetaLoaded = false;

            function populateQuickProductMeta() {
                if (quickProductMetaLoaded) return;
                $.ajax({
                    url: `${window.appConfig.APP_URL}admin/termekek/meta`,
                    method: 'GET',
                    success: function (resp) {
                        quickProductMetaLoaded = true;
                        const meta = resp.original ?? resp;

                        const taxSelect = $('#quickProductTax');
                        taxSelect.empty();
                        (meta.taxes ?? []).forEach(t => {
                            taxSelect.append(`<option value="${t.id}" data-tax-value="${t.tax_value}">${escapeHtml(t.tax_name)} (${Number(t.tax_value)}%)</option>`);
                        });
                        const defaultTax = (meta.taxes ?? []).find(t => Number(t.tax_value) === 27);
                        if (defaultTax) taxSelect.val(String(defaultTax.id));

                        const unitSelect = $('#quickProductUnit');
                        unitSelect.find('option:not(:first)').remove();
                        (meta.units ?? []).forEach(u => {
                            if (u.active === false || u.active === 0 || u.active === '0') return;
                            unitSelect.append(`<option value="${u.id}">${escapeHtml(u.name)} (${escapeHtml(u.abbreviation)})</option>`);
                        });
                    }
                });
            }

            $('#openQuickProductModal').on('click', function () {
                $('#quickProductForm')[0].reset();
                $('#quickProductErrors').addClass('d-none').empty();
                populateQuickProductMeta();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('quickProductModal')).show();
            });

            $('#quickProductForm').on('submit', function (e) {
                e.preventDefault();
                const form = this;
                if (!form.reportValidity()) return;

                const fd = new FormData(form);
                fd.append('_token', csrfToken);

                $('#quickProductSubmit').prop('disabled', true);
                $.ajax({
                    url: `${window.appConfig.APP_URL}admin/termekek/quick`,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function (resp) {
                        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('quickProductModal'));
                        if (modalInstance) modalInstance.hide();
                        if (resp?.product) addQuickProductAsItem(resp.product);
                        form.reset();
                    },
                    error: function (xhr) {
                        const errs = xhr.responseJSON?.errors;
                        let msg = xhr.responseJSON?.message || 'Hiba történt a mentés során.';
                        if (errs) {
                            const list = Object.values(errs).flat().join(' ');
                            if (list) msg = list;
                        }
                        $('#quickProductErrors').removeClass('d-none').text(msg);
                    },
                    complete: function () {
                        $('#quickProductSubmit').prop('disabled', false);
                    }
                });
            });

            function resetForm(title = null) {
                $('#goodsReceiptForm')[0].reset();
                $('#goodsReceiptModalLabel').text(title);
                $('#goods_receipt_id').val('');
                $('#goods_receipt_status').val('');

                if (defaultCompanyId) {
                    $('#company_id').val(defaultCompanyId);
                }

                if (warehouses.length) {
                    $('#warehouse_id').val(warehouses[0].id);
                }

                $('#received_at').val(nowDateTimeLocal());

                $('#partner_name').val('');
                $('#partner_tax_number').val('');
                $('#partner_country').val('HU');
                $('#partner_zip_code').val('');
                $('#partner_city').val('');
                $('#partner_address_line').val('');
                $('#partner_email').val('');
                $('#partner_phone').val('');

                $('#supplier_document_number').val('');
                $('#note_for_document').val('');
                $('#note').val('');

                resetPreview();
                $('#openGoodsReceiptPdf').hide();

                partnerCustomerId = null;
                selectedPartnerName = '';

                items.splice(0, items.length);
                renderItems();
                $('#product_search').val('');
                $('#product_search_results').empty();
                clearPartnerClientResults();
            }

            $('#addButton').on('click', async function () {
                resetForm('Új bevételezés');
                modal.show();
            });

            $('#items_table').on('input', '.item-qty, .item-unit, .item-unit-net, .item-vat', function () {
                const $tr = $(this).closest('tr');
                const idx = Number($tr.data('idx'));
                if (!Number.isFinite(idx) || !items[idx]) return;

                const qty = Number($tr.find('.item-qty').val() || 0);

                items[idx].quantity = qty;
                items[idx].unit = String($tr.find('.item-unit').val() || 'db');
                items[idx].unit_net_price = Number($tr.find('.item-unit-net').val() || 0);
                items[idx].vat_percent = Number($tr.find('.item-vat').val() || 0);

                const { net, gross } = calcRow(items[idx]);
                $tr.find('.item-net-cell').text(fmtMoney(net));
                $tr.find('.item-gross-cell').text(fmtMoney(gross));
                syncItemsJson();
                scheduleDraftAutoSave();
            });

            $('#items_table').on('click', '.item-remove', function () {
                const $tr = $(this).closest('tr');
                const idx = Number($tr.data('idx'));
                if (!Number.isFinite(idx)) return;
                items.splice(idx, 1);
                renderItems();
                scheduleDraftAutoSave();
            });

            let searchTimeout = null;
            $('#product_search').on('input', function () {
                const q = $(this).val().trim();
                clearTimeout(searchTimeout);
                if (q.length < 2) {
                    $('#product_search_results').empty();
                    return;
                }

                const warehouseId = String($('#warehouse_id').val() || '').trim();
                if (!warehouseId) {
                    $('#product_search_results').empty();
                    return;
                }

                searchTimeout = setTimeout(() => {
                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/termekek/search?q=${encodeURIComponent(q)}&warehouse_id=${encodeURIComponent(warehouseId)}&show_partner_prices=1${partnerCustomerId ? '&customer_id=' + encodeURIComponent(partnerCustomerId) : ''}`,
                        method: 'GET',
                        success: function (resp) {
                            const results = resp?.products ?? [];
                            const container = $('#product_search_results');
                            container.empty();
                            results.forEach(p => {
                                const unitText = (p.unit_abbreviation || p.unit_name) ? `${escapeHtml(p.unit_abbreviation || p.unit_name)}` : '';
                                container.append(`
                                    <button type="button" class="list-group-item list-group-item-action product-result"
                                        data-id="${escapeHtml(p.id)}"
                                        data-name="${escapeHtml(p.title)}"
                                        data-unit="${escapeHtml(p.unit_abbreviation || p.unit_name || 'db')}"
                                        data-unit-qty="${escapeHtml(p.unit_qty || '')}"
                                        data-price="${escapeHtml(p.effective_gross_price ?? p.gross_price ?? 0)}"
                                        data-vat="${escapeHtml(p.tax_value ?? '')}"
                                        data-img="${escapeHtml(p.image_url ?? '')}">
                                        <div class="d-flex align-items-center gap-2">
                                            ${p.image_url ? `<img src="${escapeHtml(p.image_url)}" class="item-thumb" alt="">` : ''}
                                            <div>
                                                <div class="fw-semibold">${escapeHtml(p.title)}</div>
                                                <div class="small text-muted">${unitText}</div>
                                            </div>
                                        </div>
                                    </button>
                                `);
                            });
                        }
                    });
                }, 250);
            });

            $('#product_search_results').on('click', '.product-result', function () {
                const $btn = $(this);

                const vatRaw = String($btn.data('vat') ?? '');
                const vatPercent = vatRaw !== '' && Number.isFinite(Number(vatRaw)) ? Number(vatRaw) : 27;
                const gross = Number($btn.data('price') || 0);
                const unitNet = vatPercent > 0 ? gross / (1 + vatPercent / 100) : gross;
                const packQty = Number($btn.data('unit-qty') || 0);
                const defaultQty = Number.isFinite(packQty) && packQty > 0 ? packQty : 1;

                items.push({
                    product_id: $btn.data('id'),
                    image_url: $btn.data('img') || null,
                    name: $btn.data('name'),
                    unit: $btn.data('unit') || 'db',
                    unit_qty: packQty,
                    quantity: defaultQty,
                    unit_net_price: Number.isFinite(unitNet) ? Math.round(unitNet) : 0,
                    vat_percent: vatPercent,
                });
                renderItems();
                $('#product_search').val('');
                $('#product_search_results').empty();
                scheduleDraftAutoSave();
            });

            async function loadGoodsReceiptPdfPreview() {
                const previewBtn = document.getElementById('previewGoodsReceipt');
                if (!previewBtn) return;
                const originalText = previewBtn ? previewBtn.innerHTML : null;
                const saveDraftBtn = document.getElementsByClassName('saveDraftGoodsReceipt');
                const saveDraftBtnWasDisabled = saveDraftBtn ? saveDraftBtn.disabled : false;
                if (previewBtn) {
                    previewBtn.disabled = true;
                    previewBtn.innerHTML = 'Betöltés...';
                }
                if (saveDraftBtn) {
                    saveDraftBtn.disabled = true;
                }

                try {
                    syncItemsJson();
                    const form = document.getElementById('goodsReceiptForm');
                    const formData = new FormData(form);

                    const resp = await fetch('{{ route('admin.documents.goods-receipts.preview-pdf') }}', {
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
                    $('#goods_receipt_preview_iframe').attr('src', blobUrl);
                    $('#goods_receipt_preview_iframe_modal').attr('src', blobUrl);
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

            $(document).on('click', '#previewGoodsReceipt', function () {
                loadGoodsReceiptPdfPreview();
            });

            $('#adminTable').on('click', '.pdf', function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const id = row_data?.id;
                if (!id) return;
                const src = `{{ url('/admin/bizonylatok/bevetelezesek') }}/${id}/pdf`;
                $('#goods_receipt_preview_iframe_modal').attr('src', src);
                setPreviewModalFooterVisible(false);
                if (previewModal) previewModal.show();
            });

            $('#createGoodsReceipt').on('click', async function (e) {
                e.preventDefault();

                syncItemsJson();

                const btn = this;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = 'Létrehozás...';

                const saveDraftBtns = $('.saveDraftGoodsReceipt');
                const originalSaveDraftHtml = saveDraftBtns.length ? saveDraftBtns.html() : null;
                if (saveDraftBtns.length) saveDraftBtns.prop('disabled', true);

                const previewBtn = $('#previewGoodsReceipt');
                const originalPreviewHtml = previewBtn.length ? previewBtn.html() : null;
                if (previewBtn.length) previewBtn.prop('disabled', true);

                const cancelBtn = $('#goodsReceiptPreviewModalFooter button[data-bs-dismiss="modal"]');
                if (cancelBtn.length) cancelBtn.prop('disabled', true);

                try {
                    const id = String($('#goods_receipt_id').val() || '').trim();
                    const isEdit = !!id;

                    $('#goods_receipt_status').val('draft');

                    const url = isEdit
                        ? `{{ url('/admin/bizonylatok/bevetelezesek') }}/${id}`
                        : `{{ url('/admin/bizonylatok/bevetelezesek') }}`;
                    const method = isEdit ? 'PUT' : 'POST';

                    const saveResp = await fetch(url, {
                        method,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'Accept': 'application/json',
                        },
                        body: $('#goodsReceiptForm').serialize(),
                    });

                    if (!saveResp.ok) {
                        let msg = 'Hiba történt a mentés során.';
                        try {
                            const json = await saveResp.json();
                            if (json?.message) msg = json.message;
                            if (json?.errors) msg = Object.values(json.errors).flat().join(' ');
                        } catch (e) {}
                        throw new Error(msg);
                    }

                    const saveJson = await saveResp.json();
                    const savedId = saveJson?.goods_receipt?.id;
                    if (!savedId) {
                        throw new Error('Sikeres mentés, de hiányzik a bizonylat azonosítója.');
                    }
                    $('#goods_receipt_id').val(savedId);

                    const form = document.getElementById('goodsReceiptForm');
                    const formData = new FormData(form);

                    const issueResp = await fetch(`{{ url('/admin/bizonylatok/bevetelezesek') }}/${savedId}/issue-pdf`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    if (!issueResp.ok) {
                        let msg = 'Hiba történt a bizonylat létrehozásakor.';
                        try {
                            const json = await issueResp.json();
                            if (json?.message) msg = json.message;
                        } catch (e) {}
                        throw new Error(msg);
                    }

                    showToast('Bizonylat létrehozva.', 'success');
                    $('#adminTable').DataTable().ajax.reload(null, false);
                    modal.hide();
                    if (previewModal) previewModal.hide();
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                    $('#adminTable').DataTable().ajax.reload(null, false);
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    if (previewBtn.length) {
                        previewBtn.prop('disabled', false);
                        if (originalPreviewHtml !== null) previewBtn.html(originalPreviewHtml);
                    }

                    if (saveDraftBtns.length) {
                        saveDraftBtns.prop('disabled', false);
                        if (originalSaveDraftHtml !== null) saveDraftBtns.html(originalSaveDraftHtml);
                    }

                    if (cancelBtn.length) cancelBtn.prop('disabled', false);
                }
            });


            $('#issueGoodsReceiptPdf').on('click', async function () {
                const id = String($('#goods_receipt_id').val() || '').trim();
                if (!id) {
                    showToast('Előbb mentsd el a bevételezést!', 'warning');
                    return;
                }

                syncItemsJson();

                const btn = this;
                const origText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = 'Folyamatban...';

                try {
                    const form = document.getElementById('goodsReceiptForm');
                    const formData = new FormData(form);

                    const resp = await fetch(`{{ url('/admin/bizonylatok/bevetelezesek') }}/${id}/issue-pdf`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    if (!resp.ok) {
                        let msg = 'Hiba történt a PDF kiállításakor.';
                        try {
                            const json = await resp.json();
                            if (json?.message) msg = json.message;
                        } catch (e) {}
                        throw new Error(msg);
                    }

                    const blob = await resp.blob();
                    const blobUrl = URL.createObjectURL(blob);
                    $('#goods_receipt_preview_iframe').attr('src', blobUrl);
                    $('#goods_receipt_preview_iframe_modal').attr('src', blobUrl);
                    if (previewModal) previewModal.show();

                    $('#adminTable').DataTable().ajax.reload(null, false);
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                }
            });

            let partnerClientSearchDebounce = null;
            let partnerCustomerId = null;
            let selectedPartnerName = '';

            function clearPartnerClientResults() {
                $('#partner_select_results').empty();
            }

            function fillPartnerFromClient({ name = '', clientName = '', tax = '', email = '', phone = '', country = 'HU', zip = '', city = '', line = '', customerId = null } = {}) {
                partnerCustomerId = (customerId !== null && customerId !== '') ? Number(customerId) : null;
                const partnerName = String(clientName || '').trim() || String(name || '').trim();
                selectedPartnerName = partnerName;

                $('#partner_name').val(partnerName);
                $('#partner_tax_number').val(tax || '');
                $('#partner_email').val(email || '');
                $('#partner_phone').val(phone || '');
                $('#partner_country').val(country || 'HU');
                $('#partner_zip_code').val(zip || '');
                $('#partner_city').val(city || '');
                $('#partner_address_line').val(line || '');

                scheduleDraftAutoSave();
            }

            // Ha a felhasználó kézzel módosítja a nevet, a korábban kiválasztott partner
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
                                                    data-source="${escapeHtml(source)}"
                                                    data-pid="${escapeHtml(c?.id ?? '')}"
                                                    data-aid="${escapeHtml(a?.id ?? '')}"
                                                    data-addr-tax="${escapeHtml(a?.tax_number || '')}"
                                                    data-cid="${isPartnerCustomer ? c.id : ''}">
                                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                                    <span>
                                                        ${addrClientName ? `<span class="d-block fw-semibold">${escapeHtml(addrClientName)}</span>` : ''}
                                                        <span class="small ${addrClientName ? 'text-muted' : 'fw-semibold'}">${escapeHtml(addrText || 'Cím nélkül')}${a?.is_default ? ' (alapértelmezett)' : ''}</span>
                                                        ${addrTax ? `<span class="d-block small text-muted"><i class="fas fa-id-card me-1"></i>${escapeHtml(addrTax)}</span>` : ''}
                                                    </span>
                                                    <span class="partner-address-edit ms-auto ps-3 text-secondary" role="button" title="Cím szerkesztése"><i class="fas fa-pen"></i></span>
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
                                                <div class="mt-2 partner-address-new-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-success partner-address-new" data-source="${escapeHtml(source)}" data-pid="${escapeHtml(c?.id ?? '')}">
                                                        <i class="fas fa-plus me-1"></i> Új számlázási cím
                                                    </button>
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

            @include('admin.documents.partials.partner-address-editor')

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

            // --- NAV adózó-kereső (szamlazz.hu) az új partner űrlaphoz ---
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
                        let msg = 'Hiba történt a partner mentésekor.';
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

                    showToast(json?.message || 'Partner mentve.', 'success');
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
                ajax: '{{ route('admin.documents.goods-receipts.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'document_number' },
                    { data: 'supplier_document_number' },
                    { data: 'partner_name' },
                    { data: 'received_at' },
                    { data: 'net_total' },
                    { data: 'vat_total' },
                    { data: 'gross_total' },
                    { data: 'status' },
                    { data: 'action', orderable: false, searchable: false },
                ],
            });

            $('.filter-input').on('keyup change', function () {
                const i = $(this).data('column');
                const v = $(this).val();
                table.columns(i).search(v).draw();
            });

            $('#adminTable').on('click', '.edit', async function () {
                resetForm('Bevételezés szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const id = row_data.id;

                const resp = await fetch(`{{ url('/admin/bizonylatok/bevetelezesek') }}/${id}`);
                const json = await resp.json();

                const receipt = json?.goods_receipt;
                const receiptItems = json?.items || [];

                $('#goods_receipt_id').val(receipt.id);
                $('#goods_receipt_status').val(receipt.status || '');
                $('#company_id').val(receipt.company_id || defaultCompanyId);
                $('#warehouse_id').val(receipt.warehouse_id || ($('#warehouse_id').val() || (warehouses[0]?.id ?? '')));
                $('#supplier_document_number').val(receipt.supplier_document_number || '');
                if (receipt.received_at) {
                    try {
                        const d = new Date(receipt.received_at);
                        if (!isNaN(d.getTime())) {
                            const m = String(d.getMonth() + 1).padStart(2, '0');
                            const day = String(d.getDate()).padStart(2, '0');
                            const hh = String(d.getHours()).padStart(2, '0');
                            const mm = String(d.getMinutes()).padStart(2, '0');
                            $('#received_at').val(`${d.getFullYear()}-${m}-${day}T${hh}:${mm}`);
                        } else {
                            $('#received_at').val(nowDateTimeLocal());
                        }
                    } catch (e) {
                        $('#received_at').val(nowDateTimeLocal());
                    }
                } else {
                    $('#received_at').val(nowDateTimeLocal());
                }

                $('#partner_name').val(receipt.partner_name || '');
                $('#partner_tax_number').val(receipt.partner_tax_number || '');
                $('#partner_country').val(receipt.partner_country || 'HU');
                $('#partner_zip_code').val(receipt.partner_zip_code || '');
                $('#partner_city').val(receipt.partner_city || '');
                $('#partner_address_line').val(receipt.partner_address_line || '');
                $('#partner_email').val(receipt.partner_email || '');
                $('#partner_phone').val(receipt.partner_phone || '');

                $('#note_for_document').val(receipt.note_for_document || '');
                $('#note').val(receipt.note || '');

                items.splice(0, items.length);
                (json?.items || []).forEach(it => {
                    items.push({
                        product_id: it.product_id,
                        image_url: it.image_url ?? null,
                        name: it.name,
                        unit: it.unit || 'db',
                        unit_qty: Number(it.unit_qty || 0),
                        quantity: Number(it.quantity || 0),
                        unit_net_price: Number(it.unit_net_price || 0),
                        vat_percent: Number(it.vat_percent || 0),
                    });
                });
                renderItems();

                if (receipt.pdf_path) {
                    $('#openGoodsReceiptPdf')
                        .show()
                        .off('click')
                        .on('click', function () {
                            window.open(`{{ url('/admin/bizonylatok/bevetelezesek') }}/${receipt.id}/pdf`, '_blank');
                        });
                }

                modal.show();
            });

            $('#adminTable').on('click', '.delete', function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const id = row_data.id;

                if (!confirm('Biztosan törlöd?')) {
                    return;
                }

                $.ajax({
                    url: `{{ url('/admin/bizonylatok/bevetelezesek') }}/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    success: function (resp) {
                        showToast(resp?.message || 'Sikeres törlés!', 'success');
                        table.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        showToast(xhr?.responseJSON?.message || 'Hiba történt.', 'danger');
                    }
                });
            });

            $('.saveDraftGoodsReceipt').on('click', function (e) {
                e.preventDefault();
                $('#goods_receipt_status').val('draft');
                $('#goodsReceiptForm').trigger('submit');
            });

            // Automatikus piszkozat mentés: a létrehozó modalban történő
            // módosításkor debounce-olva ment piszkozatként, modal bezárása nélkül.
            let autoSaveTimer = null;
            let autoSaveInFlight = false;
            let autoSavePending = false;

            function canAutoSaveDraft() {
                if (!modal || !modal._isShown) return false;
                const st = String($('#goods_receipt_status').val() || '');
                if (st !== '' && st !== 'draft') return false;
                return String($('#partner_name').val() || '').trim() !== '';
            }

            function scheduleDraftAutoSave() {
                if (!canAutoSaveDraft()) return;
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(autoSaveDraft, 1500);
            }

            function autoSaveDraft() {
                if (!canAutoSaveDraft()) return;

                if (autoSaveInFlight) {
                    autoSavePending = true;
                    return;
                }

                syncItemsJson();
                $('#goods_receipt_status').val('draft');

                const id = String($('#goods_receipt_id').val() || '').trim();
                const url = id
                    ? `{{ url('/admin/bizonylatok/bevetelezesek') }}/${id}`
                    : `{{ url('/admin/bizonylatok/bevetelezesek') }}`;
                const method = id ? 'PUT' : 'POST';

                autoSaveInFlight = true;
                $.ajax({
                    url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    data: $('#goodsReceiptForm').serialize(),
                    success: function (resp) {
                        const rid = resp?.goods_receipt?.id;
                        if (rid && !$('#goods_receipt_id').val()) {
                            $('#goods_receipt_id').val(rid);
                        }
                        table.ajax.reload(null, false);
                    },
                    complete: function () {
                        autoSaveInFlight = false;
                        if (autoSavePending) {
                            autoSavePending = false;
                            scheduleDraftAutoSave();
                        }
                    }
                });
            }

            $('#goodsReceiptForm').on('input change', 'input, select, textarea', function () {
                scheduleDraftAutoSave();
            });

            $('#goodsReceiptForm').on('submit', function (e) {
                e.preventDefault();

                syncItemsJson();

                const id = String($('#goods_receipt_id').val() || '').trim();
                const isEdit = !!id;

                const url = isEdit
                    ? `{{ url('/admin/bizonylatok/bevetelezesek') }}/${id}`
                    : `{{ url('/admin/bizonylatok/bevetelezesek') }}`;

                const method = isEdit ? 'PUT' : 'POST';

                const $btn = $('.saveDraftGoodsReceipt');
                const originalText = $btn.length ? $btn.html() : null;
                if ($btn.length) {
                    $btn.prop('disabled', true).html('Mentés...');
                }

                $.ajax({
                    url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    data: $(this).serialize(),
                    success: function (resp) {
                        showToast(resp?.message || 'Sikeres mentés!', 'success');
                        table.ajax.reload(null, false);

                        modal.hide();

                        const receipt = resp?.goods_receipt;
                        if (receipt?.id) {
                            $('#goods_receipt_id').val(receipt.id);
                        }

                        if (receipt?.pdf_path) {
                            $('#openGoodsReceiptPdf')
                                .show()
                                .off('click')
                                .on('click', function () {
                                    window.open(`{{ url('/admin/bizonylatok/bevetelezesek') }}/${receipt.id}/pdf`, '_blank');
                                });
                        }
                    },
                    error: function (xhr) {
                        showToast(xhr?.responseJSON?.message || 'Hiba történt.', 'danger');
                    },
                    complete: function () {
                        if ($btn.length) {
                            $btn.prop('disabled', false);
                            if (originalText !== null) {
                                $btn.html(originalText);
                            }
                        }
                    }
                });
            });

        });
    </script>
@endsection
