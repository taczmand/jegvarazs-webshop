@extends('layouts.admin')

@section('content')
    <div class="container p-0">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Érdeklődő konverzió (Felmérés → Szerződés)</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">
            @if($canView ?? (auth('admin')->user() && auth('admin')->user()->can('view-lead-conversion-report')))
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-12 col-md-3">
                        <label for="fromDate" class="form-label">Kezdő dátum</label>
                        <input type="date" class="form-control" id="fromDate" value="{{ $from ?? '' }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="toDate" class="form-label">Záró dátum</label>
                        <input type="date" class="form-control" id="toDate" value="{{ $to ?? '' }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="formNameSelect" class="form-label">Form</label>
                        <select class="form-select" id="formNameSelect">
                            <option value="">Összes</option>
                            @foreach(($formNames ?? []) as $fn)
                                <option value="{{ $fn }}" @if(($selectedFormName ?? null) === $fn) selected @endif>{{ $fn }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="onlineOfflineSelect" class="form-label">Online / Offline</label>
                        <select class="form-select" id="onlineOfflineSelect">
                            <option value="">Összes</option>
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
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
        const formNameEl = document.getElementById('formNameSelect');
        const onlineOfflineEl = document.getElementById('onlineOfflineSelect')

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
            const contractProductsQty = Number(counts.contract_products_qty || 0);

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
                    contentFormatter: function (e) {
                        const dp = e.entries[0].dataPoint;

                        return `${dp.label}: <strong>${dp.y}</strong>`;
                    }
                },
                data: [
                    {
                        type: 'column',
                        name: 'Konverzió',
                        dataPoints: [
                            {
                                label: `Érdeklődések (${leads}db)`,
                                y: leads,
                                showInLegend: true,
                                legendText: 'Érdeklődések'
                            },
                            {
                                label: `Felmérés (${p1}%)`,
                                y: survey,
                                showInLegend: true,
                                legendText: 'Felmérés'
                            },
                            {
                                label: `Szerződés (${p2}%)`,
                                y: contract,
                                showInLegend: true,
                                legendText: 'Szerződés'
                            },
                            {
                                label: `Termékek (${contractProductsQty}db)`,
                                y: contractProductsQty,
                                showInLegend: true,
                                legendText: 'Termékek'
                            }
                        ]
                    }
                ]
            };
        }

        async function load() {
            const from = fromEl?.value;
            const to = toEl?.value;
            const formName = formNameEl?.value;
            const onlineOffline = onlineOfflineEl?.value;
            if (!from || !to) return;

            setHint('Betöltés...');

            const url = new URL(`{{ route('admin.stats.lead_conversion.data') }}`, window.location.origin);
            url.searchParams.set('from', from);
            url.searchParams.set('to', to);
            if (formName) {
                url.searchParams.set('form_name', formName);
            }
            if (onlineOffline) {
                url.searchParams.set('online_offline', onlineOffline);
            }

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
        if (formNameEl) formNameEl.addEventListener('change', load);
        if (onlineOfflineEl) onlineOfflineEl.addEventListener('change', load);

        load();
    </script>
@endsection
