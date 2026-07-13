@extends('layouts.admin')

@section('content')
    <div class="container p-0">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Termékmennyiségek</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">
            @if(auth('admin')->user()->can('view-worksheet-products-by-worker-report'))
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-12 col-md-3">
                        <label for="yearSelect" class="form-label">Év</label>
                        <select class="form-select" id="yearSelect">
                            @foreach(($years ?? []) as $y)
                                <option value="{{ $y }}" @if(($currentYear ?? null) === $y) selected @endif>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label for="monthSelect" class="form-label">Hónap</label>
                        <select class="form-select" id="monthSelect">
                            <option value="" @if(empty($currentMonth)) selected @endif>Összes</option>
                            <option value="1" @if(($currentMonth ?? null) === 1) selected @endif>Január</option>
                            <option value="2" @if(($currentMonth ?? null) === 2) selected @endif>Február</option>
                            <option value="3" @if(($currentMonth ?? null) === 3) selected @endif>Március</option>
                            <option value="4" @if(($currentMonth ?? null) === 4) selected @endif>Április</option>
                            <option value="5" @if(($currentMonth ?? null) === 5) selected @endif>Május</option>
                            <option value="6" @if(($currentMonth ?? null) === 6) selected @endif>Június</option>
                            <option value="7" @if(($currentMonth ?? null) === 7) selected @endif>Július</option>
                            <option value="8" @if(($currentMonth ?? null) === 8) selected @endif>Augusztus</option>
                            <option value="9" @if(($currentMonth ?? null) === 9) selected @endif>Szeptember</option>
                            <option value="10" @if(($currentMonth ?? null) === 10) selected @endif>Október</option>
                            <option value="11" @if(($currentMonth ?? null) === 11) selected @endif>November</option>
                            <option value="12" @if(($currentMonth ?? null) === 12) selected @endif>December</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="workTypeSelect" class="form-label">Munkalap típusa</label>
                        <select class="form-select" id="workTypeSelect">
                            <option value="Karbantartás" @if(($currentWorkType ?? null) === 'Karbantartás') selected @endif>Karbantartás</option>
                            <option value="Szerelés" @if(($currentWorkType ?? null) === 'Szerelés') selected @endif>Szerelés</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label">Munkalap állapot</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach(($allowedStatuses ?? []) as $status)
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="work_statuses[]"
                                        id="workStatus_{{ md5($status) }}"
                                        value="{{ $status }}"
                                        @if(in_array($status, ($currentWorkStatuses ?? []), true)) checked @endif
                                    >
                                    <label class="form-check-label" for="workStatus_{{ md5($status) }}">{{ $status }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12 col-md-9">
                        <div class="small text-muted" id="chartHint"></div>
                    </div>
                </div>

                <div id="chartScrollWrap" style="width: 100%; overflow-x: auto; overflow-y: hidden;">
                    <div id="chartContainer" style="width: 100%;"></div>
                </div>

                <div class="mt-4" id="monthlyChartScrollWrap" style="width: 100%; overflow-x: auto; overflow-y: hidden;">
                    <div id="monthlyChartContainer" style="width: 100%;"></div>
                </div>

                <div class="mt-2 fw-bold" id="monthlyChartSum"></div>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a jelentés megtekintéséhez.
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <style>
        #chartContainer { height: 520px; }
        #monthlyChartContainer { height: 420px; }
        #chartScrollWrap { -webkit-overflow-scrolling: touch; }
        #monthlyChartScrollWrap { -webkit-overflow-scrolling: touch; }
        @media (max-width: 576px) {
            #chartContainer { height: 380px; }
            #monthlyChartContainer { height: 320px; }
        }
    </style>
    <script type="module">
        const hintEl = document.getElementById('chartHint');
        const yearSelect = document.getElementById('yearSelect');
        const workTypeSelect = document.getElementById('workTypeSelect');
        const monthSelect = document.getElementById('monthSelect');
        const chartEl = document.getElementById('chartContainer');
        const chartScrollWrapEl = document.getElementById('chartScrollWrap');

        const workStatusCheckboxes = Array.from(document.querySelectorAll('input[name="work_statuses[]"]'));

        const monthlyChartEl = document.getElementById('monthlyChartContainer');
        const monthlyChartScrollWrapEl = document.getElementById('monthlyChartScrollWrap');

        const monthlyChartSumEl = document.getElementById('monthlyChartSum');

        let chart = null;
        let monthlyChart = null;

        function isMobile() {
            return window.matchMedia && window.matchMedia('(max-width: 576px)').matches;
        }

        function ensureScrollableMinWidth(payload) {
            if (!chartEl || !chartScrollWrapEl) return;

            const points = Array.isArray(payload?.dataPoints) ? payload.dataPoints.length : 0;
            const basePxPerColumn = isMobile() ? 34 : 22;
            const padding = 160;
            const desired = (Math.max(1, points) * basePxPerColumn) + padding;
            const wrapWidth = chartScrollWrapEl.clientWidth || 0;

            chartEl.style.minWidth = `${Math.max(desired, wrapWidth)}px`;
        }

        function ensureScrollableMinWidthMonthly(payload) {
            if (!monthlyChartEl || !monthlyChartScrollWrapEl) return;

            const points = Array.isArray(payload?.dataPoints) ? payload.dataPoints.length : 0;
            const basePxPerColumn = isMobile() ? 40 : 34;
            const padding = 160;
            const desired = (Math.max(1, points) * basePxPerColumn) + padding;
            const wrapWidth = monthlyChartScrollWrapEl.clientWidth || 0;

            monthlyChartEl.style.minWidth = `${Math.max(desired, wrapWidth)}px`;
        }

        function setHint(text) {
            if (!hintEl) return;
            hintEl.textContent = text || '';
        }

        function setSum(el, payload) {
            if (!el) return;
            const points = Array.isArray(payload?.dataPoints) ? payload.dataPoints : [];
            const sum = points.reduce((acc, p) => acc + (parseInt(p?.y ?? 0, 10) || 0), 0);
            el.textContent = `Összesen: ${sum} db`;
        }

        function getSelectedWorkStatuses() {
            return workStatusCheckboxes
                .filter(cb => cb && cb.checked)
                .map(cb => cb.value)
                .filter(v => typeof v === 'string' && v !== '');
        }

        function buildChartOptions(payload) {
            const mobile = isMobile();
            const points = Array.isArray(payload?.dataPoints) ? payload.dataPoints : [];

            return {
                animationEnabled: true,
                theme: 'light2',
                title: {
                    text: `Munkalapokon felhasznált termékek mennyisége dolgozónként – ${payload?.year ?? ''}`,
                    fontSize: mobile ? 14 : 18,
                    margin: mobile ? 8 : 10,
                },
                axisY: {
                    title: mobile ? '' : 'Mennyiség (db)',
                    includeZero: true,
                    labelFontSize: mobile ? 10 : 12,
                    titleFontSize: mobile ? 11 : 13,
                },
                axisX: {
                    interval: 1,
                    labelFontSize: mobile ? 10 : 12,
                    labelAngle: mobile ? 0 : 0,
                },
                toolTip: {
                    shared: false,
                },
                data: [{
                    type: 'column',
                    dataPoints: points,
                }],
            };
        }

        function buildMonthlyChartOptions(payload) {
            const mobile = isMobile();
            const points = Array.isArray(payload?.dataPoints) ? payload.dataPoints : [];

            return {
                animationEnabled: true,
                theme: 'light2',
                title: {
                    text: `Havi összesített mennyiség – ${payload?.year ?? ''}`,
                    fontSize: mobile ? 14 : 18,
                    margin: mobile ? 8 : 10,
                },
                axisY: {
                    title: mobile ? '' : 'Mennyiség (db)',
                    includeZero: true,
                    labelFontSize: mobile ? 10 : 12,
                    titleFontSize: mobile ? 11 : 13,
                },
                axisX: {
                    interval: 1,
                    labelFontSize: mobile ? 10 : 12,
                    labelAngle: mobile ? 0 : 0,
                },
                toolTip: {
                    shared: false,
                },
                data: [{
                    type: 'column',
                    dataPoints: points,
                }],
            };
        }

        async function loadData(year, workType, month, workStatuses) {
            setHint('Betöltés...');

            const url = new URL(`{{ route('admin.stats.worksheet_products_by_worker.data') }}`, window.location.origin);
            url.searchParams.set('year', year);
            if (workType) {
                url.searchParams.set('work_type', workType);
            }
            if (month) {
                url.searchParams.set('month', month);
            }
            if (Array.isArray(workStatuses)) {
                workStatuses.forEach(s => url.searchParams.append('work_statuses[]', s));
            }

            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const payload = await res.json().catch(() => ({}));

            if (!res.ok) {
                setHint(payload?.message || 'Hiba történt az adatok betöltésekor.');
                return null;
            }

            setHint('');
            return payload;
        }

        async function loadMonthlyData(year, workType, month, workStatuses) {
            const url = new URL(`{{ route('admin.stats.worksheet_products_by_worker.data_by_month') }}`, window.location.origin);
            url.searchParams.set('year', year);
            if (workType) {
                url.searchParams.set('work_type', workType);
            }
            if (month) {
                url.searchParams.set('month', month);
            }
            if (Array.isArray(workStatuses)) {
                workStatuses.forEach(s => url.searchParams.append('work_statuses[]', s));
            }

            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const payload = await res.json().catch(() => ({}));

            if (!res.ok) {
                setHint(payload?.message || 'Hiba történt az adatok betöltésekor.');
                return null;
            }

            return payload;
        }

        async function renderFor(year, workType, month, workStatuses) {
            const payload = await loadData(year, workType, month, workStatuses);
            if (!payload) return;

            ensureScrollableMinWidth(payload);

            chart = new CanvasJS.Chart('chartContainer', buildChartOptions(payload));
            chart.render();

            const monthlyPayload = await loadMonthlyData(year, workType, month, workStatuses);
            if (!monthlyPayload) return;

            ensureScrollableMinWidthMonthly(monthlyPayload);

            monthlyChart = new CanvasJS.Chart('monthlyChartContainer', buildMonthlyChartOptions(monthlyPayload));
            monthlyChart.render();

            setSum(monthlyChartSumEl, monthlyPayload);
        }

        function rerender() {
            if (!yearSelect) return;
            const year = yearSelect.value;
            const workType = workTypeSelect ? workTypeSelect.value : '';
            const month = monthSelect ? monthSelect.value : '';
            const workStatuses = getSelectedWorkStatuses();
            renderFor(year, workType, month, workStatuses);
        }

        if (yearSelect) {
            yearSelect.addEventListener('change', rerender);
        }
        if (workTypeSelect) {
            workTypeSelect.addEventListener('change', rerender);
        }
        if (monthSelect) {
            monthSelect.addEventListener('change', rerender);
        }

        workStatusCheckboxes.forEach(cb => cb.addEventListener('change', rerender));

        rerender();
    </script>
@endsection
