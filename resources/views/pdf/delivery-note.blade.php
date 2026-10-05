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
        .nowrap { white-space: nowrap; }
        .small { font-size: 10px; }
        .mb-8 { margin-bottom: 8px; }
        .mb-12 { margin-bottom: 12px; }
        .mb-16 { margin-bottom: 16px; }
        .bar { background: #cfe0ff; border: 1px solid #cbd5e1; padding: 4px 8px; font-weight: 700; font-size: 11px; }
        .footer { position: fixed; left: 0; right: 0; bottom: 0; }
        .signature-wrap { margin-top: 14px; }
        .signature-line { border-top: 1px dotted #111827; height: 1px; }
        .signature-label { text-align: center; margin-top: 6px; }
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
            <div class="bar">Raktár</div>
            <div style="font-weight:700;">{{ $warehouse?->name ?? '-' }}</div>
            <div class="muted small">{{ trim(($warehouse?->country ?? '') . ' ' . ($warehouse?->zip_code ?? '') . ' ' . ($warehouse?->city ?? '') . ' ' . ($warehouse?->address_line ?? '')) }}</div>
            <div class="muted small">{{ $warehouse?->email ?? '' }}{{ ($warehouse?->email && $warehouse?->phone) ? ' | ' : '' }}{{ $warehouse?->phone ?? '' }}</div>
        </div>
    </div>
</div>

@if(($delivery_note->note_for_document ?? '') !== '')
    <div class="mb-8">{!! nl2br(e($delivery_note->note_for_document)) !!}</div>
@endif

<div class="mb-12">
    <table>
        <thead>
        <tr>
            <th style="width: 42%;">Megnevezés</th>
            <th style="width: 10%;" class="text-right">Mennyiség</th>
            <th style="width: 8%;">Mee.</th>
            <th style="width: 13%;" class="text-right">Nettó</th>
            <th style="width: 7%;" class="text-right">ÁFA</th>
            <th style="width: 13%;" class="text-right">Bruttó</th>
        </tr>
        </thead>
        <tbody>
        @php
            $sumNet = 0;
            $sumVat = 0;
            $sumGross = 0;
        @endphp
        @foreach(($items ?? []) as $it)
            @php
                $qty = (float) ($it['quantity'] ?? 0);
                $netUnit = $it['net_price'] !== null ? (float) $it['net_price'] : null;
                $vatPercent = $it['vat_percent'] !== null ? (float) $it['vat_percent'] : null;
                $grossUnit = $it['gross_price'] !== null ? (float) $it['gross_price'] : null;

                $netLine = $netUnit !== null ? $netUnit * $qty : null;
                $grossLine = $grossUnit !== null ? $grossUnit * $qty : null;
                $vatLine = ($netLine !== null && $vatPercent !== null) ? ($netLine * ($vatPercent / 100)) : null;

                if ($netLine !== null && $vatLine !== null) {
                    $sumNet += $netLine;
                    $sumVat += $vatLine;
                    $sumGross += ($netLine + $vatLine);
                } elseif ($grossLine !== null) {
                    $sumGross += $grossLine;
                }
            @endphp
            <tr>
                <td>
                    <div style="font-weight:600;">{{ $it['name'] ?? '' }}</div>
                    @if(!empty($it['note']))
                        <div class="muted small">{{ $it['note'] }}</div>
                    @endif
                </td>
                <td class="text-right nowrap">{{ rtrim(rtrim(number_format((float) ($it['quantity'] ?? 0), 3, '.', ''), '0'), '.') }}</td>
                <td>{{ $it['unit'] ?? 'db' }}</td>
                <td class="text-right nowrap">
                    @if($netUnit !== null)
                        {{ number_format($netUnit, 2, ',', ' ') }} Ft
                    @endif
                </td>
                <td class="text-right nowrap">
                    @if($vatPercent !== null)
                        {{ rtrim(rtrim(number_format($vatPercent, 2, '.', ''), '0'), '.') }}%
                    @endif
                </td>
                <td class="text-right nowrap">
                    @if($grossUnit !== null)
                        {{ number_format($grossUnit, 2, ',', ' ') }} Ft
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <th colspan="3" class="text-right">Összesen</th>
            <th class="text-right nowrap">{{ number_format((float) $sumNet, 2, ',', ' ') }} Ft</th>
            <th class="text-right nowrap">{{ number_format((float) $sumVat, 2, ',', ' ') }} Ft</th>
            <th class="text-right nowrap">{{ number_format((float) $sumGross, 2, ',', ' ') }} Ft</th>
        </tr>
        </tfoot>
    </table>
</div>

<div class="footer">
    <div class="grid signature-wrap">
        <div class="col col-50" style="padding-right:12px;">
            <div class="signature-line"></div>
            <div class="signature-label small muted">Átadó aláírás</div>
        </div>
        <div class="col col-50" style="padding-left:12px;">
            <div class="signature-line"></div>
            <div class="signature-label small muted">Átvevő aláírás</div>
        </div>
    </div>
</div>

</body>
</html>
