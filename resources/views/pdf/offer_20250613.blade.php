<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Ajánlat PDF</title>
    <style>
        @php
            $imagePath = asset('static_media/offer_bg_image.jpeg');
            $imageData = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath);
            $backgroundImage = "data:$mimeType;base64,$imageData";
        @endphp


        @page {
            margin: 0;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background-color: white;
            background-image: url("{{ $backgroundImage }}");
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
        <p style="margin-top: 30mm">{{ $offer->name }} részére</p>
        <p style="margin-top: 10mm">Örömmel vettük érdeklődését szolgáltatásunk iránt, melyről üzleti feltételeink szerint és az Ön által
            megadott paraméterek alapján az alábbi árajánlatot adja ki a
            <b>Gál-Ker Jégvarázs Klíma és Biztonságtechnikai Kft.</b></p>
        <h2 style="margin-top: 10mm; font-weight: bold; font-style: italic; text-decoration: underline">{{ $offer->title }}</h2>
        <table style="margin-top: 10mm">

            @php
                $total_gross = 0;
            @endphp
            <tr>
                <th class="product-name">Termék</th>
                <th class="product-name">Mennyiség</th>
                <th class="product-price">Bruttó egységár</th>
            </tr>
            @foreach ($products as $product)
                <tr>
                    <td class="product-name">{{ $product['title'] }}</td>
                    <td class="product-name">{{ $product['quantity'] }} db</td>
                    <td class="product-price">{{ number_format($product['gross_price'], 0, ',', ' ') }} Ft</td>
                </tr>
                @php
                    $total_gross += $product['gross_price'] * $product['quantity'];
                @endphp
            @endforeach

        </table>
        <div style="margin-top: 15mm; text-align: right; float: right; display: block; font-style: italic">
            <b style="text-decoration: underline">Összesen: {{ number_format($total_gross, 0, ',', ' ') }} Ft</b>
            <p>Az árak forintban értendők és tartalmazzák az ÁFÁT!</p>
        </div>
        <div style="clear: both;"></div>
        <h2 style="font-style: italic">Az árváltozás jogát fenntartjuk.</h2>
        <h2 style="font-style: italic">Árajánlatunk a kiadástól számítva 14 napig érvényes!</h2>
        <h2 style="font-style: italic">Jászberény, {{ date('Y.m.d') }}.</h2>

    </div>
</body>
</html>
