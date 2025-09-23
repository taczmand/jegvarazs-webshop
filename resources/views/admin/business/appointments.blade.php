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
                        <input type="text" placeholder="Irányítószám" class="filter-input form-control" data-column="3">
                    </div>
                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Város" class="filter-input form-control" data-column="4">
                    </div>
                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Cím" class="filter-input form-control" data-column="5">
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
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="appointmentForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="appointmentModalLabel">Időpontfoglalás szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="appointment_id" name="id">

                        <ul class="nav nav-tabs" id="appointmentTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#basic" type="button">Alapadatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#images" type="button">Képek</button></li>
                        </ul>

                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="basic">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Ügyfélnév*</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">E-mail cím</label>
                                        <input type="email" class="form-control" id="email" name="email">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Telefonszám*</label>
                                        <input type="text" class="form-control" id="phone" name="phone" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="zip_code" class="form-label">Irányítószám*</label>
                                        <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">Város*</label>
                                        <input type="text" class="form-control" id="city" name="city" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="address_line" class="form-label">Cím*</label>
                                        <input type="text" class="form-control" id="address_line" name="address_line" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="appointment_date" class="form-label">Dátum*</label>
                                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="appointment_type" class="form-label">Típus*</label>
                                        <select id="appointment_type" name="appointment_type" class="form-control">
                                            <option value="Karbantartás">Karbantartás</option>
                                            <option value="Felmérés">Felmérés</option>
                                            <option value="Egyéb">Egyéb</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Állapot*</label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="Függőben">Függőben</option>
                                            <option value="Folyamatban">Folyamatban</option>
                                            <option value="Kész">Kész</option>
                                            <option value="Törölve">Törölve</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="message" class="form-label">Üzenet</label>
                                        <textarea name="message" id="message" rows="3" class="form-control"></textarea>
                                    </div>
                                </div>
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
                $('#appontment_id').val('');
                $('#appointmentModalLabel').text(title);

                // Visszakapcsolás az első tabra (Alapadatok)
                const firstTab = new bootstrap.Tab(document.querySelector('#appointmentTab .nav-link[data-bs-target="#basic"]'));
                firstTab.show();
            }
        });

    </script>
@endsection
