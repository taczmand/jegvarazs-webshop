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
        .email-link {
            display: inline-block;
            padding: 12px 20px;
            background-color: #0B013E;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            border-radius: 6px;
        }

    </style>

</head>

<body>

<div class="email-container">

    <p class="email-intro">
        Kedves Ügyfelünk
    </p>

    <p>
        Ezúton szeretnénk jelezni, hogy a klímaberendezésének karbantartása esedékessé vált.
        A rendszeres tisztítás elengedhetetlen a garancia megőrzéséhez, a higiénikus, tiszta levegőhöz,
        valamint a készülék hosszú távú, megbízható működéséhez.
    </p>

    <p>A karbantartási folyamatunk során szakembereink professzionális fertőtlenítést és teljes műszaki
        ellenőrzést végeznek, hogy klímája továbbra is, hatékonyan és biztonságosan üzemeljen.</p>

    <div class="highlight-box">
        <strong>&#9733; Foglaljon időpontot kényelmesen, akár online:</strong>
        <br /><br />
        <a class="email-link" href="{{ route('appointment', [
            'full_name'  => $automation->full_name,
            'email_address' => $automation->email_address,
            'phone' => $automation->phone,
            'zip' => $automation->zip,
            'city' => $automation->city,
            'address' => $automation->address,
            'type' => 'Karbantartás'
        ]) }}">
            Időpontfoglalás most
        </a>
        <p>Vagy telefonon az alábbi telefonszámon:</p>
        <a href="tel:+36 (20) 433-1949">&#128222; +36 20 433 1949</a>

    </div>

    <p>
        <b>Köszönjük, hogy a Jégvarázs Klímát választja.</b>
    </p>

    <p>
        <b>További szép napot kívánunk!</b>
    </p>

</div>

</body>
</html>
