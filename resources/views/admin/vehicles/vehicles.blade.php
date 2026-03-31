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
                        <th>Figyelmeztetés</th>
                        <th>Műszaki lejár</th>
                        <th data-priority="3">Műszaki hátra (nap)</th>
                        <th>Megjegyzés</th>
                        <th>Aktuális km állás</th>
                        <th data-priority="4">Olajcsere hátra (km)</th>
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
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminModalLabel">Jármű szerkesztése</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-lg-5">
                            <div class="card">
                                <div class="card-header">
                                    Jármű adatok
                                </div>
                                <div class="card-body">
                                    <form id="adminForm">
                                        <input type="hidden" id="id" name="id">

                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label for="license_plate" class="form-label">Rendszám*</label>
                                                <input type="text" class="form-control" id="license_plate" name="license_plate" required>
                                            </div>

                                            <div class="col-12 mb-3">
                                                <label for="type" class="form-label">Típus</label>
                                                <input type="text" class="form-control" id="type" name="type">
                                            </div>

                                            <div class="col-12 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="status" name="status" value="active">
                                                    <label class="form-check-label" for="status">Állapot (Aktív)</label>
                                                </div>
                                            </div>

                                            <div class="col-12 mb-3">
                                                <label for="technical_inspection_expires_at" class="form-label">Műszaki lejárati dátum</label>
                                                <input type="date" class="form-control" id="technical_inspection_expires_at" name="technical_inspection_expires_at">
                                            </div>

                                            <div class="col-12 mb-3">
                                                <label for="oil_change_interval" class="form-label">Olajcsere periódus (km)</label>
                                                <input type="number" class="form-control" id="oil_change_interval" name="oil_change_interval" min="1" step="1">
                                            </div>

                                            <div class="col-12">
                                                <label for="note" class="form-label">Megjegyzés</label>
                                                <textarea class="form-control" id="note" name="note" rows="4"></textarea>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-7">
                            <div class="card">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <span>Timeline</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="timelineRefreshBtn">Frissítés</button>
                                </div>
                                <div class="card-body">
                                    <div class="row g-2 mb-3">
                                        <div class="col-12 col-md-6">
                                            <div class="border rounded p-2">
                                                <div class="fw-semibold mb-2">Km rögzítés</div>
                                                <div class="mb-2">
                                                    <label for="timeline_odometer_date" class="form-label mb-1">Dátum*</label>
                                                    <input type="date" class="form-control" id="timeline_odometer_date">
                                                </div>
                                                <div class="mb-2">
                                                    <label for="timeline_odometer_value" class="form-label mb-1">Km óra állás*</label>
                                                    <input type="number" class="form-control" id="timeline_odometer_value" min="0" step="1" placeholder="pl. 123456">
                                                </div>
                                                <div class="mb-2">
                                                    <label for="timeline_odometer_note" class="form-label mb-1">Megjegyzés</label>
                                                    <input type="text" class="form-control" id="timeline_odometer_note" placeholder="">
                                                </div>
                                                @if(auth('admin')->user()->can('create-vehicle-event'))
                                                    <button type="button" class="btn btn-primary w-100" id="timelineAddOdometerBtn">Km rögzítése</button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="border rounded p-2">
                                                <div class="fw-semibold mb-2">Olajcsere</div>
                                                <div class="mb-2">
                                                    <label for="timeline_oil_change_date" class="form-label mb-1">Dátum*</label>
                                                    <input type="date" class="form-control" id="timeline_oil_change_date">
                                                </div>
                                                <div class="mb-2">
                                                    <label for="timeline_oil_change_note" class="form-label mb-1">Megjegyzés</label>
                                                    <input type="text" class="form-control" id="timeline_oil_change_note" placeholder="">
                                                </div>
                                                @if(auth('admin')->user()->can('create-vehicle-event'))
                                                    <button type="button" class="btn btn-outline-primary w-100" id="timelineAddOilChangeBtn">Olajcsere rögzítése</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div id="vehicleTimeline" class="vehicle-timeline"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" form="adminForm" class="btn btn-primary save-btn">Alapadatok mentése</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if(auth('admin')->user()->can('view-vehicles'))
        <style>
            .vehicle-timeline {
                position: relative;
                padding: 6px 0;
            }
            .vehicle-timeline::before {
                content: '';
                position: absolute;
                left: 50%;
                top: 0;
                bottom: 0;
                width: 2px;
                transform: translateX(-1px);
                background: repeating-linear-gradient(
                    to bottom,
                    rgba(0,0,0,0.18),
                    rgba(0,0,0,0.18) 6px,
                    rgba(0,0,0,0) 6px,
                    rgba(0,0,0,0) 12px
                );
            }
            .vehicle-timeline-item {
                position: relative;
                display: flex;
                gap: 12px;
                align-items: flex-start;
                padding: 10px 0;
            }
            .vehicle-timeline-col {
                width: 50%;
            }
            .vehicle-timeline-col.left { padding-right: 22px; }
            .vehicle-timeline-col.right { padding-left: 22px; }

            .vehicle-timeline-center {
                position: absolute;
                left: 50%;
                top: 18px;
                transform: translateX(-50%);
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 6px;
                pointer-events: none;
            }
            .vehicle-timeline-dot {
                width: 10px;
                height: 10px;
                border-radius: 999px;
                background: #6c757d;
                border: 2px solid #fff;
                box-shadow: 0 0 0 2px rgba(0,0,0,0.10);
            }
            .vehicle-timeline-dot.kind-present { background: #0d6efd; }
            .vehicle-timeline-dot.kind-future { background: #198754; }
            .vehicle-timeline-dot.kind-event { background: #6c757d; }

            .vehicle-timeline-date {
                font-size: 0.75rem;
                color: rgba(0,0,0,0.75);
                background: #fff;
                border: 1px solid rgba(0,0,0,0.10);
                border-radius: 999px;
                padding: 2px 8px;
                line-height: 1.2;
                white-space: nowrap;
                box-shadow: 0 1px 0 rgba(0,0,0,0.04);
            }

            .vehicle-timeline-card {
                border: 1px solid rgba(0,0,0,0.08);
                border-radius: 12px;
                padding: 8px 10px;
                background: #fff;
            }
            .vehicle-timeline-badge {
                display: inline-block;
                font-size: 0.75rem;
                padding: 2px 8px;
                border-radius: 999px;
                background: rgba(13,110,253,0.10);
                color: #0d6efd;
                border: 1px solid rgba(13,110,253,0.20);
            }
            .vehicle-timeline-badge.badge-gray {
                background: rgba(108,117,125,0.10);
                color: #6c757d;
                border-color: rgba(108,117,125,0.18);
            }
            .vehicle-timeline-badge.badge-green {
                background: rgba(25,135,84,0.10);
                color: #198754;
                border-color: rgba(25,135,84,0.20);
            }
            .vehicle-timeline-badge.badge-red {
                background: rgba(220,53,69,0.10);
                color: #dc3545;
                border-color: rgba(220,53,69,0.20);
            }

            .vehicle-timeline-meta {
                display: flex;
                gap: 6px;
                flex-wrap: wrap;
                margin-top: 6px;
            }

            @media (max-width: 767.98px) {
                .vehicle-timeline::before { left: 12px; transform: none; }
                .vehicle-timeline-col { width: 100%; }
                .vehicle-timeline-col.left,
                .vehicle-timeline-col.right { padding: 0 0 0 22px; }
                .vehicle-timeline-center { left: 12px; transform: none; }
            }
        </style>
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
                    { data: 'attention', orderable: false, searchable: false, defaultContent: '' },
                    { data: 'technical_inspection_expires_at', defaultContent: '' },
                    { data: 'technical_inspection_remaining_days', defaultContent: '' },
                    { data: 'note', defaultContent: '' },
                    { data: 'current_odometer', defaultContent: '' },
                    { data: 'oil_change_remaining_km', defaultContent: '' },
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

            let currentVehicleId = null;

            function escapeHtml(str) {
                return String(str ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function setTimelineLoading(isLoading) {
                const el = document.getElementById('vehicleTimeline');
                if (!el) return;
                if (isLoading) {
                    el.innerHTML = '<div class="text-muted">Betöltés...</div>';
                }
            }

            function renderTimeline(items) {
                const el = document.getElementById('vehicleTimeline');
                if (!el) return;

                if (!items || items.length === 0) {
                    el.innerHTML = '<div class="text-muted">Nincs timeline adat.</div>';
                    return;
                }

                const html = items.map((it) => {
                    const kind = it.kind || 'event';
                    const title = escapeHtml(it.title || '');
                    const dateBadge = it.date ? `<div class="vehicle-timeline-date">${escapeHtml(it.date)}</div>` : '';

                    const eventSide = (kind === 'event' && it.type === 'oil_change')
                        ? 'right'
                        : ((kind === 'event' && it.type === 'odometer')
                            ? 'left'
                            : ((kind === 'future' && it.type === 'technical_inspection') ? 'left' : 'center'));

                    let cardTitleBadge = '';
                    let metaBadges = [];

                    if (kind === 'present') {
                        const km = it.meta?.current_odometer ?? '';
                        const rem = it.meta?.oil_change_remaining_km ?? '';
                        const nextAt = it.meta?.next_oil_change_at_odometer ?? '';

                        cardTitleBadge = `<span class="vehicle-timeline-badge">${title}</span>`;

                        if (String(km) !== '') metaBadges.push(`<span class="vehicle-timeline-badge badge-gray">Aktuális km: <strong>${escapeHtml(km)}</strong></span>`);
                        if (String(rem) !== '') metaBadges.push(`<span class="vehicle-timeline-badge badge-gray">Olajcsere hátra: <strong>${escapeHtml(rem)}</strong> km</span>`);
                        if (String(nextAt) !== '') metaBadges.push(`<span class="vehicle-timeline-badge badge-gray">Köv. olajcsere: <strong>${escapeHtml(nextAt)}</strong> km</span>`);
                    }

                    if (kind === 'future') {
                        if (it.type === 'technical_inspection') {
                            const status = it.meta?.status;
                            const cls = status === 'expired' ? 'badge-red' : (status === 'today' ? 'badge-gray' : 'badge-green');
                            cardTitleBadge = `<span class="vehicle-timeline-badge ${cls}">${title}</span>`;
                        } else {
                            cardTitleBadge = `<span class="vehicle-timeline-badge badge-green">${title}</span>`;
                        }
                    }

                    if (kind === 'event') {
                        const value = it.meta?.value;
                        const note = it.meta?.note;

                        cardTitleBadge = `<span class="vehicle-timeline-badge badge-gray">${title}</span>`;

                        if (it.type === 'odometer') {
                            if (value !== undefined && value !== null && String(value) !== '') {
                                metaBadges.push(`<span class="vehicle-timeline-badge badge-gray">Km: <strong>${escapeHtml(value)}</strong></span>`);
                            }
                            if (note !== undefined && note !== null && String(note) !== '') {
                                metaBadges.push(`<span class="vehicle-timeline-badge badge-gray">${escapeHtml(note)}</span>`);
                            }
                        }

                        if (it.type === 'oil_change') {
                            metaBadges.push(`<span class="vehicle-timeline-badge badge-gray">Olajcsere</span>`);
                            if (note !== undefined && note !== null && String(note) !== '') {
                                metaBadges.push(`<span class="vehicle-timeline-badge badge-gray">${escapeHtml(note)}</span>`);
                            }
                        }
                    }

                    const metaHtml = metaBadges.length > 0
                        ? `<div class="vehicle-timeline-meta">${metaBadges.join('')}</div>`
                        : '';

                    const cardHtml = `
                        <div class="vehicle-timeline-card">
                            <div>${cardTitleBadge}</div>
                            ${metaHtml}
                        </div>
                    `;

                    const leftCol = (eventSide === 'left') ? cardHtml : '';
                    const rightCol = (eventSide === 'right') ? cardHtml : '';

                    const centerCol = (eventSide === 'center')
                        ? `<div class="vehicle-timeline-col left"></div><div class="vehicle-timeline-col right">${cardHtml}</div>`
                        : `<div class="vehicle-timeline-col left">${leftCol}</div><div class="vehicle-timeline-col right">${rightCol}</div>`;

                    return `
                        <div class="vehicle-timeline-item">
                            ${centerCol}
                            <div class="vehicle-timeline-center">
                                <div class="vehicle-timeline-dot kind-${escapeHtml(kind)}"></div>
                                ${dateBadge}
                            </div>
                        </div>
                    `;
                }).join('');

                el.innerHTML = html;
            }

            async function loadTimeline(vehicleId) {
                if (!vehicleId) return;
                setTimelineLoading(true);

                try {
                    const res = await fetch(`{{ url('/admin/jarmuvek') }}/${vehicleId}/timeline`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (!res.ok) {
                        const body = await res.json().catch(() => ({}));
                        throw new Error(body?.message || 'Hiba a timeline betöltésekor.');
                    }

                    const data = await res.json();
                    renderTimeline(data.items || []);
                } catch (e) {
                    const el = document.getElementById('vehicleTimeline');
                    if (el) {
                        el.innerHTML = `<div class="text-danger">${escapeHtml(e?.message || 'Hiba!')}</div>`;
                    }
                }
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
                $('#note').val(row_data.note || '');
                $('#oil_change_interval').val(row_data.oil_change_interval || 12000);

                currentVehicleId = row_data.id;

                const statusCheckbox = $('#status');
                const statusLabel = $('label[for="status"]');
                if (row_data.status === 'Aktív') {
                    statusCheckbox.prop('checked', true);
                    statusLabel.text('Állapot (Aktív)');
                } else {
                    statusCheckbox.prop('checked', false);
                    statusLabel.text('Állapot (Inaktív)');
                }

                adminModal.show();
                loadTimeline(currentVehicleId);
            });

            $('#timelineRefreshBtn').on('click', function () {
                if (!currentVehicleId) {
                    showToast('Előbb mentsd el a járművet!', 'danger');
                    return;
                }

                loadTimeline(currentVehicleId);
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

            $('#timelineAddOdometerBtn').on('click', function () {
                if (!currentVehicleId) {
                    showToast('Előbb mentsd el a járművet!', 'danger');
                    return;
                }

                const payload = {
                    _token: csrfToken,
                    type: 'odometer',
                    event_date: $('#timeline_odometer_date').val(),
                    value: $('#timeline_odometer_value').val(),
                    note: $('#timeline_odometer_note').val(),
                };

                $.ajax({
                    url: `{{ url('/admin/jarmuvek') }}/${currentVehicleId}/events`,
                    type: 'POST',
                    data: payload,
                    success: (res) => {
                        showToast(res.message || 'Sikeres!', 'success');
                        table.ajax.reload(null, false);
                        loadTimeline(currentVehicleId);
                        $('#timeline_odometer_value').val('');
                        $('#timeline_odometer_note').val('');
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

            $('#timelineAddOilChangeBtn').on('click', function () {
                if (!currentVehicleId) {
                    showToast('Előbb mentsd el a járművet!', 'danger');
                    return;
                }

                const payload = {
                    _token: csrfToken,
                    type: 'oil_change',
                    event_date: $('#timeline_oil_change_date').val(),
                    note: $('#timeline_oil_change_note').val(),
                };

                $.ajax({
                    url: `{{ url('/admin/jarmuvek') }}/${currentVehicleId}/events`,
                    type: 'POST',
                    data: payload,
                    success: (res) => {
                        showToast(res.message || 'Sikeres!', 'success');
                        table.ajax.reload(null, false);
                        loadTimeline(currentVehicleId);
                        $('#timeline_oil_change_note').val('');
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

                const statusLabel = $('label[for="status"]');
                statusLabel.text('Állapot (Aktív)');

                const today = new Date().toISOString().slice(0, 10);
                $('#timeline_odometer_date').val(today);
                $('#timeline_oil_change_date').val(today);

                const timelineEl = document.getElementById('vehicleTimeline');
                if (timelineEl) {
                    timelineEl.innerHTML = '<div class="text-muted">Előbb válassz ki egy járművet.</div>';
                }
            }
        });

    </script>
    @endif
@endsection
