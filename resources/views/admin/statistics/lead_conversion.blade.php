@extends('layouts.admin')

@section('content')
    <div class="container p-0">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Érdeklődő konverzió (Felmérés → Szerződés)</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">
            @if(auth('admin')->user()->can('view-leads') && (auth('admin')->user()->can('view-contracts') || auth('admin')->user()->can('view-own-contracts')))
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-12 col-md-3">
                        <label for="fromDate" class="form-label">Kezdő dátum</label>
                        <input type="date" class="form-control" id="fromDate" value="{{ $from ?? '' }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="toDate" class="form-label">Záró dátum</label>
                        <input type="date" class="form-control" id="toDate" value="{{ $to ?? '' }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="small text-muted" id="chartHint"></div>
                    </div>
                </div>

                <div id="chartContainer" style="height: 420px; width: 100%;"></div>
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
        const fromEl = document.getElementById('fromDate');
        const toEl = document.getElementById('toDate');

        let chart = null;

        function setHint(text) {
            if (!hintEl) return;
            hintEl.textContent = text || '';
        }

        function buildOptions(payload) {
            const counts = payload?.counts || {};
            const leads = Number(counts.leads || 0);
            const survey = Number(counts.survey || 0);
            const contract = Number(counts.contract || 0);

            const p1 = leads > 0 ? Math.round((survey / leads) * 1000) / 10 : 0;
            const p2 = leads > 0 ? Math.round((contract / leads) * 1000) / 10 : 0;

            return {
                animationEnabled: true,
                theme: 'light2',
                title: {
                    text: `Érdeklődő konverzió (${payload?.from ?? ''} – ${payload?.to ?? ''})`,
                },
                axisY: {
                    includeZero: true,
                    title: 'Darabszám',
                },
                toolTip: {
                    shared: true,
                },
                data: [
                    {
                        type: 'column',
                        dataPoints: [
                            { label: `Érdeklődők`, y: leads },
                            { label: `Felmérés (${p1}%)`, y: survey },
                            { label: `Szerződés (${p2}%)`, y: contract },
                        ],
                    }
                ]
            };
        }

        async function load() {
            const from = fromEl?.value;
            const to = toEl?.value;
            if (!from || !to) return;

            setHint('Betöltés...');

            const url = new URL(`{{ route('admin.stats.lead_conversion.data') }}`, window.location.origin);
            url.searchParams.set('from', from);
            url.searchParams.set('to', to);

            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const payload = await res.json().catch(() => ({}));

            if (!res.ok) {
                setHint(payload?.message || 'Hiba történt az adatok betöltésekor.');
                return;
            }

            setHint('');

            if (!chart) {
                chart = new CanvasJS.Chart('chartContainer', buildOptions(payload));
            } else {
                chart.options = buildOptions(payload);
            }

            chart.render();
        }

        if (fromEl) fromEl.addEventListener('change', load);
        if (toEl) toEl.addEventListener('change', load);

        load();
    </script>
@endsection
