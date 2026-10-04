@extends('layouts.admin')

@section('content')


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
                        <label for="partner_name" class="form-label">Név*</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="partner_name" name="partner_name" required autocomplete="off">
                            <div id="partner_client_search_results" class="list-group w-100 admin-client-search-results" style="z-index: 1100; display:none; max-height: 260px; overflow-y: auto;"></div>
                        </div>
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
                    <input type="text" class="form-control" id="product_search" autocomplete="off" placeholder="Kezdj el gépelni...">
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

            function getQtyStep(row) {
                const pack = Number(row.unit_qty || 0);
                if (Number.isFinite(pack) && pack > 0) return pack;
                return 1;
            }

            function normalizeQtyToStep(qty, step) {
                const q = Number(qty || 0);
                const s = Number(step || 1);
                if (!Number.isFinite(q) || !Number.isFinite(s) || s <= 0) return 0;
                return Math.round(q / s) * s;
            }

            function renderItems() {
                const $tbody = $('#items_table tbody');
                $tbody.empty();

                items.forEach((row, idx) => {
                    const { net, gross } = calcRow(row);
                    const qtyStep = getQtyStep(row);
                    const qtyVal = (row.quantity ?? 1);

                    $tbody.append(`
                        <tr data-idx="${idx}">
                            <td>
                                <div class="fw-semibold">${escapeHtml(row.name || '')}</div>
                            </td>
                            <td class="text-end">
                                <input type="number" step="${escapeHtml(qtyStep)}" min="0" class="form-control form-control-sm item-qty" value="${escapeHtml(qtyVal)}">
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

                const $qty = $tr.find('.item-qty');
                const qtyStep = getQtyStep(items[idx]);
                let qty = Number($qty.val() || 0);

                if (qtyStep !== 1) {
                    const normalized = normalizeQtyToStep(qty, qtyStep);
                    if (Number.isFinite(normalized) && normalized !== qty) {
                        qty = normalized;
                        $qty.val(String(qty));
                    }
                }

                items[idx].quantity = qty;
                items[idx].unit = String($tr.find('.item-unit').val() || 'db');
                items[idx].unit_net_price = Number($tr.find('.item-unit-net').val() || 0);
                items[idx].vat_percent = Number($tr.find('.item-vat').val() || 0);

                const { net, gross } = calcRow(items[idx]);
                $tr.find('.item-net-cell').text(fmtMoney(net));
                $tr.find('.item-gross-cell').text(fmtMoney(gross));
                syncItemsJson();
            });

            $('#items_table').on('click', '.item-remove', function () {
                const $tr = $(this).closest('tr');
                const idx = Number($tr.data('idx'));
                if (!Number.isFinite(idx)) return;
                items.splice(idx, 1);
                renderItems();
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
                        url: `${window.appConfig.APP_URL}admin/termekek/search?q=${encodeURIComponent(q)}&warehouse_id=${encodeURIComponent(warehouseId)}`,
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
                                        data-unit-qty="${escapeHtml(p.unit_qty || '')}">
                                        <div class="fw-semibold">${escapeHtml(p.title)}</div>
                                        <div class="small text-muted">${unitText}</div>
                                    </button>
                                `);
                            });
                        }
                    });
                }, 250);
            });

            $('#product_search_results').on('click', '.product-result', function () {
                const $btn = $(this);
                items.push({
                    product_id: $btn.data('id'),
                    name: $btn.data('name'),
                    unit: $btn.data('unit') || 'db',
                    unit_qty: Number($btn.data('unit-qty') || 0),
                    quantity: 1,
                    unit_net_price: 0,
                    vat_percent: 27,
                });
                renderItems();
                $('#product_search').val('');
                $('#product_search_results').empty();
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

            function clearPartnerClientResults() {
                $('#partner_client_search_results').hide().empty();
            }

            function fillPartnerFromClient({ name = '', tax = '', email = '', phone = '', country = 'HU', zip = '', city = '', line = '' } = {}) {
                $('#partner_name').val(name || '');
                $('#partner_tax_number').val(tax || '');
                $('#partner_email').val(email || '');
                $('#partner_phone').val(phone || '');
                $('#partner_country').val(country || 'HU');
                $('#partner_zip_code').val(zip || '');
                $('#partner_city').val(city || '');
                $('#partner_address_line').val(line || '');
            }

            $('#partner_name').on('input', function () {
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
                            const $list = $('#partner_client_search_results');
                            $list.empty();

                            if (clients.length) {
                                clients.forEach(c => {
                                    const name = c?.name || '';
                                    const idNumber = c?.id_number || '';
                                    const email = c?.email || '';
                                    const phone = c?.phone || '';
                                    const source = c?.source || '';
                                    const addresses = Array.isArray(c?.addresses) ? c.addresses : [];

                                    const headerParts = [source, idNumber, email, phone].filter(Boolean).join(', ');
                                    $list.append(`
                                        <div class="list-group-item py-0 px-1 client-search-header">
                                            <div class="fw-semibold small">${escapeHtml(name || email || 'N/A')}${headerParts ? ' (' + escapeHtml(headerParts) + ')' : ''}</div>
                                        </div>
                                    `);

                                    if (!addresses.length) {
                                        $list.append(`
                                            <button type="button" class="list-group-item list-group-item-action py-0 px-1 small client-no-address"
                                                data-name="${escapeHtml(name)}"
                                                data-tax="${escapeHtml(idNumber)}"
                                                data-email="${escapeHtml(email)}"
                                                data-phone="${escapeHtml(phone)}">
                                                <div class="fw-semibold">Cím nélkül</div>
                                                <div class="small text-muted">Kattints a kiválasztáshoz</div>
                                            </button>
                                        `);
                                        return;
                                    }

                                    addresses.forEach(a => {
                                        const addrText = `${a?.zip_code || ''} ${a?.city || ''}, ${a?.address_line || ''}`.trim();
                                        $list.append(`
                                            <button type="button" class="list-group-item list-group-item-action py-0 px-1 small client-address-item"
                                                data-name="${escapeHtml(name)}"
                                                data-tax="${escapeHtml(idNumber)}"
                                                data-email="${escapeHtml(email)}"
                                                data-phone="${escapeHtml(phone)}"
                                                data-country="${escapeHtml(a?.country || 'HU')}"
                                                data-zip="${escapeHtml(a?.zip_code || '')}"
                                                data-city="${escapeHtml(a?.city || '')}"
                                                data-line="${escapeHtml(a?.address_line || '')}">
                                                <div class="fw-semibold">${escapeHtml(addrText || 'Cím nélkül')}${a?.is_default ? ' (alapértelmezett)' : ''}</div>
                                                <div class="text-muted" style="font-size: 0.7rem;">${escapeHtml(a?.country || '')}</div>
                                            </button>
                                        `);
                                    });
                                });
                            } else {
                                $list.append(`
                                    <div class="list-group-item py-0 px-1 small">
                                        <div class="small text-muted">Nincs találat.</div>
                                    </div>
                                `);
                            }

                            $list.append(`
                                <button type="button" class="list-group-item list-group-item-action py-0 px-1 small client-create client-create-item">
                                    <div class="fw-semibold">Új partner létrehozása</div>
                                    <div class="small text-muted">Az alábbi mezőkben megadott adatokkal</div>
                                </button>
                            `);

                            $list.show();
                        },
                        error: function () {
                            const $list = $('#partner_client_search_results');
                            $list.empty();
                            $list.append(`
                                <div class="list-group-item py-0 px-1 small">
                                    <div class="small text-muted">A keresés sikertelen volt.</div>
                                </div>
                            `);
                            $list.show();
                        }
                    });
                }, 300);
            });

            $('#partner_client_search_results').on('click', '.client-address-item', function () {
                const $btn = $(this);
                fillPartnerFromClient({
                    name: $btn.data('name') || '',
                    tax: $btn.data('tax') || '',
                    email: $btn.data('email') || '',
                    phone: $btn.data('phone') || '',
                    country: $btn.data('country') || 'HU',
                    zip: $btn.data('zip') || '',
                    city: $btn.data('city') || '',
                    line: $btn.data('line') || '',
                });
                clearPartnerClientResults();
            });

            $('#partner_client_search_results').on('click', '.client-no-address', function () {
                const $btn = $(this);
                fillPartnerFromClient({
                    name: $btn.data('name') || '',
                    tax: $btn.data('tax') || '',
                    email: $btn.data('email') || '',
                    phone: $btn.data('phone') || '',
                    country: 'HU',
                    zip: '',
                    city: '',
                    line: '',
                });
                clearPartnerClientResults();
                setTimeout(() => {
                    $('#partner_zip_code').trigger('focus');
                }, 0);
            });

            $('#partner_client_search_results').on('click', '.client-create', function () {
                clearPartnerClientResults();
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
