<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8" />
    <title>Jelszó visszaállítása</title>
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
    <h1>Jelszó visszaállítása</h1>

    <p>Kedves {{ $customer->first_name ?? 'Vásárló' }}!</p>

    <p>Azért kapta ezt az e-mailt, mert valaki jelszó-visszaállítást kért az Ön fiókjához.</p>

    <p>
        A jelszó visszaállításához kattintson az alábbi gombra:
    </p>

    <a href="{{ $url }}" class="btn">Jelszó visszaállítása</a>

    <p>Ha nem Ön kérte ezt, akkor nincs további teendője.</p>

    <div class="footer">
        © {{ now()->year }} Jegvarazsbolt.hu – Minden jog fenntartva.
    </div>
</div>
</body>
</html>
