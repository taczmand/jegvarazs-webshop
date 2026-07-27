@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Raktározás / Raktárközi átvezetés</h2>
            @if(auth('admin')->user()->can('create-warehouse-transfer'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új átvezetés</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-warehouse-transfers'))

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
                        <select class="form-select filter-input" data-column="6">
                            <option value="">Állapot (összes)</option>
                            <option value="draft">draft</option>
                            <option value="posted">posted</option>
                            <option value="cancelled">cancelled</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Bizonylatszám</th>
                        <th>Forrás raktár</th>
                        <th>Cél raktár</th>
                        <th>Átvezetés dátuma</th>
                        <th>PDF</th>
                        <th>Állapot</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Nincs jogosultságod az átvezetések megtekintéséhez.
                </div>
            @endif
        </div>
    </div>


    <x-admin.document-modal id="warehouseTransferModal" title="Raktárközi átvezetés" form-id="warehouseTransferForm" save-button-id="saveWarehouseTransfer" pane-left="40%" pane-mid="60%">
        <x-slot:left>
            <input type="hidden" id="warehouse_transfer_id" name="id">

            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Alapadatok</legend>

                <div class="mb-2">
                    <label for="from_warehouse_id" class="form-label">Forrás raktár*</label>
                    <select class="form-select" id="from_warehouse_id" name="from_warehouse_id" required>
                        @foreach(($warehouses ?? []) as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label for="to_warehouse_id" class="form-label">Cél raktár*</label>
                    <select class="form-select" id="to_warehouse_id" name="to_warehouse_id" required>
                        @foreach(($warehouses ?? []) as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label for="transferred_at" class="form-label">Átvezetés dátuma</label>
                    <input type="date" class="form-control" id="transferred_at" name="transferred_at">
                </div>
            </fieldset>

            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Megjegyzések</legend>

                <div class="mb-2">
                    <label for="note_before_items" class="form-label">Megjegyzés (tételek előtt)</label>
                    <textarea class="form-control" id="note_before_items" name="note_before_items" rows="2"></textarea>
                </div>
                <div class="mb-2">
                    <label for="note_after_items" class="form-label">Megjegyzés (tételek után)</label>
                    <textarea class="form-control" id="note_after_items" name="note_after_items" rows="2"></textarea>
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
                            <th style="width: 70%;">Megnevezés</th>
                            <th style="width: 18%;" class="text-end">Menny.</th>
                            <th style="width: 8%;">Mee.</th>
                            <th style="width: 4%;"></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <input type="hidden" id="items_json" name="items_json" value="[]">
            </fieldset>
        </x-slot:middle>

        <x-slot:right>
            <div class="d-flex flex-column h-100" style="min-height: 0;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold">PDF előnézet</div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="previewWarehouseTransfer">Előnézet</button>
                </div>
                <div class="border rounded flex-grow-1" style="min-height: 0; overflow: hidden;">
                    <iframe id="warehouse_transfer_preview_iframe" src="about:blank" style="width:100%; height:100%; border:0;"></iframe>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-primary w-100" id="issueWarehouseTransferPdf">PDF kiállítás + készlet átvezetés</button>
                </div>
            </div>
        </x-slot:right>

        <x-slot:footer>
            <button type="button" class="btn btn-outline-primary" id="previewWarehouseTransferFooter">Előnézet</button>
            <button type="button" class="btn btn-outline-secondary" id="openWarehouseTransferPdf" style="display:none;">Megnyitás PDF</button>
        </x-slot:footer>
    </x-admin.document-modal>


    <div class="modal fade" id="warehouseTransferPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 95vw;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Átvezetés PDF előnézet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <div class="modal-body" style="height: 80vh;">
                    <iframe id="warehouse_transfer_preview_iframe_modal" src="about:blank" style="width:100%; height:100%; border:0;"></iframe>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script type="module">

        const warehouses = @json($warehouses ?? []);

        const modalDOM = document.getElementById('warehouseTransferModal');
        const modal = new bootstrap.Modal(modalDOM);
        const previewModalDOM = document.getElementById('warehouseTransferPreviewModal');
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

            function resetPreview() {
                $('#warehouse_transfer_preview_iframe').attr('src', 'about:blank');
                $('#warehouse_transfer_preview_iframe_modal').attr('src', 'about:blank');
            }

            const items = [];

            function renderItems() {
                const $tbody = $('#items_table tbody');
                $tbody.empty();

                items.forEach((row, idx) => {
                    $tbody.append(`
                        <tr data-idx="${idx}">
                            <td>
                                <div class="fw-semibold">${escapeHtml(row.name || '')}</div>
                                <div class="small text-muted">${escapeHtml(row.sku || '')}</div>
                            </td>
                            <td class="text-end">
                                <input type="number" step="0.001" class="form-control form-control-sm item-qty" value="${escapeHtml(row.quantity ?? 1)}">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm item-unit" value="${escapeHtml(row.unit || 'db')}">
                            </td>
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
                $('#warehouseTransferForm')[0].reset();
                $('#warehouseTransferModalLabel').text(title);
                $('#warehouse_transfer_id').val('');

                if (warehouses.length) {
                    $('#from_warehouse_id').val(warehouses[0].id);
                    $('#to_warehouse_id').val(warehouses.length > 1 ? warehouses[1].id : warehouses[0].id);
                }

                $('#transferred_at').val(todayDate());

                $('#note_before_items').val('');
                $('#note_after_items').val('');
                $('#note').val('');

                resetPreview();
                $('#openWarehouseTransferPdf').hide();

                items.splice(0, items.length);
                renderItems();
                $('#product_search').val('');
                $('#product_search_results').empty();
            }

            $('#addButton').on('click', async function () {
                resetForm('Új átvezetés');
                modal.show();
            });

            $('#items_table').on('input', '.item-qty, .item-unit', function () {
                const $tr = $(this).closest('tr');
                const idx = Number($tr.data('idx'));
                if (!Number.isFinite(idx) || !items[idx]) return;

                items[idx].quantity = Number($tr.find('.item-qty').val() || 0);
                items[idx].unit = String($tr.find('.item-unit').val() || 'db');

                renderItems();
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

                const fromWarehouseId = String($('#from_warehouse_id').val() || '').trim();
                if (!fromWarehouseId) {
                    $('#product_search_results').empty();
                    return;
                }

                searchTimeout = setTimeout(() => {
                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/termekek/search?q=${encodeURIComponent(q)}&warehouse_id=${encodeURIComponent(fromWarehouseId)}`,
                        method: 'GET',
                        success: function (resp) {
                            const results = resp?.products ?? [];
                            const container = $('#product_search_results');
                            container.empty();
                            results.forEach(p => {
                                const availableQtyRaw = p.available_quantity;
                                const availableQty = Number(availableQtyRaw ?? 0);
                                const isOut = availableQtyRaw === null || !Number.isFinite(availableQty) || availableQty <= 0;
                                const unitText = (p.unit_abbreviation || p.unit_name) ? `${escapeHtml(p.unit_abbreviation || p.unit_name)}` : '';
                                const sku = p.sku ? `SKU: ${escapeHtml(p.sku)}` : '';
                                const title = p.title || p.name || '';
                                const qtyLabel = availableQtyRaw === null
                                    ? 'Készlet: -'
                                    : `Készlet: ${escapeHtml(String(Number.isFinite(availableQty) ? availableQty : 0).replace(/\.0+$/, ''))}`;
                                container.append(`
                                    <button type="button" class="list-group-item list-group-item-action product-result" ${isOut ? 'disabled aria-disabled="true"' : ''}
                                        data-id="${escapeHtml(p.id)}"
                                        data-name="${escapeHtml(title)}"
                                        data-sku="${escapeHtml(p.sku || '')}"
                                        data-unit="${escapeHtml(p.unit_abbreviation || p.unit_name || 'db')}"
                                        data-available-qty="${escapeHtml(availableQty)}">
                                        <div class="fw-semibold">${escapeHtml(title)}</div>
                                        <div class="small text-muted">${sku}${sku ? ' | ' : ''}${unitText}${(sku || unitText) ? ' | ' : ''}${qtyLabel}${isOut ? ' | nincs készleten' : ''}</div>
                                    </button>
                                `);
                            });
                        },
                        error: function () {
                            $('#product_search_results').empty();
                        }
                    });
                }, 250);
            });

            $('#product_search_results').on('click', '.product-result', function () {
                const $btn = $(this);
                const availableQty = Number($btn.data('available-qty') ?? 0);
                if (!Number.isFinite(availableQty) || availableQty <= 0) {
                    showToast('Nincs készleten a forrás raktárban.', 'warning');
                    return;
                }
                items.push({
                    product_id: $btn.data('id'),
                    name: $btn.data('name'),
                    sku: $btn.data('sku'),
                    unit: $btn.data('unit') || 'db',
                    quantity: 1,
                });
                renderItems();
                $('#product_search').val('');
                $('#product_search_results').empty();
            });

            async function loadWarehouseTransferPdfPreview() {
                const previewBtn = document.getElementById('previewWarehouseTransfer');
                if (!previewBtn) return;

                const fromWarehouseId = String($('#from_warehouse_id').val() || '').trim();
                const toWarehouseId = String($('#to_warehouse_id').val() || '').trim();
                if (fromWarehouseId && toWarehouseId && fromWarehouseId === toWarehouseId) {
                    showToast('A forrás és cél raktár nem lehet azonos.', 'warning');
                    return;
                }

                const originalText = previewBtn ? previewBtn.innerHTML : null;
                const saveBtn = document.getElementById('saveWarehouseTransfer');
                const saveBtnWasDisabled = saveBtn ? saveBtn.disabled : false;
                if (previewBtn) {
                    previewBtn.disabled = true;
                    previewBtn.innerHTML = 'Betöltés...';
                }
                if (saveBtn) {
                    saveBtn.disabled = true;
                }

                try {
                    syncItemsJson();
                    const form = document.getElementById('warehouseTransferForm');
                    const formData = new FormData(form);

                    const resp = await fetch('{{ route('admin.warehouse-transfers.preview-pdf') }}', {
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
                    $('#warehouse_transfer_preview_iframe').attr('src', blobUrl);
                    $('#warehouse_transfer_preview_iframe_modal').attr('src', blobUrl);
                    if (previewModal) previewModal.show();
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                } finally {
                    if (previewBtn) {
                        previewBtn.disabled = false;
                        if (originalText !== null) previewBtn.innerHTML = originalText;
                    }
                    if (saveBtn) {
                        saveBtn.disabled = saveBtnWasDisabled;
                    }
                }
            }

            $('#previewWarehouseTransfer').on('click', function () {
                loadWarehouseTransferPdfPreview();
            });

            $('#previewWarehouseTransferFooter').on('click', function () {
                loadWarehouseTransferPdfPreview();
            });

            $('#issueWarehouseTransferPdf').on('click', async function () {
                const id = String($('#warehouse_transfer_id').val() || '').trim();
                if (!id) {
                    showToast('Előbb mentsd el az átvezetést!', 'warning');
                    return;
                }

                const fromWarehouseId = String($('#from_warehouse_id').val() || '').trim();
                const toWarehouseId = String($('#to_warehouse_id').val() || '').trim();
                if (fromWarehouseId && toWarehouseId && fromWarehouseId === toWarehouseId) {
                    showToast('A forrás és cél raktár nem lehet azonos.', 'warning');
                    return;
                }

                syncItemsJson();

                const btn = this;
                const origText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = 'Folyamatban...';

                try {
                    const form = document.getElementById('warehouseTransferForm');
                    const formData = new FormData(form);

                    const resp = await fetch(`{{ url('/admin/raktarozas/raktarkozi-atvezetesek') }}/${id}/issue-pdf`, {
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
                    $('#warehouse_transfer_preview_iframe').attr('src', blobUrl);
                    $('#warehouse_transfer_preview_iframe_modal').attr('src', blobUrl);
                    if (previewModal) previewModal.show();

                    $('#adminTable').DataTable().ajax.reload(null, false);
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                }
            });

            function fillForm(transfer, itemsArr) {
                $('#warehouse_transfer_id').val(transfer.id || '');
                $('#from_warehouse_id').val(transfer.from_warehouse_id || '');
                $('#to_warehouse_id').val(transfer.to_warehouse_id || '');
                $('#transferred_at').val(transfer.transferred_at || '');
                $('#note_before_items').val(transfer.note_before_items || '');
                $('#note_after_items').val(transfer.note_after_items || '');
                $('#note').val(transfer.note || '');

                items.splice(0, items.length);
                (itemsArr || []).forEach(it => {
                    items.push({
                        product_id: it.product_id,
                        name: it.name,
                        sku: it.sku,
                        unit: it.unit,
                        quantity: it.quantity,
                    });
                });
                renderItems();

                resetPreview();

                if (transfer.pdf_path) {
                    $('#openWarehouseTransferPdf').show().off('click').on('click', function () {
                        window.open(`{{ url('/admin/raktarozas/raktarkozi-atvezetesek') }}/${transfer.id}/pdf`, '_blank');
                    });
                } else {
                    $('#openWarehouseTransferPdf').hide();
                }
            }

            $('#saveWarehouseTransfer').on('click', async function () {
                syncItemsJson();

                const fromWarehouseId = String($('#from_warehouse_id').val() || '').trim();
                const toWarehouseId = String($('#to_warehouse_id').val() || '').trim();
                if (fromWarehouseId && toWarehouseId && fromWarehouseId === toWarehouseId) {
                    showToast('A forrás és cél raktár nem lehet azonos.', 'warning');
                    return;
                }

                const id = String($('#warehouse_transfer_id').val() || '').trim();
                const isEdit = Boolean(id);

                const btn = this;
                const origText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = 'Mentés...';

                try {
                    const form = document.getElementById('warehouseTransferForm');
                    const formData = new FormData(form);

                    let url = '{{ url('/admin/raktarozas/raktarkozi-atvezetesek') }}';
                    let method = 'POST';
                    if (isEdit) {
                        url = `${url}/${id}`;
                        method = 'POST';
                        formData.append('_method', 'PUT');
                    }

                    const resp = await fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    if (!resp.ok) {
                        let msg = 'Hiba történt a mentés során.';
                        try {
                            const json = await resp.json();
                            if (json?.message) msg = json.message;
                        } catch (e) {}
                        throw new Error(msg);
                    }

                    const json = await resp.json();
                    const transfer = json?.warehouse_transfer;
                    if (transfer?.id) {
                        $('#warehouse_transfer_id').val(transfer.id);
                    }

                    showToast(json?.message || 'Sikeres mentés!', 'success');
                    $('#adminTable').DataTable().ajax.reload(null, false);
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                }
            });

            $('#adminTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.warehouse-transfers.data') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'document_number', name: 'document_number' },
                    { data: 'from_warehouse', name: 'from_warehouse', orderable: false, searchable: false },
                    { data: 'to_warehouse', name: 'to_warehouse', orderable: false, searchable: false },
                    { data: 'transferred_at', name: 'transferred_at' },
                    {
                        data: 'pdf_path',
                        name: 'pdf_path',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            if (!data) return '';
                            return `<a href="{{ url('/admin/raktarozas/raktarkozi-atvezetesek') }}/${row.id}/pdf" target="_blank">PDF</a>`;
                        }
                    },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                order: [[0, 'desc']],
                initComplete: function() {
                    const table = this.api();
                    $('.filter-input').on('keyup change', function() {
                        const col = $(this).data('column');
                        table.column(col).search(this.value).draw();
                    });
                }
            });

            $('#adminTable').on('click', '.edit', async function () {
                const id = $(this).data('id');
                if (!id) return;

                try {
                    const resp = await fetch(`{{ url('/admin/raktarozas/raktarkozi-atvezetesek') }}/${id}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!resp.ok) throw new Error('Nem sikerült betölteni.');

                    const json = await resp.json();
                    fillForm(json?.warehouse_transfer || {}, json?.items || []);

                    $('#warehouseTransferModalLabel').text('Átvezetés szerkesztése');
                    modal.show();
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                }
            });

            $('#adminTable').on('click', '.delete', async function () {
                const id = $(this).data('id');
                if (!id) return;

                if (!confirm('Biztosan törlöd?')) return;

                try {
                    const resp = await fetch(`{{ url('/admin/raktarozas/raktarkozi-atvezetesek') }}/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: new URLSearchParams({ _method: 'DELETE' })
                    });

                    const json = await resp.json().catch(() => ({}));

                    if (!resp.ok) {
                        throw new Error(json?.message || 'Törlés sikertelen.');
                    }

                    showToast(json?.message || 'Sikeres törlés!', 'success');
                    $('#adminTable').DataTable().ajax.reload(null, false);
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                }
            });
        });

    </script>
@endsection
