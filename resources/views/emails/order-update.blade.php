@php
    $total_amount = 0;
@endphp

    <!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Rendelés állapotváltozás</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: auto; padding: 20px; }
        .header { background-color: #f4f4f4; padding: 20px; text-align: center; }
        .section { margin-bottom: 30px; }
        .section h2 { border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .product { display: flex; align-items: center; margin-bottom: 10px; }
        .product img { width: 60px; height: auto; margin-right: 15px; border: 1px solid #ccc; }
        .product-details { flex: 1; }
        .summary { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 5px; vertical-align: top; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Rendelése frissült</h1>
        <p>Rendelésszám: #{{ $order->id }}</p>
    </div>

    <div class="section">
        <h2>Rendelés új állapota</h2>
        <p><strong>{{ $order->status_label }}</strong></p>
    </div>

    <div class="section">
        <h2>Termékek</h2>
        @foreach ($order_items as $item)
            <div class="product">
                <div class="product-details">
                    <div><strong>{{ $item['product_name'] }}</strong></div>
                    <div>Mennyiség: {{ $item['quantity'] }}</div>
                    <div>Bruttó egységár: {{ number_format($item['gross_price'], 0, ',', ' ') }} Ft</div>
                </div>
            </div>
            @php
                $total_amount += $item['gross_price'] * $item['quantity'];
            @endphp
        @endforeach
    </div>

    <div class="section">
        <h2>Kapcsolati adatok</h2>
        <table>
            <tr><td>Név:</td><td>{{ $order->contact_last_name }} {{ $order->contact_first_name }}</td></tr>
            <tr><td>E-mail cím:</td><td>{{ $order->contact_email }}</td></tr>
            <tr><td>Telefon:</td><td>{{ $order->contact_phone }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Szállítási adatok</h2>
        <table>
            <tr><td>Név:</td><td>{{ $order->shipping_name }}</td></tr>
            <tr><td>Cím:</td><td>{{ $order->shipping_country }} {{ $order->shipping_postal_code }} {{ $order->shipping_city }}, {{ $order->shipping_address_line }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Számlázási adatok</h2>
        <table>
            <tr><td>Név:</td><td>{{ $order->billing_name }}</td></tr>
            <tr><td>Cím:</td><td>{{ $order->billing_country }} {{ $order->billing_postal_code }} {{ $order->billing_city }}, {{ $order->billing_address_line }}</td></tr>
            <tr><td>Adószám:</td><td>{{ $order->billing_tax_number ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Összegzés</h2>
        <table>
            <tr class="summary"><td>Bruttó végösszeg:</td><td>{{ number_format($total_amount, 0, ',', ' ') }} Ft</td></tr>
        </table>
    </div>
</div>
</body>
</html>
