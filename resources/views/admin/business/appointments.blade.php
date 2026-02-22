@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyviteli folyamatok / Időpontfoglalások</h2>
            @if(auth('admin')->user()->can('create-appointment'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új időpontfoglalás</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-appointments'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Ügyfélnév" class="filter-input form-control" data-column="1">
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
                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="11">
                            <option value="">Állapot (összes)</option>
                            <option value="Függőben">Függőben</option>
                            <option value="Folyamatban">Folyamatban</option>
                            <option value="Kész">Kész</option>
                            <option value="Törölve">Törölve</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="appointmentsTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th data-priority="1">Ügyfélnév</th>
                            <th style="display:none">E-mail</th>
                            <th style="display:none">Telefonszám</th>
                            <th style="display:none">Irányítószám</th>
                            <th>Város</th>
                            <th>Cím</th>
                            <th>Dátum</th>
                            <th>Típus</th>
                            <th style="display:none">Megjegyzés</th>
                            <th>Látta</th>
                            <th>Állapot</th>
                            <th>Létrehozva</th>
                            <th data-priority="2">Műveletek</th>
                        </tr>
                        </thead>
                </table>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultsága az időpontfoglalások megtekintéséhez.
                </div>
            @endif
        </div>
    </div>


    <!-- Modális ablak -->
    <div class="modal fade admin-modal-soft" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="appointmentForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-custom">
                        <h5 class="modal-title" id="appointmentModalLabel">Időpontfoglalás szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="appointment_id" name="id">
                        <input type="hidden" id="client_id" name="client_id">
                        <input type="hidden" id="client_address_id" name="client_address_id">
                        <input type="hidden" id="create_client" name="create_client" value="0">
                        <input type="hidden" id="use_custom_address" name="use_custom_address" value="0">

                        <ul class="nav nav-tabs admin-modal-tabs" id="appointmentTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#basic" type="button">Alapadatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#images" type="button">Képek</button></li>
                        </ul>

                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="basic">
                                <table class="table admin-modal-form-table">
                                    <tbody>
                                    <tr>
                                        <td class="w-25">Ügyfél keresés</td>
                                        <td class="position-relative">
                                            <input type="text" class="form-control" id="client_search" placeholder="Név / e-mail / telefon..." autocomplete="off">
                                            <div id="client_search_results" class="list-group position-absolute w-100 admin-client-search-results" style="z-index: 1100; display:none; max-height: 260px; overflow-y: auto;"></div>
                                        </td>
                                    </tr>
                                    <tr class="appointment-client-fields" style="display:none;">
                                        <td>Ügyfélnév*</td>
                                        <td><input type="text" class="form-control" id="name" name="name" required></td>
                                    </tr>
                                    <tr class="appointment-client-fields" style="display:none;">
                                        <td>E-mail cím</td>
                                        <td><input type="email" class="form-control" id="email" name="email"></td>
                                    </tr>
                                    <tr class="appointment-client-fields" style="display:none;">
                                        <td>Telefonszám*</td>
                                        <td><input type="text" class="form-control" id="phone" name="phone" required></td>
                                    </tr>
                                    <tr class="appointment-client-fields" style="display:none;">
                                        <td>Irányítószám*</td>
                                        <td><input type="text" class="form-control" id="zip_code" name="zip_code" required></td>
                                    </tr>
                                    <tr class="appointment-client-fields" style="display:none;">
                                        <td>Város*</td>
                                        <td class="position-relative">
                                            <input type="text" class="form-control" id="city" name="city" required>
                                            <div id="zip_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                        </td>
                                    </tr>
                                    <tr class="appointment-client-fields" style="display:none;">
                                        <td>Cím*</td>
                                        <td class="position-relative">
                                            <input type="text" class="form-control" id="address_line" name="address_line" required>
                                            <div id="street_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Dátum*</td>
                                        <td><input type="date" class="form-control" id="appointment_date" name="appointment_date" required></td>
                                    </tr>
                                    <tr>
                                        <td>Típus*</td>
                                        <td>
                                            <select id="appointment_type" name="appointment_type" class="form-control">
                                                <option value="Karbantartás">Karbantartás</option>
                                                <option value="Felmérés">Felmérés</option>
                                                <option value="Egyéb">Egyéb</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Állapot*</td>
                                        <td>
                                            <select id="status" name="status" class="form-control">
                                                <option value="Függőben">Függőben</option>
                                                <option value="Folyamatban">Folyamatban</option>
                                                <option value="Kész">Kész</option>
                                                <option value="Törölve">Törölve</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Üzenet</td>
                                        <td><textarea name="message" id="message" rows="3" class="form-control"></textarea></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tab-pane fade" id="images">
                                <div class="mb-3">
                                    <label class="form-label">Új képek feltöltése</label>
                                    <input type="file" class="form-control" name="new_photos[]" multiple accept="image/*">
                                </div>

                                <div id="appointmentPhotos" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-btn" id="saveAppointment">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">

        const urlParams = new URLSearchParams(window.location.search);
        const searchId = urlParams.get('id');
        const appointmentModalDOM = document.getElementById('appointmentModal');
        const appointmentModal = new bootstrap.Modal(appointmentModalDOM);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        $(document).ready(function() {
            const table = $('#appointmentsTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.appointments.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'email', visible: false },
                    { data: 'phone', visible: false },
                    { data: 'zip_code', visible: false },
                    { data: 'city' },
                    { data: 'address_line' },
                    { data: 'appointment_date' },
                    { data: 'appointment_type' },
                    { data: 'message', visible: false },
                    { data: 'viewed_by' },
                    { data: 'status' },
                    { data: 'created_at' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            // Szűrők beállítása

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            if (searchId) {
                $('.filter-input[data-name="id"]').val(searchId);
                const input = $('.filter-input[data-name="id"]');
                const i = input.attr('data-column');
                const v = input.val();
                table.columns(i).search(v).draw();

                editAppointment(searchId);
            }

            // Új időpontfoglalás létrehozása modal megjelenítése
            $('#addButton').on('click', async function () {
                try {
                    resetForm('Új időpontfoglalás létrehozása');
                    renderPhotos([]);  // Üres fotók kezdetben
                } catch (error) {
                    showToast(error, 'danger');
                }
                appointmentModal.show();
            });

            let zipDebounceTimeout;

            $('#zip_code').on('input', function () {
                clearTimeout(zipDebounceTimeout);

                zipDebounceTimeout = setTimeout(() => {
                    const zip = ($('#zip_code').val() || '').trim();
                    const $suggestions = $('#zip_suggestions');
                    $suggestions.empty();

                    if (!zip) {
                        $suggestions.hide();
                        return;
                    }

                    $.ajax({
                        url: window.appConfig.APP_URL + 'api/postal-codes/search?zip=' + encodeURIComponent(zip),
                        type: 'GET',
                        success: function (data) {
                            $suggestions.empty();

                            if (Array.isArray(data) && data.length > 0) {
                                data.forEach(function (row) {
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
                        },
                        error: function () {
                            $suggestions.hide();
                        }
                    });
                }, 300);
            });

            $('#zip_suggestions').on('click', 'button', function () {
                $('#zip_code').val($(this).attr('data_zip'));
                $('#city').val($(this).text().trim());
                $('#zip_suggestions').hide();
            });

            let streetDebounceTimeout;

            $('#address_line').on('input', function () {
                clearTimeout(streetDebounceTimeout);

                streetDebounceTimeout = setTimeout(() => {
                    const city = ($('#city').val() || '').trim();
                    const q = ($('#address_line').val() || '').trim();

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
                $('#address_line').val($(this).text().trim());
                $('#street_suggestions').hide();
            });

            // Időpontfoglalás mentése

            $('#saveAppointment').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('appointmentForm');

                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const appointmentId = $('#appointment_id').val();

                let url = '{{ route('admin.appointments.store') }}';  // Ha nincs id, akkor új időpont létrehozása
                let method = 'POST';  // Alapértelmezett metódus

                if (appointmentId) {
                    url = `${window.appConfig.APP_URL}admin/idopontfoglalasok/${appointmentId}`;  // update URL, ha van ID
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
                        appointmentModal.hide();
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

            function editAppointment(appointmentId) {
                resetForm('Időpontfoglalás szerkesztése');

                $.get(`{{ url('/admin/idopontfoglalasok') }}/${appointmentId}`, function(data) {
                    const assigned_photos = data.photos;

                    // Alapadatok betöltése
                    $('#appointment_id').val(data.id);
                    $('#client_id').val(data.client_id || '');
                    $('#name').val(data.name);
                    $('#email').val(data.email);
                    $('#phone').val(data.phone);
                    $('#zip_code').val(data.zip_code);
                    $('#city').val(data.city);
                    $('#address_line').val(data.address_line);
                    $('#appointment_date').val(data.appointment_date);
                    $('#appointment_type').val(data.appointment_type);
                    $('#status').val(data.status);
                    $('#message').val(data.message);

                    setClientFieldsVisible(true);
                    setSnapshotMode(!!data.client_id);
                    $('#client_search').val(data.name || '');


                    renderPhotos(assigned_photos);
                    sendViewRequest("appointment", appointmentId);

                    table.ajax.reload(null, false);

                    appointmentModal.show();
                }).fail(function(xhr, status, error) {
                    showToast('Nem sikerült betölteni az időpont adatait! ' + error, 'danger');
                });
            }

            // Időpont szerkesztése

            $('#appointmentsTable').on('click', '.edit', async function () {
                const row_data = $('#appointmentsTable').DataTable().row($(this).parents('tr')).data();
                const appointmentId = row_data.id;
                editAppointment(appointmentId);
            });

            // Időpont törlése
            $('#appointmentsTable').on('click', '.delete', async function () {
                const row_data = $('#appointmentsTable').DataTable().row($(this).parents('tr')).data();
                const appointmentId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd az időpontot?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/idopontfoglalasok') }}/${appointmentId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Időpont sikeresen törölve!', 'success');
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt az időpont törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt az időpont törlésekor', 'danger');
                }
            });

            function renderPhotos(photos) {
                const container = $('#appointmentPhotos');
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
                                    <th>Törlés</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                `);

                const tbody = table.find('tbody');

                photos.forEach(photo => {
                    const fileUrl = `${window.appConfig.APP_URL}admin/appointment-photos/${photo.path}`;
                    const row = $(`
                        <tr data-photo-id="${photo.id}">
                            <td><a href="${fileUrl}" target="_blank"><img src="${fileUrl}" class="img-thumbnail" style="width: 100px;"></a></td>

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

                // Törlés esemény kezelő

                container.off('click', '.delete-photo').on('click', '.delete-photo', function () {
                    const photoId = $(this).data('photo-id');
                    const row = $(this).closest('tr');

                    if (!confirm('Biztosan törölni szeretnéd ezt a képet?')) return;

                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/idopontfoglalasok/delete-photo`,
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


            function resetForm(title = null) {
                $('#appointmentForm')[0].reset();
                $('#appointment_id').val('');
                $('#appointmentModalLabel').text(title);

                clearClientSelection();
                $('#client_search').val('');
                $('#client_search_results').hide().empty();

                $('#zip_suggestions').empty().hide();
                $('#street_suggestions').empty().hide();

                // Visszakapcsolás az első tabra (Alapadatok)
                const firstTab = new bootstrap.Tab(document.querySelector('#appointmentTab .nav-link[data-bs-target="#basic"]'));
                firstTab.show();
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

                const $inputs = $('#name, #zip_code, #city, #address_line');
                const $alwaysEditableInputs = $('#phone, #email');

                $inputs.prop('readonly', disable);
                $alwaysEditableInputs.prop('readonly', false);

                if (disable) {
                    $inputs.addClass('bg-light');
                    $alwaysEditableInputs.removeClass('bg-light');
                } else {
                    $inputs.removeClass('bg-light');
                    $alwaysEditableInputs.removeClass('bg-light');
                }
            }

            function setClientFieldsVisible(visible) {
                if (visible) {
                    $('.appointment-client-fields').show();
                } else {
                    $('.appointment-client-fields').hide();
                }
            }

            function clearClientSelection() {
                $('#client_id').val('');
                $('#client_address_id').val('');
                $('#create_client').val('0');
                $('#use_custom_address').val('0');

                $('#name').val('');
                $('#email').val('');
                $('#phone').val('');
                $('#zip_code').val('');
                $('#city').val('');
                $('#address_line').val('');

                setClientFieldsVisible(false);
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
                const zip = $btn.data('zip') || null;
                const city = $btn.data('city') || null;
                const line = $btn.data('line') || null;

                $('#client_id').val(clientId);
                $('#client_address_id').val(addressId);
                $('#create_client').val('0');
                $('#use_custom_address').val('0');

                $('#name').val(name);
                $('#email').val(email);
                $('#phone').val(phone);
                $('#zip_code').val(zip);
                $('#city').val(city);
                $('#address_line').val(line);

                setClientFieldsVisible(true);
                setSnapshotMode(true);

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

                $('#name').val(name);
                $('#email').val(email);
                $('#phone').val(phone);
                $('#zip_code').val('');
                $('#city').val('');
                $('#address_line').val('');

                setClientFieldsVisible(true);
                setSnapshotMode(false);

                const headerParts = [idNumber, email].filter(Boolean).join(', ');
                const display = `${name || ''}${headerParts ? ' (' + headerParts + ')' : ''}`.trim();
                $('#client_search').val(display);
                $('#client_search_results').hide().empty();

                setTimeout(() => {
                    $('#zip_code').trigger('focus');
                }, 0);
            });

            $('#client_search_results').on('click', '.client-create', function () {
                $('#create_client').val('1');
                $('#client_id').val('');
                $('#client_address_id').val('');
                $('#use_custom_address').val('0');

                setClientFieldsVisible(true);
                setSnapshotMode(false);

                $('#client_search').val('');
                $('#client_search_results').hide().empty();

                setTimeout(() => {
                    $('#name').trigger('focus');
                }, 0);
            });

        });

    </script>
@endsection
