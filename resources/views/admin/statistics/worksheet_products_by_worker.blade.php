@extends('layouts.admin')

@section('content')
    <div class="container p-0">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Dolgozók termék db</h2>
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
                    <div class="col-12 col-md-3">
                        <label for="workTypeSelect" class="form-label">Munkalap típusa</label>
                        <select class="form-select" id="workTypeSelect">
                            <option value="" @if(empty($currentWorkType)) selected @endif>Összes</option>
                            <option value="Karbantartás" @if(($currentWorkType ?? null) === 'Karbantartás') selected @endif>Karbantartás</option>
                            <option value="Szerelés" @if(($currentWorkType ?? null) === 'Szerelés') selected @endif>Szerelés</option>
                            <option value="Felmérés" @if(($currentWorkType ?? null) === 'Felmérés') selected @endif>Felmérés</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-9">
                        <div class="small text-muted" id="chartHint"></div>
                    </div>
                </div>

                <div id="chartScrollWrap" style="width: 100%; overflow-x: auto; overflow-y: hidden;">
                    <div id="chartContainer" style="width: 100%;"></div>
                </div>
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
        #chartScrollWrap { -webkit-overflow-scrolling: touch; }
        @media (max-width: 576px) {
            #chartContainer { height: 380px; }
        }
    </style>
    <script type="module">
        const hintEl = document.getElementById('chartHint');
        const yearSelect = document.getElementById('yearSelect');
        const workTypeSelect = document.getElementById('workTypeSelect');
        const chartEl = document.getElementById('chartContainer');
        const chartScrollWrapEl = document.getElementById('chartScrollWrap');

        let chart = null;

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

        function setHint(text) {
            if (!hintEl) return;
            hintEl.textContent = text || '';
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

        async function loadData(year, workType) {
            setHint('Betöltés...');

            const url = new URL(`{{ route('admin.stats.worksheet_products_by_worker.data') }}`, window.location.origin);
            url.searchParams.set('year', year);
            if (workType) {
                url.searchParams.set('work_type', workType);
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

        async function renderFor(year, workType) {
            const payload = await loadData(year, workType);
            if (!payload) return;

            ensureScrollableMinWidth(payload);

            chart = new CanvasJS.Chart('chartContainer', buildChartOptions(payload));
            chart.render();
        }

        function rerender() {
            if (!yearSelect) return;
            const year = yearSelect.value;
            const workType = workTypeSelect ? workTypeSelect.value : '';
            renderFor(year, workType);
        }

        if (yearSelect) {
            yearSelect.addEventListener('change', rerender);
        }
        if (workTypeSelect) {
            workTypeSelect.addEventListener('change', rerender);
        }

        rerender();
    </script>
@endsection
