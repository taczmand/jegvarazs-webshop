@extends('layouts.admin')

@section('content')
    <div class="container p-0">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Munkalapok / Client ID fix (ideiglenes)</h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.worksheets.clientid-fix') }}" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label" for="limit">Limit</label>
                        <input type="number" class="form-control" id="limit" name="limit" value="{{ (int) $limit }}" min="1" max="2000">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" type="submit">Frissítés</button>
                    </div>
                </form>

                <hr>

                <label class="form-label" for="sql">Generált SQL (csak ellenőrzés után futtasd!)</label>
                <textarea id="sql" class="form-control" rows="10" readonly style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">{{ $sql }}</textarea>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Munkalap</th>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Név</th>
                            <th>Cím</th>
                            <th>Talált ügyfél</th>
                            <th>Ok</th>
                            <th>Bizalom</th>
                            <th>Művelet</th>
                            <th>SQL</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $row)
                            @php
                                $w = $row['worksheet'];
                                $c = $row['client'];
                            @endphp
                            <tr>
                                <td>{{ $w->id }}</td>
                                <td>
                                    {{ $w->installation_date }}
                                    <div class="text-muted" style="font-size: 12px;">{{ $w->created_at }}</div>
                                </td>
                                <td>{{ $w->email }}</td>
                                <td>{{ $w->phone }}</td>
                                <td>{{ $w->name }}</td>
                                <td>{{ $w->zip_code }} {{ $w->city }}<br>{{ $w->address_line }}</td>
                                <td>
                                    @if($c)
                                        #{{ $c->id }} {{ $c->name }}
                                        <div class="text-muted" style="font-size: 12px;">{{ $c->email }} {{ $c->phone }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $row['reason'] ?: '-' }}</td>
                                <td>{{ (int) ($row['confidence'] ?? 0) }}%</td>
                                <td>
                                    @if($c)
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success js-apply-clientid-fix"
                                            data-worksheet-id="{{ (int) $w->id }}"
                                            data-client-id="{{ (int) $c->id }}"
                                        >Hozzárendelés</button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row['sql'])
                                        <code style="white-space: nowrap;">{{ $row['sql'] }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('click', async function (e) {
            const btn = e.target.closest('.js-apply-clientid-fix');
            if (!btn) return;

            const worksheetId = btn.getAttribute('data-worksheet-id');
            const clientId = btn.getAttribute('data-client-id');

            if (!worksheetId || !clientId) {
                showToast('Hiányzó azonosítók (worksheet_id/client_id).', 'danger');
                return;
            }

            btn.disabled = true;

            try {
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const csrf = tokenMeta ? tokenMeta.getAttribute('content') : null;

                const res = await fetch(`{{ route('admin.worksheets.clientid-fix.apply') }}` , {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                    },
                    body: JSON.stringify({
                        worksheet_id: worksheetId,
                        client_id: clientId,
                    }),
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    showToast(data.message || 'Hiba történt a frissítés során.', 'danger');
                    btn.disabled = false;
                    return;
                }

                showToast(data.message || 'Sikeres frissítés!', 'success');
                setTimeout(() => window.location.reload(), 300);
            } catch (err) {
                showToast(err, 'danger');
                btn.disabled = false;
            }
        });
    </script>
@endsection
