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
                        <ul class="nav nav-tabs" id="productTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#basic" type="button">Alapadatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#attributes" type="button">Egyedi tulajdonságok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tags" type="button">Címkék</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#images" type="button">Képek</button></li>
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
                                            <label class="form-label">Bruttó ár (Ft)*</label>
                                            <input type="number" step="0.01" class="form-control" name="gross_price" id="gross_price" required>
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
                                            <label class="form-label">Partner bruttó ár (Ft)</label>
                                            <input type="number" step="0.01" class="form-control" name="partner_gross_price" id="partner_gross_price">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">ÁFA (%)*</label>
                                            <select class="form-select" id="tax-select" name="tax_id" required>
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

        tinymce.init({
            selector: 'textarea#description',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table'
        });

        $(document).ready(function() {
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

            async function editProductModal(productId) {
                const allMetaData = await getAllMetaData();

                $('#uploadPhotosBtn').show();

                $.get(`{{ url('/admin/termekek') }}/${productId}`, function(data) {
                    const product = data.original.product;
                    const assigned_attributes = data.original.assigned_attributes;
                    const assignedAttributes = Object.fromEntries(assigned_attributes.map(a => [a.id, a.pivot.value]));
                    const assigned_tags = data.original.assigned_tags;
                    const assigned_photos = data.original.product_photos;
                    const cartOwners = data.original.cart_owners || [];

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

                    productModal.show();
                }).fail(function(xhr, status, error) {
                    showToast('Nem sikerült betölteni a termék adatait! ' + error, 'danger');
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
