<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8" />
    <title>Új ajánlat érkezett</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #333;
            padding: 20px;
            margin: 0;
        }
        .container {
            background-color: #ffffff;
            max-width: 600px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            padding: 30px;
        }
        h1 {
            color: #1882E2;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .details {
            background-color: #e9f1fb;
            border-radius: 6px;
            padding: 15px 20px;
            margin-bottom: 20px;
            font-size: 15px;
        }
        .details strong {
            color: #1882E2;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #999999;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Kedves {{ $offer->name }}!</h1>

    <p>Örömmel vettük érdeklődését szolgáltatásunk iránt. Az alábbiakban összegzünk néhány alapvető adatot:</p>

    <div class="details">
        <p><strong>Ajánlat azonosítója:</strong> #{{ $offer->id }}</p>
        <p><strong>Cím:</strong> {{ $offer->title }}</p>
        <p><strong>Név:</strong> {{ $offer->name }}</p>
        <p><strong>E-mail:</strong> {{ $offer->email }}</p>
        <p><strong>Telefon:</strong> {{ $offer->phone }}</p>
        <p><strong>Irányítószám:</strong> {{ $offer->zip_code }}</p>
        <p><strong>Város:</strong> {{ $offer->city }}</p>
        <p><strong>Cím:</strong> {{ $offer->address_line }}</p>
    </div>

    <p>Az ajánlat részleteit a csatolt PDF dokumentumban találja.</p>

    <small style="font-style: italic;">Kérjük, erre az e-mail címre ne válaszoljon.</small>

    <div class="footer">
        © {{ now()->year }} Jegvarazsbolt.hu – Minden jog fenntartva.
    </div>
</div>
</body>
</html>
