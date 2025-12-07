<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>{{ $vars['subject'] ?? $automation->email_template }}</title>

    {{-- Külső CSS behúzása --}}
    <link rel="stylesheet" href="{{ asset('email_automations.css') }}">
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
