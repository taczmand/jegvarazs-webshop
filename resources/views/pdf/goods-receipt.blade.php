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
            <div class="h1">Bevételezési bizonylat</div>
            <div class="muted small">Sorszám: {{ $goods_receipt->document_number }}</div>
            <div class="muted small">Kelt: {{ $goods_receipt->received_at ? $goods_receipt->received_at->format('Y-m-d') : '-' }}</div>
            <div class="muted small">Hivatkozás: {{ $goods_receipt->supplier_document_number ?: '-' }}</div>
        </div>
        <div class="col col-50" style="text-align:right;">
            <div style="font-weight:700;">{{ $goods_receipt->company_name ?? '-' }}</div>
            <div class="muted small">
                {{ trim(($goods_receipt->company_country ?? '') . ' ' . ($goods_receipt->company_zip_code ?? '') . ' ' . ($goods_receipt->company_city ?? '') . ' ' . ($goods_receipt->company_address_line ?? '')) }}
            </div>
            <div class="muted small">Adószám: {{ $goods_receipt->company_tax_number ?? '-' }}</div>
            <div class="muted small">{{ $goods_receipt->company_email ?? '' }}{{ (($goods_receipt->company_email ?? '') && ($goods_receipt->company_phone ?? '')) ? ' | ' : '' }}{{ $goods_receipt->company_phone ?? '' }}</div>
            <div class="muted small">{{ $goods_receipt->company_bank_account ? 'Bank: ' . $goods_receipt->company_bank_account : '' }}</div>
        </div>
    </div>
</div>

<div class="grid mb-16">
    <div class="col col-50" style="padding-right:8px;">
        <div class="box">
            <div class="bar">Partner adatai</div>
            <div style="font-weight:700;">{{ $goods_receipt->partner_name }}</div>
            <div class="muted small">{{ trim(($goods_receipt->partner_country ?? '') . ' ' . ($goods_receipt->partner_zip_code ?? '') . ' ' . ($goods_receipt->partner_city ?? '') . ' ' . ($goods_receipt->partner_address_line ?? '')) }}</div>
            <div class="muted small">Adószám: {{ $goods_receipt->partner_tax_number ?? '-' }}</div>
        </div>
    </div>
    <div class="col col-50" style="padding-left:8px;">
        <div class="box">
            <div class="bar">Raktár</div>
            <div style="font-weight:700;">{{ $warehouse?->name ?? '-' }}</div>
            <div class="muted small">{{ trim(($warehouse?->country ?? '') . ' ' . ($warehouse?->zip_code ?? '') . ' ' . ($warehouse?->city ?? '') . ' ' . ($warehouse?->address_line ?? '')) }}</div>
        </div>
    </div>
</div>

@if(($goods_receipt->note_before_items ?? '') !== '')
    <div class="mb-8">{!! nl2br(e($goods_receipt->note_before_items)) !!}</div>
@endif

@php
    $sumNet = 0;
    $sumVat = 0;
    $sumGross = 0;
    foreach ($items as $it) {
        $sumNet += (int) ($it['net_value'] ?? 0);
        $sumVat += (int) ($it['vat_value'] ?? 0);
        $sumGross += (int) ($it['gross_value'] ?? 0);
    }
@endphp

<div class="mb-12">
    <table>
        <thead>
        <tr>
            <th style="width: 40%;">Megnevezés</th>
            <th style="width: 10%;" class="text-right">Menny.</th>
            <th style="width: 8%;">Mee.</th>
            <th style="width: 10%;" class="text-right">Egységár (Ft)</th>
            <th style="width: 6%;" class="text-right">ÁFA</th>
            <th style="width: 10%;" class="text-right">Nettó (Ft)</th>
            <th style="width: 8%;" class="text-right">ÁFA (Ft)</th>
            <th style="width: 8%;" class="text-right">Bruttó (Ft)</th>
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
                <td class="text-right">{{ number_format(((int) ($row['unit_net_price'] ?? 0)), 0, ',', ' ') }}</td>
                <td class="text-right">{{ ((int) ($row['vat_percent'] ?? 0)) }}%</td>
                <td class="text-right">{{ number_format(((int) ($row['net_value'] ?? 0)), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format(((int) ($row['vat_value'] ?? 0)), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format(((int) ($row['gross_value'] ?? 0)), 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="5" class="text-right" style="font-weight:700;">Összesen</td>
            <td class="text-right" style="font-weight:700;">{{ number_format($sumNet, 0, ',', ' ') }}</td>
            <td class="text-right" style="font-weight:700;">{{ number_format($sumVat, 0, ',', ' ') }}</td>
            <td class="text-right" style="font-weight:700;">{{ number_format($sumGross, 0, ',', ' ') }}</td>
        </tr>
        </tbody>
    </table>
</div>

@if(($goods_receipt->note_after_items ?? '') !== '')
    <div class="mb-8">{!! nl2br(e($goods_receipt->note_after_items)) !!}</div>
@endif

@if(($goods_receipt->note ?? '') !== '')
    <div class="muted small">{!! nl2br(e($goods_receipt->note)) !!}</div>
@endif

</body>
</html>
