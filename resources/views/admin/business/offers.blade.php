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
                        <th data-priority="3">Cím</th>
                        <th data-priority="1">Név</th>
                        <th>Ország</th>
                        <th>Irányítószám</th>
                        <th>Város</th>
                        <th>Cím</th>
                        <th>Készítette</th>
                        <th>Létrehozva</th>
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
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="adminModalForm" action="" method="POST">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Vevő/partner szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">

                        <ul class="nav nav-tabs" id="productTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#contact" type="button">Kapcsolati adatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#productmanager" type="button">Termékek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#offer" type="button">Ajánlat generálás</button></li>
                        </ul>

                        <div class="tab-content mt-3">

                            <!-- Kapcsolati adatok tab -->

                            <div class="tab-pane fade show active" id="contact">
                                <table class="table table-bordered offer-contact-table">
                                    <tbody>
                                    <tr>
                                        <td class="w-25">Cím*</td>
                                        <td><input type="text" class="form-control" id="title" name="title" required></td>
                                    </tr>
                                    <tr>
                                        <td class="w-25">Név*</td>
                                        <td><input type="text" class="form-control" id="contact_name" name="contact_name" required></td>
                                    </tr>
                                    <tr>
                                        <td>Ország*</td>
                                        <td>
                                            <select name="contact_country" class="form-control w-100" id="contact_country">
                                                @foreach(config('countries') as $code => $name)
                                                    <option value="{{ $code }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Irányítószám*</td>
                                        <td><input type="text" class="form-control" id="contact_zip_code" name="contact_zip_code" required></td>
                                    </tr>
                                    <tr>
                                        <td>Város*</td>
                                        <td>
                                            <input type="text" class="form-control" id="contact_city" name="contact_city" required>
                                            <div id="zip_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>Cím*</td>
                                        <td><input type="text" class="form-control" id="contact_address_line" name="contact_address_line" required></td>
                                    </tr>
                                    <tr>
                                        <td>Telefonszám</td>
                                        <td><input type="text" class="form-control" id="contact_phone" name="contact_phone"></td>
                                    </tr>
                                    <tr>
                                        <td>E-mail cím</td>
                                        <td><input type="email" class="form-control" id="contact_email" name="contact_email"></td>
                                    </tr>
                                    <tr>
                                        <td>Megjegyzés</td>
                                        <td><textarea class="form-control" id="contact_description" name="contact_description" rows="3"></textarea></td>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tab-pane fade" id="productmanager">
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
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            // Új ajánlat létrehozása modal megjelenítése
            $('#addButton').on('click', async function () {
                try {
                    resetForm('Új ajánlat létrehozása');
                    loadProducts();
                    $('.offer-contact-table').find('input, select, textarea').prop('disabled', false);

                    $('#generateOffer').removeClass('d-none');
                    $('#offer_pdf_link').addClass('d-none').removeAttr('href');
                } catch (error) {
                    showToast(error, 'danger');
                }
                adminModal.show();
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

            // Ajánlat megtekintése

            $('#adminTable').on('click', '.view', async function () {

                resetForm('Ajánlat megtekintése');
                $('.offer-contact-table').find('input, select, textarea').prop('disabled', true);

                $('#generateOffer').addClass('d-none');


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

                sendViewRequest("offers", row_data.id);

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
                        table.ajax.reload();
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
                            table.ajax.reload();
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
                    showToast(error.message || 'Hiba történt a kategória törlésekor', 'danger');
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
                                >
                            </td>
                            <td>${item.title}</td>
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

            function resetForm(title = null) {
                $('#adminModalLabel').text(title);
                $('#adminModalForm')[0].reset();
            }
        });
    </script>
@endsection
