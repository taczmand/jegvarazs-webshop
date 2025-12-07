<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>{{ $vars['subject'] ?? $automation->email_template }}</title>
    <style>
        body {
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            color: #333;
        }

        .email-container {
            max-width: 600px;
            background: #ffffff;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .email-title {
            font-size: 22px;
            margin-bottom: 20px;
            color: #444;
        }

        .email-intro {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .highlight-box {
            background: #e8f5ff;
            border-left: 4px solid #3498db;
            padding: 10px;
            margin: 20px 0;
            font-size: 15px;
        }

        .footer-text {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }

    </style>

</head>

<body>

<div class="email-container">

    <h1 class="email-title">
        {{ $automation->email_template }}
    </h1>

    <p class="email-intro">
        Kedves {{ $automation->email_address ?? 'Ügyfelünk' }}!
    </p>

    <p>
        Ez egy automatikusan küldött értesítés a következő szolgáltatásról:
    </p>

    <div class="highlight-box">
        <strong>{{ $automation->email_template }}</strong>
    </div>

    <p>
        Ha kérdésed van, nyugodtan lépj kapcsolatba velünk!
    </p>

    <p class="footer-text">
        Üdvözlettel,<br>
        A rendszer
    </p>

</div>

</body>
</html>
