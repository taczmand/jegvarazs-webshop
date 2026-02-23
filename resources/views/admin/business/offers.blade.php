@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyviteli folyamatok / Ajánlatok</h2>
            @if(auth('admin')->user()->can('create-offer'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új ajánlat</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-offers'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>
                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Cím" class="filter-input form-control" data-column="1">
                    </div>
                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Név" class="filter-input form-control" data-column="2">
                    </div>
                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Irányítószám" class="filter-input form-control" data-column="4">
                    </div>
                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Város" class="filter-input form-control" data-column="5">
                    </div>
                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Cím" class="filter-input form-control" data-column="6">
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="3">Ajánlat megnevezése</th>
                        <th data-priority="1">Név</th>
                        <th>Ország</th>
                        <th>Irányítószám</th>
                        <th>Város</th>
                        <th>Cím</th>
                        <th>Készítette</th>
                        <th>Létrehozva</th>
                        <th>Látta</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod az ajánlatok megtekintésére.
                </div>
            @endif
        </div>
    </div>


    <!-- Modális ablak -->
    <div class="modal fade admin-modal-soft" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="adminModalForm" action="" method="POST">

                <input type="hidden" id="offer_id" name="offer_id">
                <input type="hidden" id="client_id" name="client_id">
                <input type="hidden" id="client_address_id" name="client_address_id">
                <input type="hidden" id="create_client" name="create_client" value="0">
                <input type="hidden" id="use_custom_address" name="use_custom_address" value="0">

                <div class="modal-content">
                    <div class="modal-header bg-gradient-custom">
                        <h5 class="modal-title" id="adminModalLabel">Vevő/partner szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">

                        <ul class="nav nav-tabs admin-modal-tabs" id="productTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#contact" type="button">Kapcsolati adatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#productmanager" type="button">Termékek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#offer" type="button">Ajánlat generálása</button></li>
                        </ul>

                        <div class="tab-content mt-3">

                            <!-- Kapcsolati adatok tab -->

                            <div class="tab-pane fade show active" id="contact">
                                <table class="table offer-contact-table admin-modal-form-table">
                                    <tbody>
                                    <tr>
                                        <td class="w-25">Ügyfél keresés</td>
                                        <td class="position-relative">
                                            <input type="text" class="form-control" id="client_search" placeholder="Név / e-mail / telefon..." autocomplete="off">
                                            <div id="client_search_results" class="list-group position-absolute w-100 admin-client-search-results" style="z-index: 1100; display:none; max-height: 260px; overflow-y: auto;"></div>
                                        </td>
                                    </tr>
                                    <tr class="offer-client-fields" style="display:none;">
                                        <td class="w-25">Ajánlat megnevezése*</td>
                                        <td><input type="text" class="form-control" id="title" name="title" required></td>
                                    </tr>
                                    <tr class="offer-client-fields" style="display:none;">
                                        <td class="w-25">Név*</td>
                                        <td><input type="text" class="form-control" id="contact_name" name="contact_name" required></td>
                                    </tr>
                                    <tr class="offer-client-fields" style="display:none;">
                                        <td>Ország*</td>
                                        <td>
                                            <select name="contact_country" class="form-control w-100" id="contact_country">
                                                @foreach(config('countries') as $code => $name)
                                                    <option value="{{ $code }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="offer-client-fields" style="display:none;">
                                        <td>Irányítószám</td>
                                        <td><input type="text" class="form-control" id="contact_zip_code" name="contact_zip_code"></td>
                                    </tr>
                                    <tr class="offer-client-fields" style="display:none;">
                                        <td>Város</td>
                                        <td>
                                            <input type="text" class="form-control" id="contact_city" name="contact_city">
                                            <div id="zip_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                        </td>

                                    </tr>
                                    <tr class="offer-client-fields" style="display:none;">
                                        <td>Cím</td>
                                        <td>
                                            <input type="text" class="form-control" id="contact_address_line" name="contact_address_line">
                                            <div id="street_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                        </td>
                                    </tr>
                                    <tr class="offer-client-fields" style="display:none;">
                                        <td>Telefonszám</td>
                                        <td><input type="text" class="form-control" id="contact_phone" name="contact_phone"></td>
                                    </tr>
                                    <tr class="offer-client-fields" style="display:none;">
                                        <td>E-mail cím*</td>
                                        <td><input type="email" class="form-control" id="contact_email" name="contact_email" required></td>
                                    </tr>
                                    <tr class="offer-client-fields" style="display:none;">
                                        <td>Megjegyzés</td>
                                        <td><textarea class="form-control" id="contact_description" name="contact_description" rows="3"></textarea></td>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tab-pane fade" id="productmanager">
                                <input type="text" class="form-control mb-3" id="productSearch" placeholder="Keresés a termékek között...">
                                <div style="max-height: 300px; overflow-y: auto">
                                    <table class="table table-bordered" id="productManagerTable">
                                        <thead>
                                        <tr>
                                            <th>Kiválasztás</th>
                                            <th>Termék</th>
                                            <th>Darab</th>
                                            <th>Bruttó egységár</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Termékek betöltése itt -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="offer">
                                <button type="submit" class="btn btn-primary d-none" id="generateOffer">
                                    <i class="fas fa-file-pdf"></i> Ajánlat generálása
                                </button>
                                <button type="button" class="btn btn-secondary d-none" id="previewOfferPdf">
                                    <i class="fas fa-eye"></i> PDF előnézet
                                </button>
                                <a href="" id="offer_pdf_link" target="_blank" class="btn btn-primary d-none">Generált PDF megtekintése</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">

        const adminModalDOM = document.getElementById('adminModal');
        const adminModal = new bootstrap.Modal(adminModalDOM);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const searchId = urlParams.get('id');
            const showModal = urlParams.get('modal');

            const table = $('#adminTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.offers.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'title' },
                    { data: 'name' },
                    { data: 'country' },
                    { data: 'zip_code' },
                    { data: 'city' },
                    { data: 'address_line' },
                    { data: 'creator_name' },
                    { data: 'created' },
                    { data: 'viewed_by' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            if (searchId) {
                const idFilter = $('.filter-input[data-column="0"]');
                idFilter.val(searchId);
                table.columns(0).search(searchId).draw();

                if (showModal) {
                    let opened = false;
                    table.one('draw', function () {
                        if (opened) return;
                        const data = table.row(0).data();
                        if (!data) return;
                        opened = true;

                        resetForm('Ajánlat megtekintése');
                        $('.offer-contact-table').find('input, select, textarea').prop('disabled', true);
                        setOfferActionsVisible(false);

                        (async () => {
                            try {
                                const offer_data = await loadOfferProducts(data.id);
                                const offer = offer_data.offer || {};
                                const offer_products = offer.products || [];

                                $('#offer_id').val(offer.id);
                                $('#title').val(offer.title);
                                $('#contact_name').val(offer.name);
                                $('#contact_country').val(offer.country);
                                $('#contact_zip_code').val(offer.zip_code);
                                $('#contact_city').val(offer.city);
                                $('#contact_address_line').val(offer.address_line);
                                $('#contact_phone').val(offer.phone);
                                $('#contact_email').val(offer.email);
                                $('#contact_description').val(offer.description);

                                $('#client_id').val(offer.client_id || '');
                                $('#client_address_id').val('');
                                $('#create_client').val('0');
                                $('#use_custom_address').val('0');

                                setClientFieldsVisible(true);
                                setSnapshotMode(!!offer.client_id);
                                const display = `${offer.name || ''}${offer.email ? ' (' + offer.email + ')' : ''}`.trim();
                                $('#client_search').val(display);
                                $('#client_search_results').hide().empty();

                                const productManagerTable = $('#productManagerTable tbody');
                                productManagerTable.empty();
                                offer_products.forEach(item => {
                                    const row = `
                                        <tr>
                                            <td>${item.id}</td>
                                            <td>${item.title}</td>
                                            <td>${item.pivot.quantity}</td>
                                            <td>${item.pivot.gross_price}</td>
                                        </tr>`;
                                    productManagerTable.append(row);
                                });

                                $('#offer_pdf_link').removeClass('d-none').attr('href', `${offer.pdf_path}`);

                                sendViewRequest("offer", data.id);
                                table.ajax.reload(null, false);
                                adminModal.show();
                            } catch (e) {
                                showToast('Hiba történt az ajánlat betöltésekor.', 'danger');
                            }
                        })();
                    });
                }
            }

            $('#previewOfferPdf').on('click', function (e) {
                e.preventDefault();

                const modalForm = document.getElementById('adminModalForm');
                const formData = new FormData(modalForm);
                formData.append('_token', csrfToken);

                fetch('{{ route('admin.offers.preview-pdf') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }).then(async (response) => {
                    if (response.status === 422) {
                        const payload = await response.json();
                        const errors = payload.errors || {};
                        const messages = [];

                        Object.keys(errors).forEach((key) => {
                            (errors[key] || []).forEach((msg) => messages.push(msg));
                        });

                        showToast(messages.length ? messages.join('\n') : (payload.message || 'Hibás adatok.'), 'danger');
                        return;
                    }

                    if (!response.ok) {
                        showToast('Hiba történt a PDF előnézet generálása során.', 'danger');
                        return;
                    }

                    const blob = await response.blob();
                    const blobUrl = URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                    setTimeout(() => URL.revokeObjectURL(blobUrl), 60 * 1000);
                }).catch(() => {
                    showToast('Hiba történt a PDF előnézet generálása során.', 'danger');
                });
            });

            // Új ajánlat létrehozása modal megjelenítése
            $('#addButton').on('click', async function () {
                try {
                    resetForm('Új ajánlat létrehozása');
                    loadProducts();
                    $('.offer-contact-table').find('input, select, textarea').prop('disabled', false);

                    setClientFieldsVisible(false);

                    setOfferActionsVisible(false);
                    $('#offer_pdf_link').addClass('d-none').removeAttr('href');
                } catch (error) {
                    showToast(error, 'danger');
                }
                adminModal.show();
            });

            $(document).on('input', '#productSearch', function () {
                const searchValue = $(this).val().toLowerCase();

                $('#productManagerTable tbody tr').each(function () {
                    const row = $(this);

                    // csak a termék sorokra keresünk, a kategória sort meghagyjuk
                    if (!row.hasClass('table-secondary')) {
                        const productName = row.find('label').text().toLowerCase();

                        if (productName.includes(searchValue)) {
                            row.show();
                        } else {
                            row.hide();
                        }
                    }
                });
            });

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');  // getting column index
                var v =$(this).val();  // getting search input value
                table.columns(i).search(v).draw();
            });

            let debounceTimeout;

            $('#contact_zip_code').on('input', function () {
                clearTimeout(debounceTimeout);

                debounceTimeout = setTimeout(() => {
                    let zip = $(this).val();

                    $.ajax({
                        url: window.appConfig.APP_URL + 'api/postal-codes/search?zip=' + zip,
                        type: 'GET',
                        success: function (data) {
                            const $suggestions = $('#zip_suggestions');
                            $suggestions.empty();

                            if (data.length > 0) {
                                data.forEach(row => {
                                    $suggestions.append(`
                                        <button type="button" class="list-group-item list-group-item-action city-item" data_zip="${row.zip}">
                                            ${row.city}
                                        </button>
                                    `);
                                });

                                $suggestions.show();
                            } else {
                                $suggestions.hide();
                            }
                        }
                    });

                }, 300); // 300 ms debounce
            });

            // Ha rákattintanak egy ajánlásra
            $('#zip_suggestions').on('click', 'button', function () {
                $('#contact_zip_code').val($(this).attr('data_zip'));
                $('#contact_city').val($(this).text().trim());
                $('#zip_suggestions').hide();
            });

            let streetDebounceTimeout;

            $('#contact_address_line').on('input', function () {
                clearTimeout(streetDebounceTimeout);

                streetDebounceTimeout = setTimeout(() => {
                    const city = ($('#contact_city').val() || '').trim();
                    const q = ($('#contact_address_line').val() || '').trim();

                    const $suggestions = $('#street_suggestions');
                    $suggestions.empty();

                    if (!city || q.length < 2) {
                        $suggestions.hide();
                        return;
                    }

                    $.ajax({
                        url: window.appConfig.APP_URL + 'api/streets/search?city=' + encodeURIComponent(city) + '&q=' + encodeURIComponent(q),
                        type: 'GET',
                        success: function (data) {
                            $suggestions.empty();

                            if (Array.isArray(data) && data.length > 0) {
                                data.forEach(function (name) {
                                    $suggestions.append(`
                                        <button type="button" class="list-group-item list-group-item-action street-item">
                                            ${name}
                                        </button>
                                    `);
                                });

                                $suggestions.show();
                            } else {
                                $suggestions.hide();
                            }
                        },
                        error: function () {
                            $suggestions.hide();
                        }
                    });
                }, 300);
            });

            $('#street_suggestions').on('click', 'button', function () {
                $('#contact_address_line').val($(this).text().trim());
                $('#street_suggestions').hide();
            });

            // Ajánlat megtekintése

            $('#adminTable').on('click', '.view', async function () {

                resetForm('Ajánlat megtekintése');
                $('.offer-contact-table').find('input, select, textarea').prop('disabled', true);

                setOfferActionsVisible(false);


                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();



                const offer_data = await loadOfferProducts(row_data.id);
                const offer = offer_data.offer || {};
                const offer_products = offer.products || [];

                // Kapcsolati adatok

                $('#offer_id').val(offer.id);
                $('#title').val(offer.title);
                $('#contact_name').val(offer.name);
                $('#contact_country').val(offer.country);
                $('#contact_zip_code').val(offer.zip_code);
                $('#contact_city').val(offer.city);
                $('#contact_address_line').val(offer.address_line);
                $('#contact_phone').val(offer.phone);
                $('#contact_email').val(offer.email);
                $('#contact_description').val(offer.description);

                $('#client_id').val(offer.client_id || '');
                $('#client_address_id').val('');
                $('#create_client').val('0');
                $('#use_custom_address').val('0');

                setClientFieldsVisible(true);
                setSnapshotMode(!!offer.client_id);
                const display = `${offer.name || ''}${offer.email ? ' (' + offer.email + ')' : ''}`.trim();
                $('#client_search').val(display);
                $('#client_search_results').hide().empty();

                // Termékek

                const productManagerTable = $('#productManagerTable tbody');
                productManagerTable.empty();
                offer_products.forEach(item => {
                    const row = `
                        <tr>
                            <td>${item.id}</td>
                            <td>${item.title}</td>
                            <td>${item.pivot.quantity}</td>
                            <td>${item.pivot.gross_price}</td>
                        </tr>`;
                    productManagerTable.append(row);
                });

                // Generált PDF link

                $('#offer_pdf_link').removeClass('d-none').attr('href', `${offer.pdf_path}`);

                sendViewRequest("offer", row_data.id);

                table.ajax.reload(null, false);

                adminModal.show();
            });

            // Ajánlat generálása

            $('#generateOffer').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                let url = '{{ route('admin.offers.store') }}';
                let method = 'POST';  // Alapértelmezett metódus

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success(response) {
                        showToast(response.message || 'Sikeres!', 'success');
                        table.ajax.reload(null, false);
                        adminModal.hide();
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

                    }
                });

            });

            // Ajánlat törlése
            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const offer_id = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt az ajánlatot?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/ajanlatok') }}/${offer_id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Ajánlat sikeresen törölve!', 'success');
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt az ajánlat törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt az ajánlat törlésekor', 'danger');
                }
            });

            function loadProducts() {
                const productManagerTable = $('#productManagerTable tbody');
                productManagerTable.empty();

                fetch(`${window.appConfig.APP_URL}admin/ajanlatok/ajanlat-termekek`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(category => {
                            const categoryRow = `
                    <tr class="table-secondary">
                        <td colspan="4"><strong>${category.title}</strong></td>
                    </tr>`;
                            productManagerTable.append(categoryRow);

                            category.products.forEach(item => {
                                const row = `
                        <tr>
                            <td>
                                <input
                                    type="checkbox"
                                    name="products[${item.id}][selected]"
                                    value="1"
                                    id="product_${item.id}"
                                >
                            </td>
                            <td><label for="product_${item.id}">${item.title}</label></td>
                            <td>
                                <input
                                    type="number"
                                    class="form-control"
                                    name="products[${item.id}][quantity]"
                                    value="1"
                                    min="1"
                                >
                            <td>
                                <input
                                    type="number"
                                    class="form-control"
                                    name="products[${item.id}][gross_price]"
                                    value="${item.gross_price}"
                                    step="0.01"
                                >
                            </td>
                        </tr>`;
                                productManagerTable.append(row);
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Hiba a termékek betöltésekor:', error);
                    });
            }




            async function loadOfferProducts(id) {
                try {
                    const response = await fetch(`{{ url('/admin/ajanlatok/termekek') }}/${id}`, {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    if (!response.ok) {
                        throw new Error('Hiba a termékek lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Lekérdezési hiba:', error);
                    return [];
                }
            }

            function setClientFieldsVisible(visible) {
                if (visible) {
                    $('.offer-client-fields').show();
                } else {
                    $('.offer-client-fields').hide();
                }

                $('#title').prop('required', !!visible);
                $('#contact_name').prop('required', !!visible);
                $('#contact_email').prop('required', !!visible);
            }

            function setOfferActionsVisible(visible) {
                if (visible) {
                    $('#generateOffer').removeClass('d-none');
                    $('#previewOfferPdf').removeClass('d-none');
                } else {
                    $('#generateOffer').addClass('d-none');
                    $('#previewOfferPdf').addClass('d-none');
                }
            }

            function resetForm(title = null) {
                $('#adminModalLabel').text(title);
                $('#adminModalForm')[0].reset();

                $('#offer_id').val('');
                clearClientSelection();
                $('#client_search').val('');
                $('#client_search_results').hide().empty();

                setClientFieldsVisible(false);
                setOfferActionsVisible(false);
            }

            function escapeHtml(str) {
                return String(str || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function setSnapshotMode(isSnapshot) {
                const disable = !!isSnapshot;

                const $inputs = $('#contact_name, #contact_zip_code, #contact_city, #contact_address_line');
                const $alwaysEditableInputs = $('#contact_phone, #contact_email');
                const $selects = $('#contact_country');

                $inputs.prop('readonly', disable);
                $alwaysEditableInputs.prop('readonly', false);
                $selects.toggleClass('snapshot-locked', disable);

                if (disable) {
                    $inputs.addClass('bg-light');
                    $alwaysEditableInputs.removeClass('bg-light');
                    $selects.addClass('bg-light');
                } else {
                    $inputs.removeClass('bg-light');
                    $alwaysEditableInputs.removeClass('bg-light');
                    $selects.removeClass('bg-light');
                }
            }

            function clearClientSelection() {
                $('#client_id').val('');
                $('#client_address_id').val('');
                $('#create_client').val('0');
                $('#use_custom_address').val('0');

                $('#contact_name').val('');
                $('#contact_email').val('');
                $('#contact_phone').val('');
                $('#contact_country').val('HU');
                $('#contact_zip_code').val('');
                $('#contact_city').val('');
                $('#contact_address_line').val('');

                setClientFieldsVisible(false);
                setOfferActionsVisible(false);
                setSnapshotMode(false);
            }

            let clientSearchDebounce;

            $('#client_search').on('input', function () {
                const q = ($(this).val() || '').trim();
                clearTimeout(clientSearchDebounce);

                $('#client_search_results').hide().empty();

                if ($('#client_id').val() || $('#create_client').val() === '1') {
                    clearClientSelection();
                }

                if (!q || q.length < 2) {
                    return;
                }

                clientSearchDebounce = setTimeout(() => {
                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/ugyfelek/kereses?q=${encodeURIComponent(q)}`,
                        method: 'GET',
                        success: function (response) {
                            const clients = response?.clients || [];
                            const $list = $('#client_search_results');
                            $list.empty();

                            if (clients.length) {
                                clients.forEach(c => {
                                    const name = c.name || '';
                                    const idNumber = c.id_number || '';
                                    const email = c.email || '';
                                    const phone = c.phone || '';

                                    const headerParts = [idNumber, email].filter(Boolean).join(', ');
                                    const addresses = Array.isArray(c.addresses) ? c.addresses : [];

                                    $list.append(`
                                        <div class="list-group-item client-search-header">
                                            <div class="fw-bold">${escapeHtml(name || email || 'N/A')}${headerParts ? ' (' + escapeHtml(headerParts) + ')' : ''}</div>
                                        </div>
                                    `);

                                    if (!addresses.length) {
                                        $list.append(`
                                            <button type="button" class="list-group-item list-group-item-action client-new-address"
                                                data-id="${c.id}"
                                                data-id-number="${escapeHtml(idNumber)}"
                                                data-name="${escapeHtml(name)}"
                                                data-email="${escapeHtml(email)}"
                                                data-phone="${escapeHtml(phone)}">
                                                <div class="fw-bold"><i class="fa-solid fa-circle-plus me-2"></i>Új cím</div>
                                                <div class="small text-muted">Nincs rögzített cím</div>
                                            </button>
                                        `);
                                        return;
                                    }

                                    addresses.forEach(a => {
                                        const addrText = `${a.zip_code || ''} ${a.city || ''}, ${a.address_line || ''}`.trim();
                                        const subtitle = [addrText].filter(Boolean).join(' | ');

                                        $list.append(`
                                            <button type="button" class="list-group-item list-group-item-action client-address-item"
                                                data-id="${c.id}"
                                                data-address-id="${a?.id || ''}"
                                                data-id-number="${escapeHtml(idNumber)}"
                                                data-name="${escapeHtml(name)}"
                                                data-email="${escapeHtml(email)}"
                                                data-phone="${escapeHtml(phone)}"
                                                data-country="${escapeHtml(a?.country || '')}"
                                                data-zip="${escapeHtml(a?.zip_code || '')}"
                                                data-city="${escapeHtml(a?.city || '')}"
                                                data-line="${escapeHtml(a?.address_line || '')}">
                                                <div class="fw-bold">${escapeHtml(subtitle || 'N/A')}${a?.is_default ? ' (alapértelmezett)' : ''}</div>
                                            </button>
                                        `);
                                    });

                                    $list.append(`
                                        <button type="button" class="list-group-item list-group-item-action client-new-address"
                                            data-id="${c.id}"
                                            data-id-number="${escapeHtml(idNumber)}"
                                            data-name="${escapeHtml(name)}"
                                            data-email="${escapeHtml(email)}"
                                            data-phone="${escapeHtml(phone)}">
                                            <div class="fw-bold"><i class="fa-solid fa-circle-plus me-2"></i>Új cím</div>
                                        </button>
                                    `);
                                });
                            }

                            $list.append(`
                                <button type="button" class="list-group-item list-group-item-action client-create client-create-item">
                                    <div class="fw-bold">Új ügyfél létrehozása</div>
                                    <div class="small text-muted">Az alábbi mezőkben megadott adatokkal</div>
                                </button>
                            `);

                            $list.show();
                        },
                        error: function () {
                            const $list = $('#client_search_results');
                            $list.empty();
                            $list.append(`
                                <button type="button" class="list-group-item list-group-item-action client-create client-create-item">
                                    <div class="fw-bold">Új ügyfél létrehozása</div>
                                    <div class="small text-muted">A keresés sikertelen volt</div>
                                </button>
                            `);
                            $list.show();
                        }
                    });
                }, 300);
            });

            $('#client_search_results').on('click', '.client-address-item', function () {
                const $btn = $(this);

                const clientId = $btn.data('id');
                const addressId = $btn.data('address-id') || null;
                const idNumber = $btn.data('id-number') || null;
                const name = $btn.data('name') || null;
                const email = $btn.data('email') || null;
                const phone = $btn.data('phone') || null;
                const country = $btn.data('country') || null;
                const zip = $btn.data('zip') || null;
                const city = $btn.data('city') || null;
                const line = $btn.data('line') || null;

                $('#client_id').val(clientId);
                $('#client_address_id').val(addressId);
                $('#create_client').val('0');
                $('#use_custom_address').val('0');

                $('#contact_name').val(name);
                $('#contact_email').val(email);
                $('#contact_phone').val(phone);
                $('#contact_country').val(country || 'HU');
                $('#contact_zip_code').val(zip);
                $('#contact_city').val(city);
                $('#contact_address_line').val(line);

                setClientFieldsVisible(true);
                setSnapshotMode(true);

                setOfferActionsVisible(true);

                const headerParts = [idNumber, email].filter(Boolean).join(', ');
                const display = `${name || ''}${headerParts ? ' (' + headerParts + ')' : ''}`.trim();
                $('#client_search').val(display);
                $('#client_search_results').hide().empty();
            });

            $('#client_search_results').on('click', '.client-new-address', function () {
                const $btn = $(this);

                const clientId = $btn.data('id');
                const idNumber = $btn.data('id-number') || null;
                const name = $btn.data('name') || null;
                const email = $btn.data('email') || null;
                const phone = $btn.data('phone') || null;

                $('#client_id').val(clientId);
                $('#client_address_id').val('');
                $('#create_client').val('0');
                $('#use_custom_address').val('1');

                $('#contact_name').val(name);
                $('#contact_email').val(email);
                $('#contact_phone').val(phone);
                $('#contact_country').val('HU');
                $('#contact_zip_code').val('');
                $('#contact_city').val('');
                $('#contact_address_line').val('');

                setClientFieldsVisible(true);
                setSnapshotMode(false);

                setOfferActionsVisible(true);

                const headerParts = [idNumber, email].filter(Boolean).join(', ');
                const display = `${name || ''}${headerParts ? ' (' + headerParts + ')' : ''}`.trim();
                $('#client_search').val(display);
                $('#client_search_results').hide().empty();

                setTimeout(() => {
                    $('#contact_zip_code').trigger('focus');
                }, 0);
            });

            $('#client_search_results').on('click', '.client-create', function () {
                $('#create_client').val('1');
                $('#client_id').val('');
                $('#client_address_id').val('');
                $('#use_custom_address').val('0');

                setClientFieldsVisible(true);
                setSnapshotMode(false);

                setOfferActionsVisible(true);

                $('#client_search').val('');
                $('#client_search_results').hide().empty();

                setTimeout(() => {
                    $('#contact_name').trigger('focus');
                }, 0);
            });
        });
    </script>
@endsection
