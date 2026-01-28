@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Szenzorok / {{ $deviceId }}</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-sensor-reports'))

                <form method="GET" class="d-flex gap-2 align-items-center mb-3">
                    <label for="year" class="mb-0">Év</label>
                    <select id="year" name="year" class="form-select" style="max-width: 120px" onchange="this.form.submit()">
                        @for($y = $minYear; $y <= $maxYear; $y++)
                            <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                        @endfor
                    </select>

                    <label for="threshold" class="mb-0">Küszöb</label>
                    <input id="threshold" name="threshold" type="number" class="form-control" style="max-width: 120px" value="{{ $threshold ?? 25 }}" min="0" onchange="this.form.submit()">

                    <a class="btn btn-outline-secondary" href="{{ route('admin.stats.sensors') }}">Vissza</a>
                </form>

                @php
                    $months = [
                        1 => 'Jan', 2 => 'Feb', 3 => 'Már', 4 => 'Ápr', 5 => 'Máj', 6 => 'Jún',
                        7 => 'Júl', 8 => 'Aug', 9 => 'Sze', 10 => 'Okt', 11 => 'Nov', 12 => 'Dec'
                    ];
                @endphp

                <div class="mb-2">
                    <strong>Foglalt napok ({{ $year }}):</strong> {{ $yearTotal ?? 0 }}
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                        <tr>
                            <th style="width: 60px">Nap</th>
                            @foreach($months as $m => $label)
                                <th class="text-center">{{ $label }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @for($day = 1; $day <= 31; $day++)
                            <tr>
                                <th class="text-center">{{ $day }}</th>
                                @for($m = 1; $m <= 12; $m++)
                                    @php
                                        $valid = checkdate($m, $day, $year);
                                        $c = $valid ? ($counts[$m][$day] ?? 0) : null;

                                        $isOverThreshold = $valid && $c !== null && $c >= ($threshold ?? 25);

                                        $bucket = ($valid && $c !== null) ? (int) floor($c / 10) : 0;
                                        $bucket = min($bucket, 9);

                                        $bgClasses = [
                                            0 => '',
                                            1 => 'bg-primary bg-opacity-10',
                                            2 => 'bg-primary bg-opacity-25',
                                            3 => 'bg-primary bg-opacity-50',
                                            4 => 'bg-primary bg-opacity-75',
                                        ];

                                        $bgClass = $bgClasses[$bucket] ?? 'bg-primary';
                                        $textClass = $bucket >= 4 ? 'text-white' : '';

                                        if ($isOverThreshold) {
                                            $bgClass = 'bg-success';
                                            $textClass = 'text-white';
                                        }

                                        $ymd = $valid ? sprintf('%04d-%02d-%02d', $year, $m, $day) : null;
                                    @endphp
                                    <td class="text-center {{ $bgClass }} {{ $textClass }} sensor-calendar-cell" @if($valid) role="button" tabindex="0" data-device-id="{{ $deviceId }}" data-day="{{ $ymd }}" @endif style="min-height: 28px; line-height: 28px; @if(!$valid) background-color: #f8f9fc; @endif">
                                        @if($valid)
                                            @if($c > 0)
                                                {{ $c }}
                                            @endif
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                        </tbody>
                        <tfoot>
                        <tr>
                            <th class="text-center">Foglalt napok összesen</th>
                            @for($m = 1; $m <= 12; $m++)
                                <th class="text-center">{{ $monthTotals[$m] ?? 0 }}</th>
                            @endfor
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="modal fade" id="sensorDayModal" tabindex="-1" role="dialog" aria-labelledby="sensorDayModalTitle" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="sensorDayModalTitle">Napi rekordok</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Bezárás">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div id="sensorDayModalMeta" class="mb-2 text-muted"></div>

                                <div id="sensorDayModalTimeline" style="position: relative; height: 120px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f8f9fc; overflow: hidden;"></div>
                                <div id="sensorDayModalScale" class="d-flex justify-content-between small text-muted" style="padding: 4px 2px;"></div>
                                <div id="sensorDayModalStatus" class="mt-2 small text-muted"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Bezárás</button>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a szenzor riportok megtekintésére.
                </div>
            @endif
        </div>
    </div>


@section('scripts')
    <script>
        (function () {
            const modalEl = document.getElementById('sensorDayModal');
            const modalTitleEl = document.getElementById('sensorDayModalTitle');
            const modalMetaEl = document.getElementById('sensorDayModalMeta');
            const timelineEl = document.getElementById('sensorDayModalTimeline');
            const scaleEl = document.getElementById('sensorDayModalScale');
            const statusEl = document.getElementById('sensorDayModalStatus');

            if (!modalEl || !timelineEl) {
                return;
            }

            let lastRequestedDay = null;
            let lastRequestedEvents = null;

            function clearTimeline() {
                timelineEl.innerHTML = '';
            }

            function renderScale() {
                if (!scaleEl) {
                    return;
                }

                scaleEl.innerHTML = '';
                const marks = [
                    { p: 0, label: '0:00' },
                    { p: 0.25, label: '6:00' },
                    { p: 0.5, label: '12:00' },
                    { p: 0.75, label: '18:00' },
                    { p: 1, label: '24:00' },
                ];

                marks.forEach(m => {
                    const span = document.createElement('span');
                    span.textContent = m.label;
                    scaleEl.appendChild(span);
                });

                const height = timelineEl.clientHeight || 1;
                marks.forEach(m => {
                    const grid = document.createElement('div');
                    grid.style.position = 'absolute';
                    grid.style.left = (m.p * 100) + '%';
                    grid.style.top = '0';
                    grid.style.transform = 'translateX(-1px)';
                    grid.style.width = '2px';
                    grid.style.height = height + 'px';
                    grid.style.background = '#111827';
                    grid.style.opacity = '0.08';
                    timelineEl.appendChild(grid);
                });
            }

            function setStatus(text) {
                statusEl.textContent = text || '';
            }

            function renderTicks(day, events) {
                clearTimeline();

                renderScale();

                const width = timelineEl.clientWidth || 1;
                const height = timelineEl.clientHeight || 1;

                events.forEach(ev => {
                    if (ev.seconds === null || ev.seconds === undefined) {
                        return;
                    }

                    const x = Math.max(0, Math.min(1, ev.seconds / 86400)) * width;
                    const tick = document.createElement('div');
                    tick.style.position = 'absolute';
                    tick.style.left = Math.round(x) + 'px';
                    tick.style.top = '0';
                    tick.style.width = '2px';
                    tick.style.height = height + 'px';
                    tick.style.background = '#0d6efd';
                    tick.style.opacity = '0.8';
                    tick.title = ev.occurred_at || '';
                    timelineEl.appendChild(tick);
                });

                modalMetaEl.textContent = `Nap: ${day} | Összesen: ${events.length} db`;
            }

            if (window.jQuery) {
                window.jQuery(modalEl).on('shown.bs.modal', function () {
                    if (lastRequestedDay && Array.isArray(lastRequestedEvents)) {
                        renderTicks(lastRequestedDay, lastRequestedEvents);
                    } else {
                        renderScale();
                    }
                });

                window.jQuery(modalEl).on('click', '[data-dismiss="modal"]', function (e) {
                    e.preventDefault();
                    window.jQuery(modalEl).modal('hide');
                });
            }

            async function openDay(cell) {
                const day = cell.getAttribute('data-day');
                const deviceId = cell.getAttribute('data-device-id');

                if (!day || !deviceId) {
                    return;
                }

                modalTitleEl.textContent = `Napi rekordok (${deviceId})`;
                modalMetaEl.textContent = `Nap: ${day}`;
                clearTimeline();
                setStatus('Betöltés...');

                lastRequestedDay = day;
                lastRequestedEvents = null;

                if (window.jQuery) {
                    window.jQuery(modalEl).modal('show');
                }

                const url = `{{ route('admin.stats.sensors.device.day', ['deviceId' => '__DEVICE__']) }}`.replace('__DEVICE__', encodeURIComponent(deviceId)) + `?day=${encodeURIComponent(day)}`;

                try {
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        setStatus(data && data.message ? data.message : 'Hiba a betöltés során.');
                        return;
                    }

                    const events = Array.isArray(data.events) ? data.events : [];

                    lastRequestedEvents = events;
                    setTimeout(function () {
                        renderTicks(day, events);
                    }, 0);
                    setStatus(events.length === 0 ? 'Nincs rekord erre a napra.' : '');
                } catch (e) {
                    setStatus('Hiba a betöltés során.');
                }
            }

            document.addEventListener('click', function (e) {
                const cell = e.target.closest('.sensor-calendar-cell');
                if (!cell) {
                    return;
                }

                if (!cell.getAttribute('data-day')) {
                    return;
                }

                openDay(cell);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter') {
                    return;
                }

                const active = document.activeElement;
                if (active && active.classList && active.classList.contains('sensor-calendar-cell')) {
                    if (active.getAttribute('data-day')) {
                        openDay(active);
                    }
                }
            });
        })();
    </script>
@endsection


@endsection
