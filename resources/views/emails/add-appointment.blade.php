<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8" />
    <title>Időpontfoglalás megerősítése</title>
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
    <h1>Kedves {{ $appointment->name }}!</h1>

    <p>Köszönjük, hogy időpontot foglalt nálunk. Az alábbiakban összegzünk néhány fontos információt a foglalással kapcsolatban:</p>

    <div class="details">
        <p><strong>Időpont típusa:</strong> {{ $appointment->appointment_type }}</p>
        <p><strong>Név:</strong> {{ $appointment->name }}</p>
        <p><strong>E-mail:</strong> {{ $appointment->email }}</p>
        <p><strong>Telefon:</strong> {{ $appointment->phone }}</p>
        <p><strong>Irányítószám:</strong> {{ $appointment->zip_code }}</p>
        <p><strong>Város:</strong> {{ $appointment->city }}</p>
        <p><strong>Cím:</strong> {{ $appointment->address_line }}</p>
        @if(!empty($appointment->appointment_date))
            <p><strong>Dátum:</strong> {{ $appointment->appointment_date }}</p>
        @endif
        @if(!empty($appointment->message))
            <p><strong>Megjegyzés:</strong> {{ $appointment->message }}</p>
        @endif
        
        <hr>
        <h4 style="margin-top: 25px; color: #0077b6;">Elérhetőségeink</h4>
        <p>
            <strong>Cím:</strong> {{ $basic_data['company_address'] ?? '' }}<br>
            <strong>Telefon:</strong> {{ $basic_data['company_appointment_phone'] ?? '' }}<br>
            <strong>E-mail:</strong> {{ $basic_data['company_appointment_email'] ?? '' }}
        </p>

        <p>Üdvözlettel,<br />{{ $basic_data['company_name'] ?? '' }}</p>

        <small style="font-style: italic;">Kérjük, erre az e-mail címre ne válaszoljon.</small>
    </div>

    <div class="footer">
        © {{ now()->year }} Jegvarazsbolt.hu – Minden jog fenntartva.
    </div>
</div>
</body>
</html>
