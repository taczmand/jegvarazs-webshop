<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Ajánlat PDF</title>
    <style>
        @page { margin: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 0; padding: 0; background-color: white; }
        .content { display: block; padding: 20mm; box-sizing: border-box; width: 170mm; }
        table { width: 100%; border-collapse: collapse; margin-top: 10mm; }
        table td { border-bottom: 1px solid #999; padding: 4px 1px; vertical-align: bottom; }
        .product-name { text-align: left; }
        .product-qty { border-left: 1px solid #999; text-align: left; }
        .product-price { text-align: left; border-left: 1px solid #999; white-space: nowrap; }
    </style>
</head>
<body>
<div class="content">
    <h2 style="margin-top: 5mm; font-weight: bold; font-style: italic; text-decoration: underline">{{ $offer->title }}</h2>

    <table style="margin-top: 10mm">
        @php $total_gross = 0; @endphp
        <tr>
            <th class="product-name">Tétel</th>
            <th class="product-qty">Menny.</th>
            <th class="product-price">Br. egységár</th>
        </tr>
        @foreach ($items as $item)
            <tr>
                <td class="product-name">{{ $item['title'] }}</td>
                <td class="product-qty">{{ $item['quantity'] }} db</td>
                <td class="product-price">{{ number_format($item['gross_price'], 0, ',', ' ') }} Ft</td>
            </tr>
            @php $total_gross += $item['gross_price'] * $item['quantity']; @endphp
        @endforeach
    </table>

    <div style="margin-top: 10mm; text-align: right; float: right; display: block; font-style: italic">
        <b style="text-decoration: underline">Összesen: {{ number_format($total_gross, 0, ',', ' ') }} Ft</b>
        <p>Az árak forintban értendők és tartalmazzák az ÁFÁT!</p>
    </div>

    <div style="clear: both;"></div>

    @if(!empty($offer->note))
        <div style="margin-top: 10mm;">
            <div style="font-weight: bold;">Megjegyzés</div>
            <div style="white-space: pre-wrap;">{{ $offer->note }}</div>
        </div>
    @endif

    <h4 style="font-style: italic; margin-top: 20mm">Az árváltozás jogát fenntartjuk.</h4>
    <h4 style="font-style: italic">Árajánlatunk a kiadástól számítva 14 napig érvényes!</h4>
    <h4 style="font-style: italic">{{ date('Y.m.d') }}.</h4>
</div>
</body>
</html>
