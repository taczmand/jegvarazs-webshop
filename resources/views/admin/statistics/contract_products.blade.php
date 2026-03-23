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

                <div id="chartContainer" style="height: 520px; width: 100%;"></div>
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
    <script type="module">
        const hintEl = document.getElementById('chartHint');
        const yearSelect = document.getElementById('yearSelect');

        function colorPalette(i) {
            const palette = [
                '#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f', '#edc948', '#b07aa1', '#ff9da7', '#9c755f', '#bab0ab'
            ];
            return palette[i % palette.length];
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

            const chart = new CanvasJS.Chart('chartContainer', {
                animationEnabled: true,
                theme: 'light2',
                title: {
                    text: `Szerződésen szereplő termékek mennyisége – ${payload?.year ?? ''}`,
                    fontSize: 18
                },
                axisY: {
                    title: 'Mennyiség (db)',
                    includeZero: true
                },
                axisX: {
                    interval: 1
                },
                toolTip: {
                    shared: true
                },
                legend: {
                    cursor: 'pointer'
                },
                data: series.map((s, idx) => ({
                    type: 'column',
                    name: s.name,
                    showInLegend: true,
                    color: colorPalette(idx),
                    dataPoints: Array.isArray(s.dataPoints) ? s.dataPoints : []
                }))
            });

            chart.render();
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

        reload();
    </script>
@endsection
