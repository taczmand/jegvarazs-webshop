<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Sikeres regisztráció</title>
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            padding: 20px;
            color: #333;
        }

        .email-container {
            background-color: #ffffff;
            max-width: 600px;
            margin: 0 auto;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        h1 {
            color: #1882E2;
        }

        .btn {
            display: inline-block;
            background-color: #1882E2;
            color: #ffffff !important;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
<div class="email-container">
    <h1>Sikeres regisztráció!</h1>
    <p>Kedves {{ $customer->first_name }},</p>

    <p>Örömmel értesítjük, hogy fiókja sikeresen létrejött rendszerünkben.</p>

    @if ($customer->is_partner == 0)
        <p>Mostantól bejelentkezhet az oldalunkra az alábbi gombbal:</p>
        <a href="{{ route('login') }}" class="btn">Bejelentkezés</a>
    @else
        <p>Partner regisztrációját fogadtuk. Jelenleg ellenőrizzük az adatait, és amint jóváhagytuk, e-mailben értesítjük Önt a hozzáférésről.</p>
    @endif

    <p>Ha bármilyen kérdése van, vegye fel velünk a kapcsolatot.</p>

    <small style="font-style: italic">Kérjük, erre az e-mail címre ne válaszoljon!</small>

    <p>Üdvözlettel,<br>A {{ $basicdata['company_name'] ?? '' }} csapata</p>

    <div class="footer">
        &copy; {{ date('Y') }} {{ $basicdata['company_name'] ?? '' }}. Minden jog fenntartva.
    </div>
</div>
</body>
</html>

