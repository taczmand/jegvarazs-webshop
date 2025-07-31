<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Partner fiók aktiválva</title>
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
        }
        p {
            font-size: 16px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            background-color: #1882E2;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
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
    <h1>Kedves {{ $customer->first_name }}!</h1>

    <p>Örömmel értesítünk, hogy partner fiókod aktiválásra került.</p>

    <p>Be tudsz jelentkezni az alábbi gomb segítségével:</p>

    <a href="{{ route('login') }}" class="btn">Bejelentkezés</a>

    <p>Ha bármilyen kérdésed van, keress bennünket bizalommal.</p>

    <small style="font-style: italic">Kérjük, erre az e-mail címre ne válaszolj!</small>

    <div class="footer">
        © {{ now()->year }} Jegvarazsbolt.hu – Minden jog fenntartva.
    </div>
</div>
</body>
</html>
