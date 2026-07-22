<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page { margin: 18mm 16mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        .row { width: 100%; }
        .muted { color: #6b7280; }
        .h1 { font-size: 18px; font-weight: 700; margin: 0 0 6px 0; }
        .h2 { font-size: 12px; font-weight: 700; margin: 0 0 6px 0; }
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
            <div class="h1">Szállítólevél</div>
            <div class="muted small">Bizonylatszám: {{ $delivery_note->document_number }}</div>
            <div class="muted small">Kelt: {{ $delivery_note->issued_at ? $delivery_note->issued_at->format('Y-m-d') : '-' }}</div>
            <div class="muted small">Átadás / kiszállítás: {{ $delivery_note->delivered_at ? $delivery_note->delivered_at->format('Y-m-d') : '-' }}</div>
        </div>
        <div class="col col-50" style="text-align:right;">
            <div style="font-weight:700;">{{ $delivery_note->company_name ?? '-' }}</div>
            <div class="muted small">
                {{ trim(($delivery_note->company_country ?? '') . ' ' . ($delivery_note->company_zip_code ?? '') . ' ' . ($delivery_note->company_city ?? '') . ' ' . ($delivery_note->company_address_line ?? '')) }}
            </div>
            <div class="muted small">Adószám: {{ $delivery_note->company_tax_number ?? '-' }}</div>
            <div class="muted small">{{ $delivery_note->company_email ?? '' }}{{ (($delivery_note->company_email ?? '') && ($delivery_note->company_phone ?? '')) ? ' | ' : '' }}{{ $delivery_note->company_phone ?? '' }}</div>
        </div>
    </div>
</div>

<div class="grid mb-16">
    <div class="col col-50" style="padding-right:8px;">
        <div class="box">
            <div class="bar">Partner adatai</div>
            <div style="font-weight:700;">{{ $delivery_note->partner_name }}</div>
            <div class="muted small">{{ trim(($delivery_note->partner_country ?? '') . ' ' . ($delivery_note->partner_zip_code ?? '') . ' ' . ($delivery_note->partner_city ?? '') . ' ' . ($delivery_note->partner_address_line ?? '')) }}</div>
            <div class="muted small">Adószám: {{ $delivery_note->partner_tax_number ?? '-' }}</div>
        </div>
    </div>
    <div class="col col-50" style="padding-left:8px;">
        <div class="box">
            <div class="bar">Telephely</div>
            <div style="font-weight:700;">{{ $company_site?->name ?? '-' }}</div>
            <div class="muted small">{{ trim(($company_site?->country ?? '') . ' ' . ($company_site?->zip_code ?? '') . ' ' . ($company_site?->city ?? '') . ' ' . ($company_site?->address_line ?? '')) }}</div>
            <div class="muted small">{{ $company_site?->email ?? '' }}{{ ($company_site?->email && $company_site?->phone) ? ' | ' : '' }}{{ $company_site?->phone ?? '' }}</div>
        </div>
    </div>
</div>

@if(($delivery_note->note_before_items ?? '') !== '')
    <div class="mb-8">{!! nl2br(e($delivery_note->note_before_items)) !!}</div>
@endif

<div class="mb-12">
    <table>
        <thead>
        <tr>
            <th style="width: 55%;">Megnevezés</th>
            <th style="width: 15%;" class="text-right">Mennyiség</th>
            <th style="width: 10%;">Mee.</th>
            <th style="width: 20%;">SKU</th>
        </tr>
        </thead>
        <tbody>
        @foreach(($items ?? []) as $it)
            <tr>
                <td>
                    <div style="font-weight:600;">{{ $it['name'] ?? '' }}</div>
                    @if(!empty($it['note']))
                        <div class="muted small">{{ $it['note'] }}</div>
                    @endif
                </td>
                <td class="text-right">{{ rtrim(rtrim(number_format((float) ($it['quantity'] ?? 0), 3, '.', ''), '0'), '.') }}</td>
                <td>{{ $it['unit'] ?? 'db' }}</td>
                <td>{{ $it['sku'] ?? '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@if(($delivery_note->note_after_items ?? '') !== '')
    <div class="mb-12">{!! nl2br(e($delivery_note->note_after_items)) !!}</div>
@endif

@if(($delivery_note->note ?? '') !== '')
    <div class="box">
        <div class="h2">Megjegyzés</div>
        <div>{!! nl2br(e($delivery_note->note)) !!}</div>
    </div>
@endif

</body>
</html>
