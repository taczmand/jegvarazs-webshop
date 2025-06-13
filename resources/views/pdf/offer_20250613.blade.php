<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Ajánlat PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h1, h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .contact-info, .products {
            width: 100%;
            margin-bottom: 30px;
        }
        .contact-info td {
            padding: 5px 10px;
        }
        .products th, .products td {
            border: 1px solid #333;
            padding: 8px 12px;
            text-align: left;
        }
        .products th {
            background-color: #eee;
        }
        .products {
            border-collapse: collapse;
        }
    </style>
</head>
<body>
<h1>Ajánlat</h1>

<h2>Kapcsolati adatok</h2>
<table class="contact-info">
    <tr>
        <td><strong>Név:</strong></td>
        <td>{{ $offer->name }}</td>
    </tr>
    <tr>
        <td><strong>Ország:</strong></td>
        <td>{{ $offer->country }}</td>
    </tr>
    <tr>
        <td><strong>Irányítószám:</strong></td>
        <td>{{ $offer->zip_code }}</td>
    </tr>
    <tr>
        <td><strong>Város:</strong></td>
        <td>{{ $offer->city }}</td>
    </tr>
    <tr>
        <td><strong>Cím:</strong></td>
        <td>{{ $offer->address_line }}</td>
    </tr>
    <tr>
        <td><strong>Telefon:</strong></td>
        <td>{{ $offer->phone }}</td>
    </tr>
    <tr>
        <td><strong>Email:</strong></td>
        <td>{{ $offer->email }}</td>
    </tr>
    <tr>
        <td><strong>Megjegyzés:</strong></td>
        <td>{{ $offer->description }}</td>
    </tr>
</table>

<h2>Termékek</h2>
<table class="products">
    <thead>
    <tr>
        <th>Termék neve</th>
        <th>Bruttó ár (Ft)</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($products as $product)
        <tr>
            <td>{{ $product['title'] }}</td>
            <td>{{ number_format($product['gross_price'], 2, ',', ' ') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
