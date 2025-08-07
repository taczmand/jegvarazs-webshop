<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Ajánlat PDF</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background-color: white;
            background-image: url("{{ asset('static_media/offer_bg_image.jpeg') }}");
            background-position: center center;
            background-repeat: no-repeat;
            background-size: contain;
        }
        .content {
            display: block;
            padding: 20mm;
            box-sizing: border-box;
            width: 170mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10mm;
        }
        table td {
            border-bottom: 1px solid #999;
            padding: 4px 0;
            vertical-align: bottom;
        }
        .product-name {
            text-align: left;
        }
        .product-price {
            text-align: right;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="content">
        <p style="margin-top: 50mm">{{ $offer->name }} részére</p>
        <p style="margin-top: 20mm">Örömmel vettük érdeklődését szolgáltatásunk iránt, melyről üzleti feltételeink szerint és az Ön által
            megadott paraméterek alapján az alábbi árajánlatot adja ki a
            <b>Gál-Ker Jégvarázs Klíma és Biztonságtechnikai Kft.</b></p>
        <h2 style="margin-top: 20mm; font-weight: bold; font-style: italic; text-decoration: underline">{{ $offer->title }}</h2>
        <table style="margin-top: 20mm">

            @php
                $total_gross = 0;
            @endphp
            @foreach ($products as $product)
                <tr>
                    <td class="product-name">{{ $product['title'] }}</td>
                    <td class="product-price">{{ number_format($product['gross_price'], 0, ',', ' ') }} Ft</td>
                </tr>
                @php
                    $total_gross += $product['gross_price'];
                @endphp
            @endforeach

        </table>
        <div style="margin-top: 15mm; text-align: right; float: right; display: block; font-style: italic">
            <b style="text-decoration: underline">Összesen: {{ number_format($total_gross, 0, ',', ' ') }} Ft</b>
            <p>Az árak forintban értendők és tartalmazzák az ÁFÁT!</p>
        </div>

    </div>
</body>
</html>
