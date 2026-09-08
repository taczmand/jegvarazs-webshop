@extends('layouts.admin')

@section('content')
    <div class="container p-0">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Termékek / Termékcsoportok</h2>
            @if(auth('admin')->user()->can('create-product-group'))
                <button class="btn btn-success" id="addGroup"><i class="fas fa-plus me-1"></i> Új termékcsoport</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">
            @if(auth('admin')->user()->can('view-product-groups'))
                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Név" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select" id="quantity_discount_filter">
                            <option value="">Mennyiségi kedvezmény (összes)</option>
                            <option value="with">Csak kedvezményes</option>
                            <option value="without">Csak kedvezmény nélküli</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="groupsTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Név</th>
                        <th>Slug</th>
                        <th>Státusz</th>
                        <th>Létrehozva</th>
                        <th>Módosítva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a termékcsoportok megtekintéséhez.
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="groupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="groupForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="groupModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="group_id">

                        <div class="mb-3">
                            <label class="form-label">Név</label>
                            <input type="text" class="form-control" id="group_name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Leírás</label>
                            <textarea class="form-control" id="group_description" rows="4"></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="group_is_active" checked>
                            <label class="form-check-label" for="group_is_active">Aktív</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="saveGroup">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="groupQuantityDiscountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="groupQuantityDiscountForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mennyiségi kedvezmény (termékcsoport)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="pgqd_group_id">

                        <div class="alert alert-info">
                            Ha a kosárban a termékcsoport összes mennyisége eléri az alap mennyiséget, akkor a kedvezmény lépcsőnként nő.
                            Példa: alap mennyiség 5, lépés kedvezmény 2% → 5 db = 2%, 10 db = 4%, 15 db = 6%.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alap mennyiség (Y)</label>
                            <input type="number" min="1" class="form-control" id="pgqd_base_quantity" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lépés kedvezmény (%)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="pgqd_percent_per_step" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max. kedvezmény (%) (opcionális)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="pgqd_max_percent">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="pgqd_is_active" checked>
                            <label class="form-check-label" for="pgqd_is_active">Aktív</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kezdete (opcionális)</label>
                            <input type="datetime-local" class="form-control" id="pgqd_starts_at">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vége (opcionális)</label>
                            <input type="datetime-local" class="form-control" id="pgqd_ends_at">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="deleteGroupQuantityDiscount">Törlés</button>
                        <button type="submit" class="btn btn-primary">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="groupProductsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="groupProductsForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Termékek kezelése (termékcsoport)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="pgp_group_id">

                        <div class="mb-3">
                            <label class="form-label">Szűrés</label>
                            <input type="text" class="form-control" id="pgp_filter" placeholder="Kategória vagy termék név / ID">
                        </div>

                        <div class="d-flex gap-2 mb-3">
                            <button type="button" class="btn btn-outline-primary" id="pgp_select_all">Összes kijelölése</button>
                            <button type="button" class="btn btn-outline-secondary" id="pgp_select_none">Kijelölés törlése</button>
                        </div>

                        <div id="pgp_catalog" class="border rounded p-2" style="max-height: 55vh; overflow:auto;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="saveGroupProducts">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const groupModal = new bootstrap.Modal(document.getElementById('groupModal'));
        const groupQuantityDiscountModal = new bootstrap.Modal(document.getElementById('groupQuantityDiscountModal'));
        const groupProductsModal = new bootstrap.Modal(document.getElementById('groupProductsModal'));

        const toDateTimeLocal = (val) => {
            if (!val) return '';
            const v = String(val).replace(' ', 'T');
            return v.length >= 16 ? v.slice(0, 16) : v;
        };


        let pgpCatalogData = null;

        function updateCategoryCheckboxState(categoryId) {
            const catCb = document.querySelector(`input.pgp-category-checkbox[data-category-id="${categoryId}"]`);
            const productCbs = Array.from(document.querySelectorAll(`input.pgp-product-checkbox[data-category-id="${categoryId}"]`));
            if (!catCb || productCbs.length === 0) {
                return;
            }

            const checkedCount = productCbs.filter(cb => cb.checked).length;

            if (checkedCount === 0) {
                catCb.checked = false;
                catCb.indeterminate = false;
                return;
            }

            if (checkedCount === productCbs.length) {
                catCb.checked = true;
                catCb.indeterminate = false;
                return;
            }

            catCb.checked = false;
            catCb.indeterminate = true;
        }

        function applyProductFilter() {
            const q = String($('#pgp_filter').val() || '').trim().toLowerCase();
            const categoryBlocks = Array.from(document.querySelectorAll('[data-pgp-category-block]'));

            categoryBlocks.forEach(block => {
                const catTitle = String(block.getAttribute('data-category-title') || '').toLowerCase();
                const productRows = Array.from(block.querySelectorAll('[data-pgp-product-row]'));

                let anyVisible = false;

                productRows.forEach(row => {
                    const productTitle = String(row.getAttribute('data-product-title') || '').toLowerCase();
                    const productId = String(row.getAttribute('data-product-id') || '').toLowerCase();

                    const hit = q === '' || catTitle.includes(q) || productTitle.includes(q) || productId.includes(q);
                    row.style.display = hit ? '' : 'none';
                    if (hit) {
                        anyVisible = true;
                    }
                });

                block.style.display = (q === '' ? '' : (anyVisible ? '' : 'none'));
            });
        }

        function renderCatalog(categories, selectedIds) {
            const selected = new Set((selectedIds || []).map(v => parseInt(v, 10)));
            const container = document.getElementById('pgp_catalog');

            let html = '';
            categories.forEach(cat => {
                const catId = cat.id;
                const products = cat.products || [];

                html += `<div class="mb-2" data-pgp-category-block="1" data-category-title="${String(cat.title).replaceAll('"', '&quot;')}">
                    <div class="py-1 ps-2" style="position: sticky; top: 0; background: white; z-index: 1;">
                        <div class="form-check m-0 d-flex align-items-center gap-2">
                            <input type="checkbox" class="form-check-input pgp-category-checkbox" data-category-id="${catId}" id="pgp_cat_${catId}">
                            <label class="form-check-label fw-bold" for="pgp_cat_${catId}">${cat.title}</label>
                        </div>
                    </div>
                    <div class="ps-4">`;

                products.forEach(p => {
                    const checked = selected.has(parseInt(p.id, 10)) ? 'checked' : '';
                    const safeTitle = String(p.title).replaceAll('"', '&quot;');
                    html += `<div class="form-check" data-pgp-product-row="1" data-product-title="${safeTitle}" data-product-id="${p.id}">
                        <input class="form-check-input pgp-product-checkbox" type="checkbox" ${checked} data-category-id="${catId}" data-product-id="${p.id}" id="pgp_prod_${p.id}">
                        <label class="form-check-label" for="pgp_prod_${p.id}">#${p.id} - ${p.title}</label>
                    </div>`;
                });

                html += `</div></div>`;
            });

            container.innerHTML = html;

            const categoryCheckboxes = Array.from(document.querySelectorAll('input.pgp-category-checkbox'));
            categoryCheckboxes.forEach(cb => {
                const catId = cb.getAttribute('data-category-id');
                updateCategoryCheckboxState(catId);
            });

            container.onchange = function (e) {
                const target = e.target;

                if (target && target.classList.contains('pgp-category-checkbox')) {
                    const categoryId = target.getAttribute('data-category-id');
                    const productCbs = Array.from(document.querySelectorAll(`input.pgp-product-checkbox[data-category-id="${categoryId}"]`));
                    productCbs.forEach(pcb => {
                        pcb.checked = target.checked;
                    });
                    updateCategoryCheckboxState(categoryId);
                    return;
                }

                if (target && target.classList.contains('pgp-product-checkbox')) {
                    const categoryId = target.getAttribute('data-category-id');
                    updateCategoryCheckboxState(categoryId);
                }
            };
        }

        $(document).ready(function() {
            const table = $('#groupsTable').DataTable({
                language: { url: '/lang/datatables/hu.json' },
                processing: true,
                serverSide: true,
                ajax: {
                    url: `${window.appConfig.APP_URL}admin/termekcsoportok/data`,
                    data: function (d) {
                        d.quantity_discount = $('#quantity_discount_filter').val();
                    }
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'slug' },
                    { data: 'status', orderable: false, searchable: false },
                    { data: 'created' },
                    { data: 'updated' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            $('.filter-input').on('change keyup', function () {
                var i = $(this).attr('data-column');
                var v = $(this).val();
                table.columns(i).search(v).draw();
            });

            $('#quantity_discount_filter').on('change', function () {
                table.ajax.reload(null, false);
            });

            $('#addGroup').on('click', function () {
                $('#group_id').val('');
                $('#group_name').val('');
                $('#group_description').val('');
                $('#group_is_active').prop('checked', true);
                $('#groupModalLabel').text('Új termékcsoport');
                groupModal.show();
            });

            $('#groupsTable').on('click', '.edit', async function () {
                const row = table.row($(this).parents('tr')).data();
                const res = await fetch(`${window.appConfig.APP_URL}admin/termekcsoportok/${row.id}`);
                const json = await res.json();

                $('#group_id').val(json.id);
                $('#group_name').val(json.name);
                $('#group_description').val(json.description || '');
                $('#group_is_active').prop('checked', !!json.is_active);
                $('#groupModalLabel').text('Termékcsoport szerkesztése');
                groupModal.show();
            });

            $('#groupForm').on('submit', function (e) {
                e.preventDefault();

                const id = $('#group_id').val();
                const payload = {
                    name: $('#group_name').val(),
                    description: $('#group_description').val() || null,
                    is_active: $('#group_is_active').is(':checked') ? 1 : 0,
                    _token: csrfToken,
                };

                const url = id
                    ? `${window.appConfig.APP_URL}admin/termekcsoportok/${id}`
                    : `${window.appConfig.APP_URL}admin/termekcsoportok`;

                if (id) {
                    payload._method = 'PUT';
                }

                $.ajax({
                    url,
                    method: 'POST',
                    data: payload,
                    success(resp) {
                        showToast(resp.message || 'Sikeres mentés!', 'success');
                        groupModal.hide();
                        table.ajax.reload(null, false);
                    },
                    error(xhr) {
                        let msg = 'Hiba!';
                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');
                    }
                });
            });

            $('#groupsTable').on('click', '.delete', function () {
                const row = table.row($(this).parents('tr')).data();
                if (!confirm('Biztosan törölni szeretnéd ezt a termékcsoportot?')) return;

                $.ajax({
                    url: `${window.appConfig.APP_URL}admin/termekcsoportok/${row.id}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success(resp) {
                        showToast(resp.message || 'Sikeres törlés!', 'success');
                        table.ajax.reload(null, false);
                    },
                    error(xhr) {
                        showToast(xhr.responseJSON?.message || 'Hiba!', 'danger');
                    }
                });
            });

            $('#groupsTable').on('click', '.quantity-discount', async function () {
                const row = table.row($(this).parents('tr')).data();
                $('#pgqd_group_id').val(row.id);
                $('#pgqd_base_quantity').val('');
                $('#pgqd_percent_per_step').val('');
                $('#pgqd_max_percent').val('');
                $('#pgqd_is_active').prop('checked', true);
                $('#pgqd_starts_at').val('');
                $('#pgqd_ends_at').val('');

                const res = await fetch(`${window.appConfig.APP_URL}admin/termekcsoportok/${row.id}/mennyisegi-kedvezmeny`, {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (json.discount) {
                    const d = json.discount;
                    $('#pgqd_base_quantity').val(d.base_quantity);
                    $('#pgqd_percent_per_step').val(d.percent_per_step);
                    $('#pgqd_max_percent').val(d.max_percent ?? '');
                    $('#pgqd_is_active').prop('checked', !!d.is_active);
                    $('#pgqd_starts_at').val(toDateTimeLocal(d.starts_at));
                    $('#pgqd_ends_at').val(toDateTimeLocal(d.ends_at));
                }

                groupQuantityDiscountModal.show();
            });

            $('#groupQuantityDiscountForm').on('submit', function (e) {
                e.preventDefault();

                const id = $('#pgqd_group_id').val();
                const payload = {
                    base_quantity: $('#pgqd_base_quantity').val(),
                    percent_per_step: $('#pgqd_percent_per_step').val(),
                    max_percent: $('#pgqd_max_percent').val() || null,
                    is_active: $('#pgqd_is_active').is(':checked') ? 1 : 0,
                    starts_at: $('#pgqd_starts_at').val() || null,
                    ends_at: $('#pgqd_ends_at').val() || null,
                    _token: csrfToken,
                };

                $.ajax({
                    url: `${window.appConfig.APP_URL}admin/termekcsoportok/${id}/mennyisegi-kedvezmeny`,
                    method: 'POST',
                    data: payload,
                    success(resp) {
                        showToast(resp.message || 'Sikeres mentés!', 'success');
                        groupQuantityDiscountModal.hide();
                        table.ajax.reload(null, false);
                    },
                    error(xhr) {
                        let msg = 'Hiba!';
                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');
                    }
                });
            });

            $('#deleteGroupQuantityDiscount').on('click', function () {
                const id = $('#pgqd_group_id').val();
                if (!confirm('Biztosan törölni szeretnéd a termékcsoport kedvezményét?')) return;

                $.ajax({
                    url: `${window.appConfig.APP_URL}admin/termekcsoportok/${id}/mennyisegi-kedvezmeny`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success(resp) {
                        showToast(resp.message || 'Törölve!', 'success');
                        groupQuantityDiscountModal.hide();
                        table.ajax.reload(null, false);
                    },
                    error(xhr) {
                        showToast(xhr.responseJSON?.message || 'Hiba!', 'danger');
                    }
                });
            });

            $('#groupsTable').on('click', '.manage-products', async function () {
                const row = table.row($(this).parents('tr')).data();
                $('#pgp_group_id').val(row.id);

                $('#pgp_filter').val('');
                document.getElementById('pgp_catalog').innerHTML = 'Betöltés...';

                try {
                    const res = await fetch(`${window.appConfig.APP_URL}admin/termekcsoportok/${row.id}/termekek/katalogus`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        throw (json?.message || 'Hiba történt.');
                    }

                    pgpCatalogData = json;
                    renderCatalog(json.categories || [], json.selected_product_ids || []);
                    applyProductFilter();
                } catch (e) {
                    showToast(e, 'danger');
                    document.getElementById('pgp_catalog').innerHTML = '';
                }

                groupProductsModal.show();
            });

            $('#pgp_filter').on('keyup change', function () {
                applyProductFilter();
            });

            $('#pgp_select_all').on('click', function () {
                Array.from(document.querySelectorAll('input.pgp-product-checkbox')).forEach(cb => cb.checked = true);
                Array.from(document.querySelectorAll('input.pgp-category-checkbox')).forEach(cb => { cb.checked = true; cb.indeterminate = false; });
            });

            $('#pgp_select_none').on('click', function () {
                Array.from(document.querySelectorAll('input.pgp-product-checkbox')).forEach(cb => cb.checked = false);
                Array.from(document.querySelectorAll('input.pgp-category-checkbox')).forEach(cb => { cb.checked = false; cb.indeterminate = false; });
            });

            $('#groupProductsForm').on('submit', function (e) {
                e.preventDefault();

                const groupId = $('#pgp_group_id').val();
                const ids = Array.from(document.querySelectorAll('input.pgp-product-checkbox:checked'))
                    .map(cb => parseInt(cb.getAttribute('data-product-id'), 10))
                    .filter(v => Number.isFinite(v) && v > 0);

                $.ajax({
                    url: `${window.appConfig.APP_URL}admin/termekcsoportok/${groupId}/termekek/sync`,
                    method: 'POST',
                    data: { product_ids: ids, _token: csrfToken },
                    success(resp) {
                        showToast(resp.message || 'Sikeres!', 'success');
                        groupProductsModal.hide();
                    },
                    error(xhr) {
                        let msg = 'Hiba!';
                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');
                    }
                });
            });
        });
    </script>
@endsection
