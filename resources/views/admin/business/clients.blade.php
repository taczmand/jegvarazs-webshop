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

                <div id="emails-list" style="margin-top:10px; font-weight:bold; background-color: #f5f5f5; padding: 10px; border-radius: 5px;"></div>
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
                                <label for="mothers_name" class="form-label">Anyja neve</label>
                                <input type="text" class="form-control" id="mothers_name" name="mothers_name">
                            </div>
                            <div class="col-md-6">
                                <label for="place_of_birth" class="form-label">Születési hely</label>
                                <input type="text" class="form-control" id="place_of_birth" name="place_of_birth">
                            </div>
                            <div class="col-md-3">
                                <label for="date_of_birth" class="form-label">Születési idő</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                            </div>
                            <div class="col-md-3">
                                <label for="id_number" class="form-label">Személyi szám</label>
                                <input type="text" class="form-control" id="id_number" name="id_number">
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
                                        <th>Ország</th>
                                        <th>Irányítószám</th>
                                        <th>Város</th>
                                        <th>Cím</th>
                                        <th>Megjegyzés</th>
                                        <th>Műveletek</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="border rounded p-3" id="addressFormWrap" style="display:none;">
                                <input type="hidden" id="address_id">
                                <div class="row g-3">
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
                                    <div class="col-12">
                                        <label for="address_comment" class="form-label">Megjegyzés</label>
                                        <textarea class="form-control" id="address_comment" name="address_comment" rows="2"></textarea>
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

    <div class="modal fade" id="timelineModal" tabindex="-1" aria-labelledby="timelineModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="timelineModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <div class="modal-body">
                    <div id="clientTimelineList" style="max-height: 65vh; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">

        const adminModalDOM = document.getElementById('adminModal');
        const adminModal = new bootstrap.Modal(adminModalDOM);

        const timelineModalDOM = document.getElementById('timelineModal');
        const timelineModal = new bootstrap.Modal(timelineModalDOM);

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        $(document).ready(function() {
            function escapeHtml(str) {
                return String(str)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

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
                drawCallback: function(settings) {
                    const emails = table.column(2, { page: 'current' })
                        .data()
                        .toArray()
                        .filter(email => email)
                        .map(email => String(email).trim())
                        .filter(email => email !== '')
                        .join('; ');

                    $('#emails-list').text(emails);
                }
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
                    country: 'HU',
                    zip_code: '',
                    city: '',
                    address_line: '',
                    comment: '',
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
                $('#mothers_name').val(row_data.mothers_name);
                $('#place_of_birth').val(row_data.place_of_birth);
                $('#date_of_birth').val(row_data.date_of_birth);
                $('#id_number').val(row_data.id_number);
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

            $('#adminTable').on('click', '.timeline', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const clientId = row_data.id;

                const clientName = (row_data.name || '').trim();
                const clientEmail = (row_data.email || '').trim();
                const clientPhone = (row_data.phone || '').trim();
                const parts = [clientName, clientEmail || clientPhone ? `(${[clientEmail, clientPhone].filter(Boolean).join(' / ')})` : ''].filter(Boolean);
                $('#timelineModalLabel').text(parts.length ? `Ügyfél előzmények – ${parts.join(' ')}` : 'Ügyfél előzmények');

                $('#clientTimelineList').html('<div class="text-muted">Betöltés...</div>');
                timelineModal.show();

                try {
                    const response = await fetch(`${window.appConfig.APP_URL}admin/ugyfelek/${clientId}/timeline`, {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        }
                    });

                    if (!response.ok) {
                        $('#clientTimelineList').html('<div class="text-danger">Hiba történt az előzmények betöltésekor.</div>');
                        return;
                    }

                    const payload = await response.json();
                    const items = payload?.items || [];

                    if (!items.length) {
                        $('#clientTimelineList').html('<div class="text-muted">Nincs előzmény.</div>');
                        return;
                    }

                    const typeLabels = {
                        contract: 'Szerződés',
                        worksheet: 'Munkalap',
                        appointment: 'Időpont',
                        offer: 'Ajánlat',
                    };

                    const html = `
                        <div class="accordion" id="clientTimelineAccordion">
                            ${items.map((item, idx) => {
                                const label = typeLabels[item.type] || (item.type || 'Esemény');
                                const title = escapeHtml(item.title || label);
                                const date = escapeHtml(item.date || '');
                                const url = escapeHtml(item.url || '#');
                                const itemId = `timeline_item_${idx}`;
                                const collapseId = `timeline_collapse_${idx}`;
                                const lines = Array.isArray(item.lines) ? item.lines : [];
                                const note = (item.note || '').trim();
                                const products = Array.isArray(item.products) ? item.products : [];

                                const linesHtml = lines
                                    .filter(l => (l?.label || '').trim() !== '' || (l?.value || '').trim() !== '')
                                    .map(l => {
                                        const lLabel = escapeHtml(l.label || '');
                                        const lValue = escapeHtml(l.value || '');
                                        return `
                                            <div class="row mb-1">
                                                <div class="col-4 text-muted">${lLabel}</div>
                                                <div class="col-8">${lValue}</div>
                                            </div>
                                        `;
                                    }).join('');

                                const noteHtml = note ? `
                                    <hr class="my-2" />
                                    <div class="mb-2">
                                        <div class="text-muted mb-1">Megjegyzés</div>
                                        <div style="white-space: pre-wrap;">${escapeHtml(note)}</div>
                                    </div>
                                ` : '';

                                const productsHtml = products.length ? `
                                    <hr class="my-2" />
                                    <div class="text-muted mb-1">Termékek</div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Termék</th>
                                                    <th style="width: 90px">Db</th>
                                                    <th style="width: 140px">Bruttó ár</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${products.map(p => {
                                                    const pTitle = escapeHtml(p.title || '');
                                                    const pQty = escapeHtml(String(p.qty ?? ''));
                                                    const pPrice = (p.gross_price === null || p.gross_price === undefined || p.gross_price === '')
                                                        ? ''
                                                        : escapeHtml(String(p.gross_price));
                                                    return `<tr><td>${pTitle}</td><td>${pQty}</td><td>${pPrice}</td></tr>`;
                                                }).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                ` : '';

                                return `
                                    <div class="accordion-item" id="${itemId}">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}">
                                                <div class="w-100 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="badge bg-secondary me-2">${escapeHtml(label)}</span>
                                                        ${title}
                                                    </div>
                                                    <small class="text-muted ms-2">${date}</small>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="${collapseId}" class="accordion-collapse collapse" data-bs-parent="#clientTimelineAccordion">
                                            <div class="accordion-body">
                                                <div class="mb-2">
                                                    <a class="btn btn-sm btn-outline-primary" href="${url}" target="_blank" rel="noopener">Megnyitás új lapon</a>
                                                </div>
                                                ${linesHtml || '<div class="text-muted">Nincs részletes adat.</div>'}
                                                ${noteHtml}
                                                ${productsHtml}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    `;

                    $('#clientTimelineList').html(html);
                } catch (e) {
                    $('#clientTimelineList').html('<div class="text-danger">Hiba történt az előzmények betöltésekor.</div>');
                }
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
                    country: $('#address_country').val() || 'HU',
                    zip_code: $('#address_zip_code').val() || null,
                    city: $('#address_city').val() || null,
                    address_line: $('#address_address_line').val() || null,
                    comment: $('#address_comment').val() || null,
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
                    country: row.data('country'),
                    zip_code: row.data('zip'),
                    city: row.data('city'),
                    address_line: row.data('line'),
                    comment: row.data('comment'),
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
                $('#mothers_name').val('');
                $('#place_of_birth').val('');
                $('#date_of_birth').val('');
                $('#id_number').val('');
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
                        <tr data-id="${a.id}" data-comment="${escapeHtml(a.comment || '')}" data-country="${escapeHtml(a.country || '')}" data-zip="${escapeHtml(a.zip_code || '')}" data-city="${escapeHtml(a.city || '')}" data-line="${escapeHtml(a.address_line || '')}" data-default="${a.is_default ? 1 : 0}">
                            <td>${isDefault}</td>
                            <td>${escapeHtml(a.country || '')}</td>
                            <td>${escapeHtml(a.zip_code || '')}</td>
                            <td>${escapeHtml(a.city || '')}</td>
                            <td>${escapeHtml(a.address_line || '')}</td>
                            <td>${escapeHtml(a.comment || '')}</td>
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
                $('#address_country').val(address?.country || 'HU');
                $('#address_zip_code').val(address?.zip_code || '');
                $('#address_city').val(address?.city || '');
                $('#address_address_line').val(address?.address_line || '');
                $('#address_comment').val(address?.comment || '');
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
                $('#address_country').val('HU');
                $('#address_zip_code').val('');
                $('#address_city').val('');
                $('#address_address_line').val('');
                $('#address_comment').val('');
                $('#address_is_default').prop('checked', false);
            }
        });
    </script>
@endsection
