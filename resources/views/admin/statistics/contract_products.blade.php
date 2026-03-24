@extends('layouts.admin')

@section('content')
    <div class="container p-0">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Szerződések termék darabszám</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">
            @if(auth('admin')->user()->can('view-contracts') || auth('admin')->user()->can('view-own-contracts'))
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-12 col-md-3">
                        <label for="yearSelect" class="form-label">Év</label>
                        <select class="form-select" id="yearSelect">
                            @foreach(($years ?? []) as $y)
                                <option value="{{ $y }}" @if(($currentYear ?? null) === $y) selected @endif>{{ $y }}</option>
                            @endforeach
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
        const chartEl = document.getElementById('chartContainer');
        const chartScrollWrapEl = document.getElementById('chartScrollWrap');

        let lastPayload = null;
        let chart = null;

        function colorPalette(i) {
            const palette = [
                '#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f', '#edc948', '#b07aa1', '#ff9da7', '#9c755f', '#bab0ab'
            ];
            return palette[i % palette.length];
        }

        function isMobile() {
            return window.matchMedia && window.matchMedia('(max-width: 576px)').matches;
        }

        function buildChartOptions(payload) {
            const series = Array.isArray(payload?.series) ? payload.series : [];
            const mobile = isMobile();

            return {
                animationEnabled: true,
                theme: 'light2',
                title: {
                    text: `Szerződésen szereplő termékek mennyisége – ${payload?.year ?? ''}`,
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
                    shared: true,
                },
                legend: {
                    cursor: 'pointer',
                    verticalAlign: mobile ? 'bottom' : 'top',
                    horizontalAlign: mobile ? 'center' : 'right',
                    fontSize: mobile ? 10 : 12,
                    maxWidth: mobile ? 360 : undefined,
                },
                data: series.map((s, idx) => ({
                    type: 'column',
                    name: s.name,
                    showInLegend: true,
                    color: colorPalette(idx),
                    dataPoints: Array.isArray(s.dataPoints) ? s.dataPoints : [],
                })),
            };
        }

        function ensureScrollableMinWidth(payload) {
            if (!chartEl || !chartScrollWrapEl) return;

            const seriesCount = Array.isArray(payload?.series) ? payload.series.length : 0;
            const months = 12;
            const columns = Math.max(1, seriesCount) * months;

            const basePxPerColumn = isMobile() ? 22 : 14;
            const padding = 140;
            const desired = (columns * basePxPerColumn) + padding;
            const wrapWidth = chartScrollWrapEl.clientWidth || 0;

            chartEl.style.minWidth = `${Math.max(desired, wrapWidth)}px`;
        }

        function setHint(text) {
            if (!hintEl) return;
            hintEl.textContent = text || '';
        }

        async function loadData(year) {
            setHint('Betöltés...');

            const url = new URL(`{{ route('admin.stats.contract_products.data') }}`, window.location.origin);
            url.searchParams.set('year', year);

            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const payload = await res.json().catch(() => ({}));

            if (!res.ok) {
                setHint(payload?.message || 'Hiba történt az adatok betöltésekor.');
                return null;
            }

            setHint('');
            return payload;
        }

        function renderChart(payload) {
            const series = Array.isArray(payload?.series) ? payload.series : [];
            if (!series.length) {
                setHint('Nincs adat a kiválasztott évre.');
            }

            lastPayload = payload;

            ensureScrollableMinWidth(payload);

            if (!chart) {
                chart = new CanvasJS.Chart('chartContainer', buildChartOptions(payload));
            } else {
                chart.options = buildChartOptions(payload);
            }

            chart.render();
        }

        function debounce(fn, wait) {
            let t = null;
            return (...args) => {
                window.clearTimeout(t);
                t = window.setTimeout(() => fn(...args), wait);
            };
        }

        async function reload() {
            const year = yearSelect ? yearSelect.value : null;
            if (!year) return;

            const payload = await loadData(year);
            if (!payload) return;

            renderChart(payload);
        }

        if (yearSelect) {
            yearSelect.addEventListener('change', reload);
        }

        window.addEventListener('resize', debounce(() => {
            if (!chart || !lastPayload) return;

            ensureScrollableMinWidth(lastPayload);
            chart.options = buildChartOptions(lastPayload);
            chart.render();
        }, 150));

        reload();
    </script>
@endsection
