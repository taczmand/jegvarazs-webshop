<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leltár</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .h1 { font-size: 20px; font-weight: 700; margin: 0 0 6px 0; }
        .muted { color: #6b7280; }
        .small { font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .cat { background: #f1f5f9; font-weight: 700; }
        .right { text-align: right; }
    </style>
</head>
<body>

<div>
    <div class="h1">Leltár</div>
    <div class="muted small">Megnevezés: {{ $stocktake->name }}</div>
    <div class="muted small">Raktár: {{ $warehouse?->name ?? '-' }}</div>
    <div class="muted small">Kezdés: {{ $stocktake->started_at_time ? $stocktake->started_at_time->format('Y-m-d H:i') : ($stocktake->started_at ? $stocktake->started_at->format('Y-m-d') : '-') }}</div>
    <div class="muted small">Lezárás: {{ $stocktake->closed_at_time ? $stocktake->closed_at_time->format('Y-m-d H:i') : ($stocktake->closed_at ? $stocktake->closed_at->format('Y-m-d') : '-') }}</div>
</div>

<table>
    <thead>
    <tr>
        <th style="width: 44%">Termék</th>
        <th style="width: 14%">Elvárt</th>
        <th style="width: 14%">Leltár</th>
        <th style="width: 14%">Eltérés</th>
        <th style="width: 14%">ME</th>
    </tr>
    </thead>
    <tbody>
    @php($currentCat = null)
    @foreach(($rows ?? []) as $r)
        @if($currentCat !== ($r['category_title'] ?? ''))
            @php($currentCat = ($r['category_title'] ?? ''))
            <tr>
                <td class="cat" colspan="5">{{ $currentCat !== '' ? $currentCat : 'Egyéb' }}</td>
            </tr>
        @endif
        <tr>
            <td>{{ $r['title'] ?? '' }}</td>
            <td class="right">{{ $r['expected_quantity'] ?? '' }}</td>
            <td class="right">{{ $r['counted_quantity'] ?? '' }}</td>
            <td class="right">{{ $r['difference_quantity'] ?? '' }}</td>
            <td>{{ $r['unit'] ?? '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
