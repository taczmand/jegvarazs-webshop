@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyviteli folyamatok / Ügyfelek</h2>
            @if(auth('admin')->user()->can('create-client'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új ügyfél</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-clients'))

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
                        <input type="text" placeholder="E-mail" class="filter-input form-control" data-column="2">
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Név</th>
                        <th data-priority="1">E-mail</th>
                        <th>Telefonszám</th>
                        <th>Megjegyzés</th>
                        <th>Létrehozva</th>
                        <th>Módosítva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod az ügyfelek megtekintéséhez.
                </div>
            @endif
        </div>
    </div>


    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="adminForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="client_id" name="id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Név / Cégnév</label>
                                <input type="text" class="form-control" id="name" name="name">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail cím*</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Telefonszám</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="col-12">
                                <label for="comment" class="form-label">Megjegyzés</label>
                                <textarea class="form-control" id="comment" name="comment" rows="2"></textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div id="addressesSection" class="d-none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0" id="addressesTitle">Címek</h5>
                                <button type="button" class="btn btn-sm btn-success" id="addAddressButton" style="display:none;">
                                    <i class="fas fa-plus me-1"></i> Új cím
                                </button>
                            </div>

                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered" id="addressesTable">
                                    <thead>
                                    <tr>
                                        <th>Alapértelmezett</th>
                                        <th>Megnevezés</th>
                                        <th>Ország</th>
                                        <th>Irányítószám</th>
                                        <th>Város</th>
                                        <th>Cím</th>
                                        <th>Műveletek</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="border rounded p-3" id="addressFormWrap" style="display:none;">
                                <input type="hidden" id="address_id">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="address_label" class="form-label">Megnevezés</label>
                                        <input type="text" class="form-control" id="address_label" name="address_label">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="address_country" class="form-label">Ország</label>
                                        <select class="form-control w-100" id="address_country" name="address_country">
                                            @foreach(config('countries') as $code => $name)
                                                <option value="{{ $code }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="address_zip_code" class="form-label">Irányítószám</label>
                                        <input type="text" class="form-control" id="address_zip_code" name="address_zip_code" autocomplete="off">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="address_city" class="form-label">Város</label>
                                        <input type="text" class="form-control" id="address_city" name="address_city">
                                        <div id="zip_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="address_address_line" class="form-label">Cím</label>
                                        <input type="text" class="form-control" id="address_address_line" name="address_address_line" autocomplete="off">
                                        <div id="street_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="address_is_default">
                                            <label class="form-check-label" for="address_is_default">Alapértelmezett cím</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="justify-content-end gap-2 mt-3" id="addressCrudButtons" style="display:none;">
                                    <button type="button" class="btn btn-secondary btn-sm" id="cancelAddress">Mégse</button>
                                    <button type="button" class="btn btn-primary btn-sm" id="saveAddress">Cím mentése</button>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-btn" id="saveClient">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
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
                ajax: '{{ route('admin.clients.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'comment' },
                    { data: 'created' },
                    { data: 'updated' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            $('#addButton').on('click', async function () {
                resetForm('Új ügyfél létrehozása');
                $('#addressesSection').removeClass('d-none');
                $('#addressesTable').closest('.table-responsive').hide();
                openAddressForm({
                    id: null,
                    label: '',
                    country: 'HU',
                    zip_code: '',
                    city: '',
                    address_line: '',
                    is_default: true,
                });
                $('#address_is_default').prop('checked', true);
                setCreateAddressUi(true);
                adminModal.show();
            });

            adminModalDOM.addEventListener('shown.bs.modal', function () {
                const isCreateMode = !($('#client_id').val() || '').trim();
                setCreateAddressUi(isCreateMode);
            });

            $('#adminTable').on('click', '.edit', async function () {
                resetForm('Ügyfél szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();

                $('#client_id').val(row_data.id);
                $('#name').val(row_data.name);
                $('#email').val(row_data.email);
                $('#phone').val(row_data.phone);
                $('#comment').val(row_data.comment);

                $('#addressesSection').removeClass('d-none');
                $('#addressesTable').closest('.table-responsive').show();
                closeAddressForm();
                setCreateAddressUi(false);
                await loadAddresses(row_data.id);

                adminModal.show();
            });

            $('#addAddressButton').on('click', function () {
                openAddressForm();
            });

            $('#cancelAddress').on('click', function () {
                closeAddressForm();
            });

            $('#saveAddress').on('click', function () {
                const clientId = $('#client_id').val();
                if (!clientId) {
                    showToast('Előbb mentsd el az ügyfelet, hogy címeket tudj hozzáadni.', 'warning');
                    return;
                }

                const addressId = $('#address_id').val();
                const payload = {
                    label: $('#address_label').val() || null,
                    country: $('#address_country').val() || 'HU',
                    zip_code: $('#address_zip_code').val() || null,
                    city: $('#address_city').val() || null,
                    address_line: $('#address_address_line').val() || null,
                    is_default: $('#address_is_default').is(':checked') ? 1 : 0,
                    _token: csrfToken,
                };

                let url = `${window.appConfig.APP_URL}admin/ugyfelek/${clientId}/cimek`;
                let method = 'POST';

                if (addressId) {
                    url = `${window.appConfig.APP_URL}admin/ugyfelek/cimek/${addressId}`;
                    payload._method = 'PUT';
                }

                $.ajax({
                    url,
                    method,
                    data: payload,
                    success: async function (response) {
                        showToast(response.message || 'Sikeres!', 'success');
                        closeAddressForm();
                        await loadAddresses(clientId);
                    },
                    error: function (xhr) {
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

            $('#addressesTable').on('click', '.edit-address', function () {
                const row = $(this).closest('tr');
                openAddressForm({
                    id: row.data('id'),
                    label: row.data('label'),
                    country: row.data('country'),
                    zip_code: row.data('zip'),
                    city: row.data('city'),
                    address_line: row.data('line'),
                    is_default: row.data('default') == 1,
                });
            });

            $('#addressesTable').on('click', '.delete-address', async function () {
                const clientId = $('#client_id').val();
                const addressId = $(this).closest('tr').data('id');
                if (!clientId || !addressId) return;
                if (!confirm('Biztosan törölni szeretnéd ezt a címet?')) return;

                $.ajax({
                    url: `${window.appConfig.APP_URL}admin/ugyfelek/cimek/${addressId}`,
                    method: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: csrfToken,
                    },
                    success: async function (response) {
                        showToast(response.message || 'Sikeres!', 'success');
                        await loadAddresses(clientId);
                    },
                    error: function (xhr) {
                        let msg = 'Hiba!';
                        if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                        showToast(msg, 'danger');
                    }
                });
            });

            $('#saveClient').on('click', function (e) {
                e.preventDefault();

                const form = document.getElementById('adminForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const clientId = $('#client_id').val();

                let url = '{{ route('admin.clients.store') }}';
                let method = 'POST';

                if (clientId) {
                    url = `${window.appConfig.APP_URL}admin/ugyfelek/${clientId}`;
                    formData.append('_method', 'PUT');
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
                        $(this).html(originalSaveButtonHtml).prop('disabled', false);
                    }
                });

            });

            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const clientId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt az ügyfelet?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/ugyfelek') }}/${clientId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Ügyfél sikeresen törölve!', 'success');
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt az ügyfél törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt az ügyfél törlésekor', 'danger');
                }
            });

            function resetForm(title = null) {
                $('#adminForm')[0].reset();
                $('#adminModalLabel').text(title);
                $('#client_id').val('');
                $('#comment').val('');
                $('#addressesTable tbody').empty();
                closeAddressForm();
                setCreateAddressUi(true);
            }

            function setCreateAddressUi(isCreateMode) {
                if (isCreateMode) {
                    $('#addressesTitle').text('Cím megadása');
                    $('#addAddressButton').hide();
                    $('#addressCrudButtons').hide();
                    $('#addressFormWrap').show();
                } else {
                    $('#addressesTitle').text('Címek');
                    $('#addAddressButton').show();
                    $('#addressCrudButtons').css('display', 'flex');
                }
            }

            let debounceTimeout;

            $('#address_zip_code').on('input', function () {
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
                }, 300);
            });

            $('#zip_suggestions').on('click', 'button', function () {
                $('#address_zip_code').val($(this).attr('data_zip'));
                $('#address_city').val($(this).text().trim());
                $('#zip_suggestions').hide();
            });

            let streetDebounceTimeout;

            $('#address_address_line').on('input', function () {
                clearTimeout(streetDebounceTimeout);

                streetDebounceTimeout = setTimeout(() => {
                    const city = ($('#address_city').val() || '').trim();
                    const q = ($('#address_address_line').val() || '').trim();

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
                        }
                    });
                }, 300);
            });

            $('#street_suggestions').on('click', 'button', function () {
                $('#address_address_line').val($(this).text().trim());
                $('#street_suggestions').hide();
            });

            async function loadAddresses(clientId) {
                if (!clientId) return;

                $.ajax({
                    url: `${window.appConfig.APP_URL}admin/ugyfelek/${clientId}/cimek`,
                    method: 'GET',
                    success: function (response) {
                        const addresses = response.addresses || [];
                        renderAddresses(addresses);
                    },
                    error: function () {
                        renderAddresses([]);
                    }
                });
            }

            function renderAddresses(addresses) {
                const tbody = $('#addressesTable tbody');
                tbody.empty();

                if (!addresses.length) {
                    tbody.append(`<tr><td colspan="7" class="text-muted">Nincs még cím rögzítve.</td></tr>`);
                    return;
                }

                addresses.forEach(a => {
                    const isDefault = a.is_default ? '<span class="badge bg-success">Igen</span>' : '';
                    tbody.append(`
                        <tr data-id="${a.id}" data-label="${escapeHtml(a.label || '')}" data-country="${escapeHtml(a.country || '')}" data-zip="${escapeHtml(a.zip_code || '')}" data-city="${escapeHtml(a.city || '')}" data-line="${escapeHtml(a.address_line || '')}" data-default="${a.is_default ? 1 : 0}">
                            <td>${isDefault}</td>
                            <td>${escapeHtml(a.label || '')}</td>
                            <td>${escapeHtml(a.country || '')}</td>
                            <td>${escapeHtml(a.zip_code || '')}</td>
                            <td>${escapeHtml(a.city || '')}</td>
                            <td>${escapeHtml(a.address_line || '')}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary edit-address" title="Szerkesztés"><i class="fas fa-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-danger delete-address" title="Törlés"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `);
                });
            }

            function openAddressForm(address = null) {
                $('#addressFormWrap').show();
                $('#address_id').val(address?.id || '');
                $('#address_label').val(address?.label || '');
                $('#address_country').val(address?.country || 'HU');
                $('#address_zip_code').val(address?.zip_code || '');
                $('#address_city').val(address?.city || '');
                $('#address_address_line').val(address?.address_line || '');
                $('#address_is_default').prop('checked', !!address?.is_default);

                const isCreateMode = !($('#client_id').val() || '').trim();
                if (isCreateMode) {
                    $('#addAddressButton').hide();
                    $('#addressCrudButtons').hide();
                }
            }

            function closeAddressForm() {
                $('#addressFormWrap').hide();
                $('#address_id').val('');
                $('#address_label').val('');
                $('#address_country').val('HU');
                $('#address_zip_code').val('');
                $('#address_city').val('');
                $('#address_address_line').val('');
                $('#address_is_default').prop('checked', false);
            }

            function escapeHtml(str) {
                return String(str)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }
        });
    </script>
@endsection
