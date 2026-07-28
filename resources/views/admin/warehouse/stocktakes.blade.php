@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Raktározás / Leltár</h2>
            @if(auth('admin')->user()->can('create-stocktake'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új leltár</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-stocktakes'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Megnevezés" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Raktár" class="filter-input form-control" data-column="2">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="3">
                            <option value="">Állapot (összes)</option>
                            <option value="open">Nyitott</option>
                            <option value="closed">Lezárt</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Megnevezés</th>
                        <th>Raktár</th>
                        <th>Állapot</th>
                        <th>Kezdés</th>
                        <th>Lezárás</th>
                        <th>Létrehozva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>

            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Nincs jogosultságod a leltárak megtekintéséhez.
                </div>
            @endif
        </div>
    </div>


    <x-admin.document-modal id="stocktakeModal" title="Leltár" form-id="stocktakeForm" save-button-id="saveStocktake" pane-left="35%" pane-mid="65%">
        <x-slot:left>
            <input type="hidden" id="stocktake_id" name="id">

            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Alapadatok</legend>

                <div class="mb-2">
                    <label for="warehouse_id" class="form-label">Raktár*</label>
                    <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                        @foreach(($warehouses ?? []) as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label for="name" class="form-label">Megnevezés*</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <input type="hidden" id="status" name="status" value="open">

                <div class="mb-2">
                    <label for="note" class="form-label">Megjegyzés</label>
                    <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                </div>
            </fieldset>
        </x-slot:left>

        <x-slot:middle>
            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Termékek</legend>

                <div id="stocktake_filter_bar" class="bg-white" style="position: sticky; top: 0; z-index: 5; padding-bottom: 8px;">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-lg-6">
                            <label for="product_filter" class="form-label">Terméknév szűrő</label>
                            <input type="text" class="form-control" id="product_filter" placeholder="Kezdj el gépelni...">
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="small text-muted" id="product_filter_count"></div>
                        </div>
                    </div>
                    <hr class="my-2">
                </div>

                <div id="stocktake_products_loader" class="py-4 text-center" style="display:none;">
                    <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                    <div class="small text-muted mt-2">Betöltés...</div>
                </div>

                <div id="stocktake_products_container"></div>

                <input type="hidden" id="items_json" name="items_json" value="[]">
            </fieldset>
        </x-slot:middle>

        <x-slot:footer>
            <button type="button" class="btn btn-outline-primary" id="saveAndCloseStocktake">Mentés és lezárás</button>
        </x-slot:footer>

    </x-admin.document-modal>

@endsection

@section('scripts')
    <script type="module">
        const warehouses = @json($warehouses ?? []);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const modalDOM = document.getElementById('stocktakeModal');
        const modal = new bootstrap.Modal(modalDOM);

        const itemsByProductId = new Map();
        let productsCache = [];

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

        function nowDateTime() {
            const d = new Date();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const hh = String(d.getHours()).padStart(2, '0');
            const mm = String(d.getMinutes()).padStart(2, '0');
            return `${d.getFullYear()}-${m}-${day} ${hh}:${mm}`;
        }

        function getSelectedWarehouseName() {
            const select = document.getElementById('warehouse_id');
            const opt = select?.options?.[select.selectedIndex];
            return opt ? String(opt.textContent || '').trim() : '';
        }

        function formatQty(v) {
            const n = Number(v);
            if (!Number.isFinite(n)) return '0';
            return String(n).replace(/\.0+$/, '');
        }

        function setFilterCount(visible, total) {
            const el = document.getElementById('product_filter_count');
            if (!el) return;
            el.textContent = `${visible} / ${total} termék`;
        }

        function setProductsLoading(isLoading) {
            const loader = document.getElementById('stocktake_products_loader');
            const container = document.getElementById('stocktake_products_container');
            if (loader) loader.style.display = isLoading ? '' : 'none';
            if (container) container.style.display = isLoading ? 'none' : '';
        }

        function syncItemsJson() {
            const out = [];
            itemsByProductId.forEach((val, productId) => {
                const qty = val === '' || val === null || val === undefined ? null : Number(val);
                if (qty === null || !Number.isFinite(qty)) {
                    return;
                }
                out.push({ product_id: Number(productId), counted_quantity: qty });
            });
            document.getElementById('items_json').value = JSON.stringify(out);
        }

        function renderProducts(products) {
            const container = document.getElementById('stocktake_products_container');
            container.innerHTML = '';

            let currentCategory = null;
            let visibleCount = 0;

            products.forEach(p => {
                if (p.category_title !== currentCategory) {
                    currentCategory = p.category_title;
                    const header = document.createElement('div');
                    header.className = 'fw-bold mt-3 mb-2';
                    header.style.borderBottom = '2px solid #dee2e6';
                    header.style.paddingBottom = '6px';
                    header.style.paddingTop = '6px';
                    header.style.paddingLeft = '8px';
                    header.style.paddingRight = '8px';
                    header.style.background = '#f1f5f9';
                    header.style.borderRadius = '6px';
                    header.textContent = currentCategory;
                    container.appendChild(header);
                }

                const row = document.createElement('div');
                row.className = 'd-flex align-items-center justify-content-between gap-2 py-2 border-bottom stocktake-row';
                row.dataset.productId = String(p.product_id);
                row.dataset.productTitle = String(p.title || '').toLowerCase();

                const left = document.createElement('div');
                left.className = 'flex-grow-1';
                left.innerHTML = `
                    <div class="fw-semibold">${escapeHtml(p.title || '')}</div>
                `;

                const right = document.createElement('div');
                right.className = 'd-flex align-items-center gap-2';

                const stockPlaceholder = `${formatQty(p.current_stock)}`;
                right.innerHTML = `
                    <input type="number" step="0.001" class="form-control form-control-sm stocktake-count" style="width: 160px;" placeholder="${escapeHtml(stockPlaceholder)}" value="${escapeHtml(p.counted_quantity ?? '')}">
                `;

                row.appendChild(left);
                row.appendChild(right);
                container.appendChild(row);
                visibleCount++;
            });

            setFilterCount(visibleCount, productsCache.length);
        }

        function applyFilter() {
            const q = String(document.getElementById('product_filter').value || '').trim().toLowerCase();

            const filtered = q === ''
                ? productsCache
                : productsCache.filter(p => String(p.title || '').toLowerCase().includes(q));

            renderProducts(filtered);
        }

        async function loadProductsForStocktake(stocktakeId = null) {
            const warehouseId = String(document.getElementById('warehouse_id').value || '').trim();
            if (!warehouseId) return;

            setProductsLoading(true);

            const url = stocktakeId
                ? `{{ url('/admin/raktarozas/leltar') }}/${stocktakeId}/products?warehouse_id=${encodeURIComponent(warehouseId)}`
                : `{{ url('/admin/raktarozas/leltar/products') }}?warehouse_id=${encodeURIComponent(warehouseId)}`;

            try {
                const resp = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });

                if (!resp.ok) {
                    throw new Error('Nem sikerült betölteni a termékeket.');
                }

                const json = await resp.json();
                productsCache = json?.products || [];

                itemsByProductId.clear();
                productsCache.forEach(p => {
                    const v = p.counted_quantity;
                    itemsByProductId.set(p.product_id, v === null || v === undefined ? '' : String(v));
                });

                renderProducts(productsCache);
                syncItemsJson();
                applyFilter();
            } finally {
                setProductsLoading(false);
            }
        }

        function resetForm(title = 'Új leltár') {
            document.getElementById('stocktakeForm').reset();
            document.getElementById('stocktakeModalLabel').textContent = title;
            document.getElementById('stocktake_id').value = '';
            document.getElementById('status').value = 'open';

            if (warehouses.length) {
                document.getElementById('warehouse_id').value = warehouses[0].id;
            }

            const whName = getSelectedWarehouseName();
            const nameSuggested = `Leltár - ${whName ? whName + ' - ' : ''}${nowDateTime()}`;
            document.getElementById('name').value = nameSuggested;

            document.getElementById('product_filter').value = '';
            document.getElementById('items_json').value = '[]';

            itemsByProductId.clear();
            productsCache = [];
            document.getElementById('stocktake_products_container').innerHTML = '';
            setProductsLoading(false);
            setFilterCount(0, 0);
        }

        $(document).ready(function () {
            const table = $('#adminTable').DataTable({
                language: { url: '/lang/datatables/hu.json' },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.warehouse.stocktakes.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'warehouse_name' },
                    {
                        data: 'status',
                        render(data) {
                            const v = String(data || '').toLowerCase();
                            if (v === 'closed') return 'Lezárt';
                            if (v === 'open') return 'Nyitott';
                            return data;
                        }
                    },
                    { data: 'started_at' },
                    { data: 'closed_at' },
                    { data: 'created' },
                    { data: 'action', orderable: false, searchable: false },
                ],
            });

            $('.filter-input').on('change keyup', function () {
                const i = $(this).attr('data-column');
                const v = $(this).val();
                table.columns(i).search(v).draw();
            });

            $('#addButton').on('click', async function () {
                resetForm('Új leltár');
                modal.show();
                try {
                    await loadProductsForStocktake(null);
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                }
            });

            $('#warehouse_id').on('change', async function () {
                const id = String($('#stocktake_id').val() || '').trim();
                try {
                    await loadProductsForStocktake(id ? id : null);
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                }
            });

            $('#product_filter').on('input', function () {
                applyFilter();
            });

            $('#stocktake_products_container').on('input', '.stocktake-count', function () {
                const row = this.closest('.stocktake-row');
                if (!row) return;
                const pid = Number(row.dataset.productId);
                if (!Number.isFinite(pid)) return;

                const v = String(this.value ?? '').trim();
                itemsByProductId.set(pid, v);
                syncItemsJson();
            });

            $('#adminTable').on('click', '.edit', async function () {
                resetForm('Leltár szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const id = row_data.id;
                $('#stocktake_id').val(id);

                try {
                    const resp = await fetch(`{{ url('/admin/raktarozas/leltar') }}/${id}`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        }
                    });
                    if (!resp.ok) throw new Error('Nem sikerült betölteni a leltárt.');

                    const json = await resp.json();
                    const st = json?.stocktake;

                    $('#warehouse_id').val(st.warehouse_id || (warehouses[0]?.id ?? ''));
                    $('#name').val(st.name || '');
                    $('#status').val(st.status || 'open');
                    $('#note').val(st.note || '');

                    modal.show();
                    await loadProductsForStocktake(id);
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                }
            });

            $('#adminTable').on('click', '.delete', async function () {
                if (!confirm('Biztosan törlöd?')) return;
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const id = row_data.id;

                try {
                    const resp = await fetch(`{{ url('/admin/raktarozas/leltar') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        }
                    });
                    if (!resp.ok) {
                        const json = await resp.json().catch(() => ({}));
                        throw new Error(json?.message || 'Hiba történt a törléskor.');
                    }
                    showToast('Sikeres törlés!', 'success');
                    table.ajax.reload(null, false);
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                }
            });

            $('#adminTable').on('click', '.pdf', function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const id = row_data.id;
                window.open(`{{ url('/admin/raktarozas/leltar') }}/${id}/pdf`, '_blank');
            });

            $('#stocktakeForm').on('submit', function (e) {
                e.preventDefault();

                syncItemsJson();

                if (!$('#status').val()) {
                    $('#status').val('open');
                }

                const formData = new FormData(this);
                formData.append('_token', csrfToken);

                const btn = $('#saveStocktake');
                const orig = btn.html();
                btn.prop('disabled', true).html('Mentés...');

                const id = String($('#stocktake_id').val() || '').trim();

                let url = '{{ route('admin.warehouse.stocktakes.store') }}';
                let method = 'POST';
                if (id) {
                    url = `{{ url('/admin/raktarozas/leltar') }}/${id}`;
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url,
                    method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success(resp) {
                        showToast(resp?.message || 'Sikeres mentés!', 'success');
                        table.ajax.reload(null, false);
                        modal.hide();
                    },
                    error(xhr) {
                        let msg = 'Hiba!';
                        if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                        showToast(msg, 'danger');
                    },
                    complete() {
                        btn.prop('disabled', false).html(orig);
                    }
                });
            });

            $('#saveAndCloseStocktake').on('click', function () {
                $('#status').val('closed');
                $('#stocktakeForm').trigger('submit');
            });
        });

    </script>
@endsection
