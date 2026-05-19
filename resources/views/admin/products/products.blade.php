@extends('layouts.admin')

@section('content')
    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Termékek / Összes termék</h2>
            @if(auth('admin')->user()->can('create-product'))
                <button class="btn btn-success" id="addProduct"><i class="fas fa-plus me-1"></i> Új termék</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-products'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Terméknév" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="6">
                            <option value="">Kategória (összes)</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="7">
                            <option value="">Állapot (összes)</option>
                            <option value="active">Aktív</option>
                            <option value="inactive">Inaktív</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="productsTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Terméknév</th>
                        <th data-priority="6">Készlet</th>
                        <th data-priority="3">Bruttó ár</th>
                        <th data-priority="4">Partner bruttó ár</th>
                        <th>ÁFA</th>
                        <th data-priority="5">Kategória</th>
                        <th>Státusz</th>
                        <th>Létrehozva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a termékek megtekintéséhez.
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="productForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>

                    <div class="modal-body">
                        <ul class="nav nav-tabs admin-modal-tabs" id="productTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#basic" type="button">Alapadatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#attributes" type="button">Egyedi tulajdonságok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tags" type="button">Címkék</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#images" type="button">Képek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#quantity_discounts" type="button">Mennyiségi kedvezmények</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#history" type="button">Történet</button></li>
                        </ul>

                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="basic">
                                <input type="hidden" id="product_id" name="id">
                                <div class="row">
                                    <div class="col-12">
                                        <div id="product_in_carts_wrapper" class="alert alert-warning d-none">
                                            <div class="fw-bold mb-2">Kosárban van</div>
                                            <div id="product_in_carts_list"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Termék neve*</label>
                                            <input type="text" class="form-control" name="title" id="title" name="title" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Kategória*</label>
                                            <select class="form-select" id="categoriesSelect" name="category_id" required>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Márka</label>
                                            <select class="form-select" id="brands-select" name="brand_id">
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Státusz</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="inactive">Inaktív</option>
                                                <option value="active">Aktív</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" name="is_offerable" id="is_offerable" class="form-check-input" value="1">
                                            <label for="is_offerable" class="form-check-label">Szerződés és ajánlat generálásnál megjelenik?</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Készlet*</label>
                                                <input type="number" class="form-control" name="stock" id="stock" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Kiszerelési mennyiség*</label>
                                                <input type="number" class="form-control" name="unit_qty" id="unit_qty" value="1" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Mértékegység</label>
                                            <select class="form-select" id="unit-select" name="unit_id">
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">ÁFA (%)*</label>
                                            <select class="form-select" id="tax-select" name="tax_id" required>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Bruttó ár (Ft)*</label>
                                            <input type="number" step="0.01" class="form-control" name="gross_price" id="gross_price" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Partner bruttó ár (Ft)</label>
                                            <input type="number" step="0.01" class="form-control" name="partner_gross_price" id="partner_gross_price">
                                        </div>
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" name="is_selectable_by_installer" id="is_selectable_by_installer" class="form-check-input" value="1">
                                            <label for="is_selectable_by_installer" class="form-check-label">Munkalapnál megjelenik?</label>
                                        </div>
                                    </div>


                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Leírás</label>
                                    <textarea class="form-control" id="description" name="description" rows="6"></textarea>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="attributes">
                                <div id="attribute-fields"></div>
                            </div>

                            <div class="tab-pane fade" id="tags">
                                <div id="tags-checkboxes"></div>
                            </div>

                            <div class="tab-pane fade" id="images">
                                <div class="mb-3">
                                    <label class="form-label">Új képek feltöltése</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="file" class="form-control" name="new_photos[]" multiple accept="image/*">
                                        <button type="button" class="btn btn-sm btn-info" id="uploadPhotosBtn">Feltöltés</button>
                                    </div>
                                </div>


                                <div id="productPhotos" class="mt-3"></div>
                            </div>

                            <div class="tab-pane fade" id="quantity_discounts">
                                <div class="alert alert-info mb-3">
                                    Itt termékenként megadható több kedvezmény sáv (pl. 3+ db, 5+ db). A legnagyobb, még teljesülő mennyiségi sáv érvényesül.
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header fw-bold">Új kedvezmény</div>
                                    <div class="card-body">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-12 col-md-3">
                                                <label class="form-label">Min. mennyiség</label>
                                                <input type="number" min="1" class="form-control" id="qd_min_quantity">
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label">Típus</label>
                                                <select class="form-select" id="qd_discount_type">
                                                    <option value="percent">Százalék (%)</option>
                                                    <option value="fixed">Fix (Ft / db)</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label">Érték</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="qd_discount_value">
                                            </div>
                                            <div class="col-12 col-md-3">
                                                <label class="form-label">Aktív</label>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" id="qd_is_active" checked>
                                                    <label class="form-check-label" for="qd_is_active">Igen</label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label">Kezdete (opcionális)</label>
                                                <input type="datetime-local" class="form-control" id="qd_starts_at">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label">Vége (opcionális)</label>
                                                <input type="datetime-local" class="form-control" id="qd_ends_at">
                                            </div>
                                            <div class="col-12">
                                                <button type="button" class="btn btn-success" id="qd_add_btn">Hozzáadás</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header fw-bold">Meglévő kedvezmények</div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-0">
                                                <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Min. mennyiség</th>
                                                    <th>Típus</th>
                                                    <th>Érték</th>
                                                    <th>Kezdete</th>
                                                    <th>Vége</th>
                                                    <th>Aktív</th>
                                                    <th>Művelet</th>
                                                </tr>
                                                </thead>
                                                <tbody id="qd_table_body">
                                                <tr>
                                                    <td colspan="8" class="text-muted">Válassz ki egy mentett terméket.</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="history">
                                <div class="rounded bg-light p-2">
                                    <table class="table table-bordered display responsive nowrap mb-0" id="productHistoryTable" style="width:100%">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Felhasználó</th>
                                            <th>Akció</th>
                                            <th>Változás</th>
                                            <th>Időpont</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" id="saveProduct">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.tiny.cloud/1/k486ypuedp01hfc64g7mn3t9rc5lp8h53a5korymr6qvuvb9/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script type="module">
        const productModalDOM = document.getElementById('productModal');
        const productModal = new bootstrap.Modal(productModalDOM);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function formatValue(v) {
            if (v === null || typeof v === 'undefined') return '-';
            if (typeof v === 'boolean') return v ? 'Igen' : 'Nem';
            if (typeof v === 'object') return JSON.stringify(v);
            return String(v);
        }

        function productFieldLabel(field) {
            const map = {
                'title': 'Termék neve',
                'gross_price': 'Bruttó ár',
                'partner_gross_price': 'Partner bruttó ár',
                'stock': 'Készlet',
                'unit_qty': 'Kiszerelési mennyiség',
                'status': 'Státusz',
                'description': 'Leírás',
                'cat_id': 'Kategória',
                'category_id': 'Kategória',
                'brand_id': 'Márka',
                'tax_id': 'ÁFA',
                'is_offerable': 'Ajánlat/Szerződéshez használható',
                'is_selectable_by_installer': 'Munkalapnál megjelenik',
                'updated_at': 'Módosítva',
                'created_at': 'Létrehozva',
            };

            const f = String(field ?? '');
            return map[f] ?? f;
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
                        <td style="width: 30%; white-space: nowrap;"><strong>${escapeHtml(productFieldLabel(c.field))}</strong></td>
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

        tinymce.init({
            selector: 'textarea#description',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table'
        });

        $(document).ready(function() {
            let currentQuantityDiscounts = [];
            const table = $('#productsTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.products.data') }}',
                order: [[0, 'desc']],
                createdRow: function (row, data) {
                    if (data && data.in_cart) {
                        $(row).addClass('table-warning');
                    }
                },
                columns: [
                    { data: 'id' },
                    { data: 'title', className: 'no-ellipsis' },
                    { data: 'stock', className: 'editable', name: 'stock' },
                    { data: 'gross_price', className: 'editable', name: 'gross_price' },
                    { data: 'partner_gross_price', className: 'editable', name: 'partner_gross_price' },
                    { data: 'tax_value' },
                    { data: 'category' },
                    { data: 'status' },
                    { data: 'created_at' },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });

            // Szűrők beállítása

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            // Új termék létrehozása modal megjelenítése
            $('#addProduct').on('click', async function () {
                try {
                    resetForm('Új termék létrehozása');

                    const allMetaData = await getAllMetaData();
                    $('#uploadPhotosBtn').hide(); // Elrejtjük a feltöltés gombot új termék esetén

                    const treeCategories = buildCategoryTree(allMetaData.original.categories);
                    renderCategories(treeCategories);
                    renderBrands(allMetaData.original.brands);
                    renderAttributes(allMetaData.original.attributes);
                    renderTags(allMetaData.original.tags);
                    renderTaxes(allMetaData.original.taxes);
                    renderUnits(allMetaData.original.units);
                    renderPhotos([], null);  // Üres fotók kezdetben
                } catch (error) {
                    showToast(error, 'danger');
                }
                productModal.show();
            });

            // Termék mentése

            $('#saveProduct').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('productForm');
                tinymce.triggerSave();
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const productId = $('#product_id').val();

                let url = '{{ route('admin.products.store') }}';  // Ha nincs id, akkor új termék létrehozása
                let method = 'POST';  // Alapértelmezett metódus

                if (productId) {
                    url = `${window.appConfig.APP_URL}admin/termekek/${productId}`;  // update URL, ha van ID
                    formData.append('_method', 'PUT');  // PUT metódus jelzése
                }

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success(response) {
                        showToast(response.message || 'Sikeres!', 'success');
                        table.ajax.reload(null, false);
                        productModal.hide();
                    },
                    error(xhr) {
                        let msg = 'Hiba!';
                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');
                    },
                    complete: () => {
                        $(this).html(originalSaveButtonHtml).prop('disabled', false);
                    }
                });

            });

            // Új képek feltöltése
            $('#uploadPhotosBtn').on('click', function () {
                const productId = $('#product_id').val();
                if (!productId) {
                    showToast('Előbb mentsd el a terméket, mielőtt képeket töltesz fel!', 'warning');
                    return;
                }

                const form = document.getElementById('productForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                if (!formData.getAll('new_photos[]').length) {
                    showToast('Válassz ki legalább egy képet a feltöltéshez!', 'warning');
                    return;
                }

                $.ajax({
                    url: `${window.appConfig.APP_URL}admin/termekek/${productId}/upload-photos`,
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('#uploadPhotosBtn').html('Feltöltés...').prop('disabled', true);
                        showLoader();
                    },
                    success: async function (response) {
                        showToast(response.message || 'Képek sikeresen feltöltve!', 'success');
                        await editProductModal(productId);
                        // Töröljük a fájl inputot
                        form.querySelector('input[name="new_photos[]"]').value = '';
                    },
                    error(xhr) {
                        let msg = 'Hiba a képek feltöltésekor!';
                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');
                    },
                    complete: function() {
                        $('#uploadPhotosBtn').html('Feltöltés').prop('disabled', false);
                        hideLoader();
                    }
                });
            });

            // Termék szerkesztése

            $('#productsTable').on('click', '.edit', async function () {

                resetForm('Termék szerkesztése');

                const row_data = $('#productsTable').DataTable().row($(this).parents('tr')).data();
                const productId = row_data.id;
                await editProductModal(productId);
            });

            let productHistoryTable = null;

            async function editProductModal(productId) {
                const allMetaData = await getAllMetaData();

                $('#uploadPhotosBtn').show();

                $.get(`{{ url('/admin/termekek') }}/${productId}`, function(data) {
                    const payload = data?.original ?? data;
                    const product = payload.product;
                    const assigned_attributes = payload.assigned_attributes;
                    const assignedAttributes = Object.fromEntries(assigned_attributes.map(a => [a.id, a.pivot.value]));
                    const assigned_tags = payload.assigned_tags;
                    const assigned_photos = payload.product_photos;
                    const cartOwners = payload.cart_owners || [];
                    currentQuantityDiscounts = payload.quantity_discounts || [];

                    if (cartOwners.length) {
                        const html = cartOwners.map(function (owner) {
                            const name = owner?.name || 'Nincs név';
                            const email = owner?.email || 'Nincs email';
                            const phone = owner?.phone || 'Nincs telefon';
                            return `<div class="mb-2"><div><strong>${name}</strong></div><div class="text-muted" style="font-size: 0.9rem">${email} • ${phone}</div></div>`;
                        }).join('');
                        $('#product_in_carts_list').html(html);
                        $('#product_in_carts_wrapper').removeClass('d-none');
                    } else {
                        $('#product_in_carts_list').empty();
                        $('#product_in_carts_wrapper').addClass('d-none');
                    }

                    // Alapadatok betöltése
                    $('#product_id').val(product.id);
                    $('#title').val(product.title);
                    $('#gross_price').val(product.gross_price);
                    $('#partner_gross_price').val(product.partner_gross_price);
                    $('#stock').val(product.stock);
                    $('#unit_qty').val(product.unit_qty);
                    $('#status').val(product.status);
                    $('#is_offerable').prop('checked', product.is_offerable);
                    $('#is_selectable_by_installer').prop('checked', product.is_selectable_by_installer);
                    tinymce.get('description').setContent(product.description || '');

                    const treeCategories = buildCategoryTree(allMetaData.original.categories);
                    renderCategories(treeCategories, product.cat_id);

                    renderBrands(allMetaData.original.brands, product.brand_id);
                    renderAttributes(allMetaData.original.attributes, assignedAttributes);
                    renderTags(allMetaData.original.tags, assigned_tags);
                    renderPhotos(assigned_photos, product.id);
                    renderTaxes(allMetaData.original.taxes, product.tax_id);
                    renderUnits(allMetaData.original.units, product.unit_id);

                    renderQuantityDiscounts(currentQuantityDiscounts);
                    initProductHistory(product.id);

                    productModal.show();
                }).fail(function(xhr, status, error) {
                    showToast('Nem sikerült betölteni a termék adatait! ' + error, 'danger');
                });
            }

            function renderQuantityDiscounts(discounts) {
                const tbody = $('#qd_table_body');
                tbody.empty();

                const productId = $('#product_id').val();
                if (!productId) {
                    tbody.append('<tr><td colspan="8" class="text-muted">Előbb mentsd el a terméket.</td></tr>');
                    return;
                }

                if (!discounts || discounts.length === 0) {
                    tbody.append('<tr><td colspan="8" class="text-muted">Nincs felvett kedvezmény.</td></tr>');
                    return;
                }

                discounts.forEach(d => {
                    const starts = d.starts_at ? String(d.starts_at).slice(0, 16) : '';
                    const ends = d.ends_at ? String(d.ends_at).slice(0, 16) : '';
                    const checked = d.is_active ? 'checked' : '';

                    tbody.append(`
                        <tr data-discount-id="${d.id}">
                            <td>${d.id}</td>
                            <td><input type="number" class="form-control form-control-sm qd-edit" data-field="min_quantity" value="${d.min_quantity}" min="1"></td>
                            <td>
                                <select class="form-select form-select-sm qd-edit" data-field="discount_type">
                                    <option value="percent" ${d.discount_type === 'percent' ? 'selected' : ''}>percent</option>
                                    <option value="fixed" ${d.discount_type === 'fixed' ? 'selected' : ''}>fixed</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm qd-edit" data-field="discount_value" value="${d.discount_value}"></td>
                            <td><input type="datetime-local" class="form-control form-control-sm qd-edit" data-field="starts_at" value="${starts}"></td>
                            <td><input type="datetime-local" class="form-control form-control-sm qd-edit" data-field="ends_at" value="${ends}"></td>
                            <td class="text-center"><input type="checkbox" class="form-check-input qd-edit" data-field="is_active" ${checked}></td>
                            <td class="text-center" style="white-space: nowrap;">
                                <button type="button" class="btn btn-sm btn-primary qd-save">Mentés</button>
                                <button type="button" class="btn btn-sm btn-danger qd-delete">Törlés</button>
                            </td>
                        </tr>
                    `);
                });
            }

            async function refreshQuantityDiscounts(productId) {
                const res = await fetch(`{{ url('/admin/termekek') }}/${productId}/mennyisegi-kedvezmenyek`);
                const json = await res.json();
                currentQuantityDiscounts = json.discounts || [];
                renderQuantityDiscounts(currentQuantityDiscounts);
            }

            $('#qd_add_btn').on('click', async function () {
                const productId = $('#product_id').val();
                if (!productId) {
                    showToast('Előbb mentsd el a terméket!', 'warning');
                    return;
                }

                try {
                    const payload = {
                        min_quantity: Number($('#qd_min_quantity').val()),
                        discount_type: String($('#qd_discount_type').val()),
                        discount_value: Number($('#qd_discount_value').val()),
                        starts_at: $('#qd_starts_at').val() || null,
                        ends_at: $('#qd_ends_at').val() || null,
                        is_active: $('#qd_is_active').is(':checked') ? 1 : 0,
                    };

                    const response = await fetch(`{{ url('/admin/termekek') }}/${productId}/mennyisegi-kedvezmenyek`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    const json = await response.json();
                    if (!response.ok) {
                        throw new Error(json?.message || 'Hiba a mentéskor');
                    }

                    $('#qd_min_quantity').val('');
                    $('#qd_discount_value').val('');
                    $('#qd_starts_at').val('');
                    $('#qd_ends_at').val('');
                    $('#qd_is_active').prop('checked', true);

                    showToast(json.message || 'Mentve', 'success');
                    await refreshQuantityDiscounts(productId);
                } catch (e) {
                    showToast(e.message || 'Hiba történt', 'danger');
                }
            });

            $('#qd_table_body').on('click', '.qd-save', async function () {
                const tr = $(this).closest('tr');
                const discountId = tr.data('discount-id');

                const payload = {
                    min_quantity: Number(tr.find('[data-field="min_quantity"]').val()),
                    discount_type: String(tr.find('[data-field="discount_type"]').val()),
                    discount_value: Number(tr.find('[data-field="discount_value"]').val()),
                    starts_at: tr.find('[data-field="starts_at"]').val() || null,
                    ends_at: tr.find('[data-field="ends_at"]').val() || null,
                    is_active: tr.find('[data-field="is_active"]').is(':checked') ? 1 : 0,
                };

                const productId = $('#product_id').val();
                if (!productId) {
                    showToast('Nincs termék ID', 'danger');
                    return;
                }

                try {
                    const response = await fetch(`{{ url('/admin/termekek/mennyisegi-kedvezmenyek') }}/${discountId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    const json = await response.json();
                    if (!response.ok) {
                        throw new Error(json?.message || 'Hiba a frissítéskor');
                    }

                    showToast(json.message || 'Frissítve', 'success');
                    await refreshQuantityDiscounts(productId);
                } catch (e) {
                    showToast(e.message || 'Hiba történt', 'danger');
                }
            });

            $('#qd_table_body').on('click', '.qd-delete', async function () {
                const tr = $(this).closest('tr');
                const discountId = tr.data('discount-id');
                const productId = $('#product_id').val();
                if (!productId) {
                    showToast('Nincs termék ID', 'danger');
                    return;
                }

                if (!confirm('Biztosan törlöd ezt a kedvezményt?')) {
                    return;
                }

                try {
                    const response = await fetch(`{{ url('/admin/termekek/mennyisegi-kedvezmenyek') }}/${discountId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    const json = await response.json();
                    if (!response.ok) {
                        throw new Error(json?.message || 'Hiba a törléskor');
                    }

                    showToast(json.message || 'Törölve', 'success');
                    await refreshQuantityDiscounts(productId);
                } catch (e) {
                    showToast(e.message || 'Hiba történt', 'danger');
                }
            });

            function initProductHistory(productId) {
                if (!productId) {
                    return;
                }

                if (productHistoryTable) {
                    productHistoryTable.destroy();
                    $('#productHistoryTable').empty();
                }

                productHistoryTable = $('#productHistoryTable').DataTable({
                    language: {
                        url: '/lang/datatables/hu.json'
                    },
                    processing: true,
                    serverSide: true,
                    ajax: `{{ url('/admin/termekek') }}/${productId}/tortenet/data`,
                    order: [[0, 'desc']],
                    columns: [
                        { data: 'id' },
                        { data: 'user_name', defaultContent: '' },
                        { data: 'action' },
                        {
                            data: 'data',
                            orderable: false,
                            render: function (data, type, row) {
                                if (type !== 'display') {
                                    return data;
                                }

                                if (data && typeof data === 'object' && Array.isArray(data.changes)) {
                                    const changes = data.changes;
                                    if (changes.length === 0) {
                                        return `<span class="text-muted">-</span>`;
                                    }

                                    const preview = changes.slice(0, 2).map(c => `${productFieldLabel(c.field)}: ${formatValue(c.old)} → ${formatValue(c.new)}`).join(' | ');
                                    const more = changes.length > 2 ? ` (+${changes.length - 2})` : '';

                                    return `
                                        <div class="d-flex align-items-center gap-2" style="width: 100%;">
                                            <span class="text-truncate" style="max-width: 100%;">${escapeHtml(preview)}${escapeHtml(more)}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary toggle-history-details">Részletek</button>
                                        </div>
                                    `;
                                }

                                const stringData = typeof data === 'string' ? data : JSON.stringify(data ?? '');
                                const shortText = stringData.length > 80 ? stringData.substring(0, 77) + '...' : stringData;
                                return `<span>${escapeHtml(shortText)}</span>`;
                            }
                        },
                        { data: 'created_at' },
                    ],
                });

                $('#productHistoryTable').off('click.historyDetails').on('click.historyDetails', 'button.toggle-history-details', function () {
                    const tr = $(this).closest('tr');
                    const row = productHistoryTable.row(tr);
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
            }

            // Termék törlése
            $('#productsTable').on('click', '.delete', async function () {
                const row_data = $('#productsTable').DataTable().row($(this).parents('tr')).data();
                const productId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt a terméket?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/termekek') }}/${productId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Termék sikeresen törölve!', 'success');
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a termék törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt a termék törlésekor', 'danger');
                }
            });
            async function getAllMetaData() {
                try {
                    const response = await fetch(`{{ url('/admin/termekek/meta') }}`);
                    if (!response.ok) {
                        throw new Error('Hiba a metaadatok lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Metaadat hiba:', error);
                    return [];
                }
            }

            function buildCategoryTree(categories) {
                const map = {};
                const roots = [];

                categories.forEach(category => {
                    map[category.id] = { ...category, children: [] };
                });

                categories.forEach(category => {
                    if (category.parent_id) {
                        if (map[category.parent_id]) {
                            map[category.parent_id].children.push(map[category.id]);
                        }
                    } else {
                        roots.push(map[category.id]);
                    }
                });

                return roots;
            }

            function renderCategories(categories, selectedId = null) {
                const categorySelect = $('#categoriesSelect');
                categorySelect.empty().append('<option value="">-- Válassz kategóriát --</option>');

                function traverse(categories, prefix = '') {
                    categories.forEach(category => {
                        const label = prefix ? `${prefix} / ${category.title}` : category.title;
                        const selected = selectedId === category.id ? 'selected' : '';
                        categorySelect.append(`<option value="${category.id}" ${selected}>${label}</option>`);

                        if (category.children && category.children.length > 0) {
                            traverse(category.children, label);
                        }
                    });
                }

                traverse(categories);
            }

            function renderBrands(brands, assignedBrandId = null) {
                const brandSelect = $('#brands-select');
                brandSelect.empty();
                brandSelect.append(`
                        <option value="">Válassz a listából</option>
                    `);
                brands.forEach(brand => {
                    const selected = assignedBrandId === brand.id ? 'selected' : '';
                    brandSelect.append(`
                        <option value="${brand.id}" ${selected}>${brand.title}</option>
                    `);
                });
            }
            function renderAttributes(attributes, assignedAttributes = {}) {

                const attrContainer = $('#attribute-fields');
                attrContainer.empty();
                attributes.forEach(attr => {
                    attrContainer.append(`
                        <div class="mb-3">
                            <label class="form-label">${attr.name}</label>
                            <input type="text" class="form-control" name="attributes[${attr.id}]" value="${assignedAttributes[attr.id] || ''}">
                        </div>
                    `);
                });
            }
            function renderTags(tags, assignedTagIds = []) {

                const tagsContainer = $('#tags-checkboxes');
                tagsContainer.empty();

                tags.forEach(tag => {
                    const checked = assignedTagIds.includes(tag.id) ? 'checked' : '';
                    tagsContainer.append(`
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="tags[]" value="${tag.id}" id="tag_${tag.id}" ${checked}>
                            <label class="form-check-label" for="tag_${tag.id}">${tag.name}</label>
                        </div>
                    `);
                });
            }
            function renderTaxes(taxes, assignedTaxId = null) {
                const taxSelect = $('#tax-select');
                taxSelect.empty();
                taxes.forEach(tax => {
                    const selected = assignedTaxId === tax.id ? 'selected' : '';
                    taxSelect.append(`
                        <option value="${tax.id}" ${selected}>${tax.tax_value} (${tax.tax_name})</option>
                    `);
                });
            }

            function renderUnits(units, assignedUnitId = null) {
                const unitSelect = $('#unit-select');
                unitSelect.empty();
                unitSelect.append(`<option value="">Nincs megadva</option>`);
                (units || []).forEach(unit => {
                    const selected = assignedUnitId === unit.id ? 'selected' : '';
                    const label = unit.abbreviation ? `${unit.name} (${unit.abbreviation})` : unit.name;
                    unitSelect.append(`<option value="${unit.id}" ${selected}>${label}</option>`);
                });
            }
            function renderPhotos(photos, productId) {
                const container = $('#productPhotos');
                container.empty();

                if (!photos || photos.length === 0) {
                    container.append('<p class="text-muted">Nincs feltöltött kép.</p>');
                    return;
                }

                const table = $(`
                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kép</th>
                                    <th>Alt szöveg</th>
                                    <th>Főkép?</th>
                                    <th>Törlés</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                `);

                const tbody = table.find('tbody');

                photos.forEach(photo => {
                    const isPrimary = photo.is_main ? 'checked' : '';
                    const alt = photo.alt || '';

                    const row = $(`
                        <tr data-photo-id="${photo.id}">
                            <td><a href="${window.appConfig.APP_URL}storage/${photo.path}" target="_blank"><img src="${window.appConfig.APP_URL}storage/${photo.path}" alt="${alt}" class="img-thumbnail" style="width: 100px;"></a></td>
                            <td>
                                <input type="text" class="form-control form-control-sm photo-alt-input" value="${alt}" data-photo-id="${photo.id}">
                            </td>
                            <td class="text-center">
                                <input type="radio" name="primary_photo" value="${photo.id}" ${isPrimary} class="photo-primary-radio" data-photo-id="${photo.id}">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger delete-photo" data-photo-id="${photo.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);

                    tbody.append(row);
                });

                container.append(table);

                // Események
                container.off('blur', '.photo-alt-input').on('blur', '.photo-alt-input', function () {
                    const photoId = $(this).data('photo-id');
                    const altText = $(this).val();

                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/termekek/update-photo-alt`,
                        method: 'PATCH',
                        data: { id: photoId, alt: altText, _token: $('meta[name="csrf-token"]').attr('content') },
                        success: () => showToast('Alt szöveg frissítve', 'success'),
                        error: () => showToast('Nem sikerült menteni az alt szöveget', 'danger')
                    });
                });

                container.off('change', '.photo-primary-radio').on('change', '.photo-primary-radio', function () {
                    const photoId = $(this).data('photo-id');

                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/termekek/set-primary-photo`,
                        method: 'PATCH',
                        data: { id: photoId, _token: $('meta[name="csrf-token"]').attr('content') },
                        success: () => showToast('Főkép beállítva', 'success'),
                        error: () => showToast('Nem sikerült beállítani a főképet', 'danger')
                    });
                });

                container.off('click', '.delete-photo').on('click', '.delete-photo', function () {
                    const photoId = $(this).data('photo-id');
                    const row = $(this).closest('tr');

                    if (!confirm('Biztosan törölni szeretnéd ezt a képet?')) return;

                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/termekek/delete-photo`,
                        method: 'DELETE',
                        data: { id: photoId, _token: $('meta[name="csrf-token"]').attr('content') },
                        success: () => {
                            row.remove();
                            showToast('Kép törölve', 'success');
                        },
                        error: () => showToast('Nem sikerült törölni a képet', 'danger')
                    });
                });
            }

            $('#productsTable').on('dblclick', 'td.editable', function () {
                const cell = $(this);
                const colIndex = cell.index();
                const originalText = cell.text().trim();

                // oszlopnév a columns definícióból
                const column = table.settings().init().columns[colIndex].data;

                // ha már input van benne, ne csináljon semmit
                if (cell.find('input').length) return;

                // csak a számot hagyjuk meg (Ft, szóköz, pont, vessző nélkül)
                const cleanedValue = originalText
                    .replace(/[^0-9,.\-]/g, '') // minden nem szám, nem pont, nem vessző eltávolítása
                    .replace(',', '.'); // ha vesszőt használ tizedes elválasztónak

                const input = $('<input type="text" class="form-control form-control-sm">')
                    .val(cleanedValue);
                cell.empty().append(input);
                input.focus();

                // ENTER ment, ESC visszavon
                input.on('keydown blur', function (e) {
                    if (e.key === 'Escape') {
                        cell.text(originalText);
                        return;
                    }

                    if (e.type === 'blur' || e.key === 'Enter') {
                        const newValue = input.val().trim();
                        if (newValue === cleanedValue) {
                            cell.text(originalText);
                            return;
                        }

                        const rowData = table.row(cell.closest('tr')).data();

                        $.ajax({
                            url: `${window.appConfig.APP_URL}admin/termekek/update-inline`,
                            type: 'POST',
                            data: {
                                id: rowData.id,
                                field: column,
                                value: newValue,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function () {
                                table.ajax.reload(null, false);
                                showToast('Termék adatai sikeresen módosultak', 'success');
                            },
                            error: function (xhr) {
                                console.error(xhr.message);
                                showToast('Hiba történt a mentés során!', 'danger');
                                cell.text(originalText);
                            }
                        });
                    }
                });
            });




            function resetForm(title = null) {
                $('#productForm')[0].reset();
                $('#product_id').val('');
                $('#attribute-fields').empty();

                currentQuantityDiscounts = [];
                renderQuantityDiscounts([]);

                if (productHistoryTable) {
                    productHistoryTable.destroy();
                    productHistoryTable = null;
                    $('#productHistoryTable').empty();
                }
                $('#tags-checkboxes').empty();
                $('#productPhotos').empty();
                tinymce.get('description').setContent('');
                $('#product_in_carts_list').empty();
                $('#product_in_carts_wrapper').addClass('d-none');

                if(title){
                    $('#productModalLabel').text(title);
                }
            }

            const firstTab = new bootstrap.Tab(document.querySelector('#productTab .nav-link[data-bs-target="#basic"]'));
            firstTab.show();
        });
    </script>
@endsection
