<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8" />
    <title>{{ $vars['subject'] ?? $automation->email_template }}</title>
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
    <h1>Kedves {{ $automation->full_name }}!</h1>

    <p>Ez egy emlékeztető az Ön által lefoglalt időpontról.</p>

    <div class="details">
        @if(!empty($automation->payload['appointment']['date']))
            <p><strong>Dátum:</strong> {{ $automation->payload['appointment']['date'] }}</p>
        @endif
        @if(!empty($automation->payload['appointment']['time']))
            <p><strong>Időpont:</strong> {{ $automation->payload['appointment']['time'] }}</p>
        @endif

        <p><strong>Cím:</strong> {{ $basic_data['company_address'] ?? '' }}</p>
        <p><strong>Telefon:</strong> {{ $basic_data['company_appointment_phone'] ?? '' }}</p>

        <hr>
        <p>Amennyiben az időpont nem megfelelő, vagy változtatni szeretne rajta, kérjük vegye fel velünk a kapcsolatot.</p>

        <p>Üdvözlettel,<br />{{ $basic_data['company_name'] ?? '' }}</p>

        <small style="font-style: italic;">Kérjük, erre az e-mail címre ne válaszoljon.</small>
    </div>

    <div class="footer">
        © {{ now()->year }} Jegvarazsbolt.hu – Minden jog fenntartva.
    </div>
</div>
</body>
</html>
