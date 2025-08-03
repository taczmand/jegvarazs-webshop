<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8" />
    <title>Szerződés megkötése</title>
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
    <h1>Kedves {{ $contract->name }}!</h1>

    <p>Köszönjük, hogy megkötötte velünk a szerződést. Az alábbiakban összegzünk néhány alapvető adatot:</p>

    <div class="details">
        <p><strong>Szerződés azonosítója:</strong> #{{ $contract->id }}</p>
        <p><strong>Név:</strong> {{ $contract->name }}</p>
        <p><strong>E-mail:</strong> {{ $contract->email }}</p>
        <p><strong>Telefon:</strong> {{ $contract->phone }}</p>
        <p><strong>Irányítószám:</strong> {{ $contract->zip_code }}</p>
        <p><strong>Város:</strong> {{ $contract->city }}</p>
        <p><strong>Cím:</strong> {{ $contract->address_line }}</p>
    </div>

    <p>A szerződés részleteit a csatolt PDF dokumentumban találja.</p>

    <small style="font-style: italic;">Kérjük, erre az e-mail címre ne válaszoljon.</small>

    <div class="footer">
        © {{ now()->year }} Jegvarazsbolt.hu – Minden jog fenntartva.
    </div>
</div>
</body>
</html>
