<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page { margin: 18mm 16mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        .muted { color: #6b7280; }
        .h1 { font-size: 18px; font-weight: 700; margin: 0 0 6px 0; }
        .box { border: 1px solid #cbd5e1; border-radius: 4px; padding: 10px; }
        .grid { display: table; width: 100%; table-layout: fixed; }
        .col { display: table-cell; vertical-align: top; }
        .col-50 { width: 50%; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; }
        th { background: #cfe0ff; text-align: left; }
        .text-right { text-align: right; }
        .small { font-size: 10px; }
        .mb-8 { margin-bottom: 8px; }
        .mb-12 { margin-bottom: 12px; }
        .mb-16 { margin-bottom: 16px; }
        .bar { background: #cfe0ff; border: 1px solid #cbd5e1; padding: 4px 8px; font-weight: 700; font-size: 11px; }
    </style>
</head>
<body>

<div class="mb-12">
    <div class="grid">
        <div class="col col-50">
            <div class="h1">Raktárközi átvezetés</div>
            <div class="muted small">Sorszám: {{ $warehouse_transfer->document_number }}</div>
            <div class="muted small">Kelt: {{ $warehouse_transfer->transferred_at ? $warehouse_transfer->transferred_at->format('Y-m-d') : '-' }}</div>
            <div class="muted small">Készítette: {{ ($createdBy ?? '') !== '' ? $createdBy : '-' }}</div>
        </div>
    </div>
</div>

<div class="grid mb-16">
    <div class="col col-50" style="padding-right:8px;">
        <div class="box">
            <div class="bar">Forrás raktár</div>
            <div style="font-weight:700;">{{ $fromWarehouse?->name ?? '-' }}</div>
            <div class="muted small">{{ trim(($fromWarehouse?->country ?? '') . ' ' . ($fromWarehouse?->zip_code ?? '') . ' ' . ($fromWarehouse?->city ?? '') . ' ' . ($fromWarehouse?->address_line ?? '')) }}</div>
        </div>
    </div>
    <div class="col col-50" style="padding-left:8px;">
        <div class="box">
            <div class="bar">Cél raktár</div>
            <div style="font-weight:700;">{{ $toWarehouse?->name ?? '-' }}</div>
            <div class="muted small">{{ trim(($toWarehouse?->country ?? '') . ' ' . ($toWarehouse?->zip_code ?? '') . ' ' . ($toWarehouse?->city ?? '') . ' ' . ($toWarehouse?->address_line ?? '')) }}</div>
        </div>
    </div>
</div>

@if(($warehouse_transfer->note_before_items ?? '') !== '')
    <div class="mb-8">{!! nl2br(e($warehouse_transfer->note_before_items)) !!}</div>
@endif

<div class="mb-12">
    <table>
        <thead>
        <tr>
            <th style="width: 55%;">Megnevezés</th>
            <th style="width: 15%;" class="text-right">Menny.</th>
            <th style="width: 10%;">Mee.</th>
            <th style="width: 20%;">Megjegyzés</th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $row)
            <tr>
                <td>
                    <div style="font-weight:700;">{{ $row['name'] }}</div>
                    @if(($row['sku'] ?? '') !== '')
                        <div class="muted small">SKU: {{ $row['sku'] }}</div>
                    @endif
                </td>
                <td class="text-right">{{ rtrim(rtrim(number_format((float) ($row['quantity'] ?? 0), 3, '.', ''), '0'), '.') }}</td>
                <td>{{ $row['unit'] ?? 'db' }}</td>
                <td>{{ $row['note'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@if(($warehouse_transfer->note_after_items ?? '') !== '')
    <div class="mb-8">{!! nl2br(e($warehouse_transfer->note_after_items)) !!}</div>
@endif

@if(($warehouse_transfer->note ?? '') !== '')
    <div class="muted small">{!! nl2br(e($warehouse_transfer->note)) !!}</div>
@endif

</body>
</html>
