@extends('layouts.admin')

@section('content')

    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Járműtörzs</h2>
            @if(auth('admin')->user()->can('create-vehicle'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új jármű</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-vehicles'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Rendszám" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Típus" class="filter-input form-control" data-column="2">
                    </div>

                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Rendszám</th>
                        <th>Típus</th>
                        <th>Állapot</th>
                        <th>Műszaki lejár</th>
                        <th>Létrehozva</th>
                        <th>Módosítva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a járművek megtekintésére.
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
                        <h5 class="modal-title" id="adminModalLabel">Jármű szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs admin-modal-tabs" id="vehicleTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" id="vehicle_basic_tab" data-bs-toggle="tab" data-bs-target="#vehicle_basic" type="button">Alapadatok</button></li>
                            <li class="nav-item" id="vehicle_events_tab_item"><button class="nav-link" id="vehicle_events_tab" data-bs-toggle="tab" data-bs-target="#vehicle_events" type="button">Események</button></li>
                        </ul>

                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="vehicle_basic">
                                <input type="hidden" id="id" name="id">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="license_plate" class="form-label">Rendszám*</label>
                                        <input type="text" class="form-control" id="license_plate" name="license_plate" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="type" class="form-label">Típus</label>
                                        <input type="text" class="form-control" id="type" name="type">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="status" name="status" value="active">
                                            <label class="form-check-label" for="status">Állapot (Aktív)</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="technical_inspection_expires_at" class="form-label">Műszaki lejárati dátum</label>
                                        <input type="date" class="form-control" id="technical_inspection_expires_at" name="technical_inspection_expires_at">
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="vehicle_events">
                                <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
                                    <div style="min-width: 180px">
                                        <label class="form-label">Típus*</label>
                                        <select class="form-select" id="event_type">
                                            <option value="oil_change">Olajcsere</option>
                                            <option value="odometer">Km óra állás</option>
                                        </select>
                                    </div>

                                    <div style="min-width: 160px">
                                        <label class="form-label">Dátum*</label>
                                        <input type="date" class="form-control" id="event_date">
                                    </div>

                                    <div style="min-width: 200px">
                                        <label class="form-label">Érték</label>
                                        <input type="text" class="form-control" id="event_value" placeholder="pl. 123456">
                                    </div>

                                    <div class="flex-grow-1" style="min-width: 220px">
                                        <label class="form-label">Megjegyzés</label>
                                        <input type="text" class="form-control" id="event_note" placeholder="">
                                    </div>

                                    @if(auth('admin')->user()->can('create-vehicle-event'))
                                        <div>
                                            <button type="button" class="btn btn-primary" id="addEventBtn">Esemény hozzáadása</button>
                                        </div>
                                    @endif
                                </div>

                                <table class="table table-bordered display responsive nowrap" id="eventsTable" style="width:100%">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Típus</th>
                                        <th>Dátum</th>
                                        <th>Érték</th>
                                        <th>Megjegyzés</th>
                                        <th>Műveletek</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-btn">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    @if(auth('admin')->user()->can('view-vehicles'))
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
                ajax: '{{ route('admin.vehicles.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'license_plate' },
                    { data: 'type' },
                    { data: 'status' },
                    { data: 'technical_inspection_expires_at', defaultContent: '' },
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

            let eventsTable = null;
            let currentVehicleId = null;

            function initEventsTable(vehicleId) {
                currentVehicleId = vehicleId;

                if (eventsTable) {
                    eventsTable.destroy();
                    $('#eventsTable tbody').empty();
                }

                eventsTable = $('#eventsTable').DataTable({
                    language: {
                        url: '/lang/datatables/hu.json'
                    },
                    processing: true,
                    serverSide: true,
                    ajax: `{{ url('/admin/jarmuvek') }}/${vehicleId}/events/data`,
                    order: [[2, 'desc']],
                    columns: [
                        { data: 'id' },
                        { data: 'type' },
                        { data: 'event_date' },
                        { data: 'value', defaultContent: '' },
                        { data: 'note', defaultContent: '' },
                        { data: 'action', orderable: false, searchable: false },
                    ]
                });
            }

            $('#addButton').on('click', function () {
                resetForm('Új jármű létrehozása');
                adminModal.show();
            });

            $('#adminTable').on('click', '.edit', function () {
                resetForm('Jármű szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();

                $('#id').val(row_data.id);
                $('#license_plate').val(row_data.license_plate);
                $('#type').val(row_data.type);
                $('#technical_inspection_expires_at').val(row_data.technical_inspection_expires_at || '');

                document.getElementById('vehicle_events_tab_item')?.classList.remove('d-none');
                document.getElementById('vehicle_events_tab_item')?.removeAttribute('aria-hidden');

                const statusCheckbox = $('#status');
                const statusLabel = $('label[for="status"]');
                if (row_data.status === 'Aktív') {
                    statusCheckbox.prop('checked', true);
                    statusLabel.text('Állapot (Aktív)');
                } else {
                    statusCheckbox.prop('checked', false);
                    statusLabel.text('Állapot (Inaktív)');
                }

                initEventsTable(row_data.id);

                adminModal.show();
            });

            $('#adminForm').on('submit', function (e) {
                e.preventDefault();

                const form = document.getElementById('adminForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const id = $('#id').val();
                let url = '{{ route('admin.vehicles.store') }}';

                if (id) {
                    url = `{{ url('/admin/jarmuvek') }}/${id}`;
                    formData.append('_method', 'PUT');
                }

                const $submitBtn = $(form).find('.save-btn');
                const originalHtml = $submitBtn.html();
                $submitBtn.html('Mentés...').prop('disabled', true);

                $.ajax({
                    url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (res) => {
                        showToast(res.message || 'Sikeres!', 'success');
                        table.ajax.reload(null, false);
                        adminModal.hide();
                    },
                    error: (xhr) => {
                        let msg = 'Hiba!';
                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');
                    },
                    complete: () => {
                        $submitBtn.html(originalHtml).prop('disabled', false);
                    }
                });
            });

            $('#adminTable').on('click', '.delete', function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const id = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt a járművet?')) return;

                $.ajax({
                    url: `{{ url('/admin/jarmuvek') }}/${id}`,
                    type: 'DELETE',
                    data: { _token: csrfToken },
                    success: (res) => {
                        showToast(res.message || 'Sikeres!', 'success');
                        table.ajax.reload(null, false);
                    },
                    error: (xhr) => {
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

            $('#addEventBtn').on('click', function () {
                if (!currentVehicleId) {
                    showToast('Előbb mentsd el a járművet!', 'danger');
                    return;
                }

                const payload = {
                    _token: csrfToken,
                    type: $('#event_type').val(),
                    event_date: $('#event_date').val(),
                    value: $('#event_value').val(),
                    note: $('#event_note').val(),
                };

                $.ajax({
                    url: `{{ url('/admin/jarmuvek') }}/${currentVehicleId}/events`,
                    type: 'POST',
                    data: payload,
                    success: (res) => {
                        showToast(res.message || 'Sikeres!', 'success');
                        if (eventsTable) {
                            eventsTable.ajax.reload(null, false);
                        }
                        $('#event_value').val('');
                        $('#event_note').val('');
                    },
                    error: (xhr) => {
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

            $('#eventsTable').on('click', '.delete-event', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (!currentVehicleId) return;

                const id = $(this).data('id');
                if (!confirm('Biztosan törölni szeretnéd ezt az eseményt?')) return;

                $.ajax({
                    url: `{{ url('/admin/jarmuvek') }}/${currentVehicleId}/events/${id}`,
                    type: 'DELETE',
                    data: { _token: csrfToken },
                    success: (res) => {
                        showToast(res.message || 'Sikeres!', 'success');
                        if (eventsTable) {
                            eventsTable.ajax.reload(null, false);
                        }
                    },
                    error: (xhr) => {
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

            function resetForm(title = null) {
                $('#adminForm')[0].reset();
                $('#adminModalLabel').text(title);
                $('#id').val('');
                currentVehicleId = null;

                const eventsTabItem = document.getElementById('vehicle_events_tab_item');
                if (eventsTabItem) {
                    eventsTabItem.classList.add('d-none');
                    eventsTabItem.setAttribute('aria-hidden', 'true');
                }

                const eventsPane = document.getElementById('vehicle_events');
                if (eventsPane) {
                    eventsPane.classList.remove('show', 'active');
                }

                const basicTabBtn = document.getElementById('vehicle_basic_tab');
                const basicPane = document.getElementById('vehicle_basic');
                if (basicTabBtn && basicPane) {
                    basicTabBtn.classList.add('active');
                    basicPane.classList.add('show', 'active');
                }

                const statusLabel = $('label[for="status"]');
                statusLabel.text('Állapot (Aktív)');

                if (eventsTable) {
                    eventsTable.destroy();
                    eventsTable = null;
                    $('#eventsTable tbody').empty();
                }

                const today = new Date().toISOString().slice(0, 10);
                $('#event_date').val(today);
            }
        });

    </script>
    @endif
@endsection
