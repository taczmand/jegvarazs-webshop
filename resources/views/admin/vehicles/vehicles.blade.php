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

                            <div class="card mt-3">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <span>Havi km összesítő</span>
                                    <select class="form-select form-select-sm" id="vehicleKmYearSelect" style="width: 110px;"></select>
                                </div>
                                <div class="card-body">
                                    <div class="small text-muted" id="vehicleKmChartHint"></div>
                                    <div id="vehicleKmChartScrollWrap" style="width: 100%; overflow-x: auto; overflow-y: hidden;">
                                        <div id="vehicleKmChartContainer" style="width: 100%;"></div>
                                    </div>
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
        <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
        <style>
            .vehicle-timeline {
                position: relative;
                padding: 6px 0;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
            }

            .vehicle-timeline-inner {
                min-width: 640px;
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
                min-width: 0;
            }
            .vehicle-timeline-badge {
                display: inline-flex;
                align-items: center;
                font-size: 0.75rem;
                padding: 2px 8px;
                border-radius: 999px;
                background: rgba(13,110,253,0.10);
                color: #0d6efd;
                border: 1px solid rgba(13,110,253,0.20);
                max-width: 100%;
                white-space: normal;
                overflow-wrap: anywhere;
                word-break: break-word;
                line-height: 1.2;
            }

            .vehicle-timeline-badge.badge-nowrap {
                white-space: nowrap;
                overflow-wrap: normal;
                word-break: normal;
                overflow: hidden;
                text-overflow: ellipsis;
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
                min-width: 0;
            }

            .vehicle-timeline-meta .vehicle-timeline-badge {
                flex: 0 1 auto;
                min-width: 0;
            }

            #vehicleKmChartContainer { height: 260px; }
            #vehicleKmChartScrollWrap { -webkit-overflow-scrolling: touch; }
            @media (max-width: 576px) {
                #vehicleKmChartContainer { height: 220px; }
            }

            @media (max-width: 767.98px) {
                .vehicle-timeline::before { left: 50%; transform: translateX(-1px); }
                .vehicle-timeline-col { width: 50%; }
                .vehicle-timeline-col.left { padding-right: 14px; }
                .vehicle-timeline-col.right { padding-left: 14px; }
                .vehicle-timeline-center { left: 50%; transform: translateX(-50%); }
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
            let vehicleKmChart = null;
            let lastVehicleKmPayload = null;

            function escapeHtml(str) {
                return String(str ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function isMobile() {
                return window.matchMedia && window.matchMedia('(max-width: 576px)').matches;
            }

            function setVehicleKmHint(text) {
                const el = document.getElementById('vehicleKmChartHint');
                if (!el) return;
                el.textContent = text || '';
            }

            function ensureVehicleKmScrollableMinWidth(payload) {
                const chartEl = document.getElementById('vehicleKmChartContainer');
                const wrapEl = document.getElementById('vehicleKmChartScrollWrap');
                if (!chartEl || !wrapEl) return;

                const months = 12;
                const basePxPerColumn = isMobile() ? 28 : 18;
                const padding = 120;
                const desired = (months * basePxPerColumn) + padding;
                const wrapWidth = wrapEl.clientWidth || 0;

                chartEl.style.minWidth = `${Math.max(desired, wrapWidth)}px`;
            }

            function buildVehicleKmChartOptions(payload) {
                const mobile = isMobile();
                const year = payload?.year ?? '';

                return {
                    animationEnabled: true,
                    theme: 'light2',
                    title: {
                        text: `Havi km összesítő – ${year}`,
                        fontSize: mobile ? 14 : 18,
                        margin: mobile ? 8 : 10,
                    },
                    axisY: {
                        title: mobile ? '' : 'Km',
                        includeZero: true,
                        labelFontSize: mobile ? 10 : 12,
                        titleFontSize: mobile ? 11 : 13,
                    },
                    axisX: {
                        interval: 1,
                        labelFontSize: mobile ? 10 : 12,
                    },
                    toolTip: {
                        shared: false,
                    },
                    data: [
                        {
                            type: 'column',
                            color: '#4e79a7',
                            dataPoints: Array.isArray(payload?.dataPoints) ? payload.dataPoints : [],
                        }
                    ]
                };
            }

            function fillVehicleKmYearSelect(availableYears, selectedYear) {
                const yearSelect = document.getElementById('vehicleKmYearSelect');
                if (!yearSelect) return;

                const years = Array.isArray(availableYears) ? availableYears : [];
                const currentYear = Number(new Date().getFullYear());

                const finalYears = years.length ? years : [currentYear];

                yearSelect.innerHTML = finalYears
                    .map((y) => {
                        const yy = Number(y);
                        const sel = (yy === Number(selectedYear)) ? 'selected' : '';
                        return `<option value="${yy}" ${sel}>${yy}</option>`;
                    })
                    .join('');
            }

            async function loadVehicleKmSummary(vehicleId, year) {
                if (!vehicleId) return null;

                setVehicleKmHint('Betöltés...');

                const url = new URL(`{{ url('/admin/jarmuvek') }}/${vehicleId}/km-summary`, window.location.origin);
                if (year) url.searchParams.set('year', year);

                const res = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                const payload = await res.json().catch(() => ({}));
                if (!res.ok) {
                    setVehicleKmHint(payload?.message || 'Hiba történt a havi km adatok betöltésekor.');
                    return null;
                }

                setVehicleKmHint('');
                return payload;
            }

            function renderVehicleKmChart(payload) {
                if (!payload) return;
                if (typeof CanvasJS === 'undefined') {
                    setVehicleKmHint('A grafikon könyvtár nem elérhető.');
                    return;
                }

                lastVehicleKmPayload = payload;
                ensureVehicleKmScrollableMinWidth(payload);

                const hasAny = Array.isArray(payload?.dataPoints) && payload.dataPoints.some(p => Number(p?.y || 0) > 0);
                if (!hasAny) {
                    setVehicleKmHint('Nincs adat a kiválasztott évre.');
                }

                if (!vehicleKmChart) {
                    vehicleKmChart = new CanvasJS.Chart('vehicleKmChartContainer', buildVehicleKmChartOptions(payload));
                } else {
                    vehicleKmChart.options = buildVehicleKmChartOptions(payload);
                }

                vehicleKmChart.render();
            }

            async function reloadVehicleKmChart(year = null) {
                if (!currentVehicleId) {
                    setVehicleKmHint('Előbb mentsd el a járművet.');
                    return;
                }

                const yearSelect = document.getElementById('vehicleKmYearSelect');
                const selectedYear = year || (yearSelect ? yearSelect.value : null);
                const payload = await loadVehicleKmSummary(currentVehicleId, selectedYear);
                if (!payload) return;

                fillVehicleKmYearSelect(payload.availableYears, payload.year);
                renderVehicleKmChart(payload);
            }

            const vehicleKmYearSelectEl = document.getElementById('vehicleKmYearSelect');
            if (vehicleKmYearSelectEl) {
                vehicleKmYearSelectEl.addEventListener('change', () => reloadVehicleKmChart());
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

                        if (String(km) !== '') metaBadges.push(`<span class="vehicle-timeline-badge badge-gray badge-nowrap">Aktuális km: <strong>${escapeHtml(km)}</strong></span>`);
                        if (String(rem) !== '') metaBadges.push(`<span class="vehicle-timeline-badge badge-gray badge-nowrap">Olajcsere hátra: <strong>${escapeHtml(rem)}</strong> km</span>`);
                        if (String(nextAt) !== '') metaBadges.push(`<span class="vehicle-timeline-badge badge-gray badge-nowrap">Köv. olajcsere: <strong>${escapeHtml(nextAt)}</strong> km</span>`);
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
                                metaBadges.push(`<span class="vehicle-timeline-badge badge-gray badge-nowrap">Km: <strong>${escapeHtml(value)}</strong></span>`);
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

                el.innerHTML = `<div class="vehicle-timeline-inner">${html}</div>`;
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
                reloadVehicleKmChart();
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
                        reloadVehicleKmChart();
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

                const yearSelect = document.getElementById('vehicleKmYearSelect');
                if (yearSelect) {
                    yearSelect.innerHTML = '';
                }
                setVehicleKmHint('Előbb mentsd el a járművet.');

                const chartEl = document.getElementById('vehicleKmChartContainer');
                if (chartEl) {
                    chartEl.innerHTML = '';
                    chartEl.style.minWidth = '';
                }

                vehicleKmChart = null;
                lastVehicleKmPayload = null;
            }

            function debounce(fn, wait) {
                let t = null;
                return (...args) => {
                    window.clearTimeout(t);
                    t = window.setTimeout(() => fn(...args), wait);
                };
            }

            window.addEventListener('resize', debounce(() => {
                if (!vehicleKmChart || !lastVehicleKmPayload) return;
                ensureVehicleKmScrollableMinWidth(lastVehicleKmPayload);
                vehicleKmChart.options = buildVehicleKmChartOptions(lastVehicleKmPayload);
                vehicleKmChart.render();
            }, 150));
        });

    </script>
    @endif
@endsection
