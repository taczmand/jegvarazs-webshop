<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $promotion->name }} | Jégvarázs</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f8;
            color: #333;
        }
        .promo-hero-logo {
            display: block;
            max-height: 84px;
            max-width: 260px;
            width: auto;
            height: auto;
            margin: 0 auto 26px;
            position: relative;
            z-index: 1;
        }
        .promo-hero {
            position: relative;
            width: 100%;
            overflow: hidden;
            background: linear-gradient(135deg, #b3122e 0%, #e63946 45%, #1b7a3d 100%);
            color: #fff;
            padding: 110px 20px 130px;
            text-align: center;
        }
        .promo-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 20%, rgba(255,255,255,.18) 0 2px, transparent 3px),
                radial-gradient(circle at 75% 15%, rgba(255,255,255,.15) 0 2px, transparent 3px),
                radial-gradient(circle at 55% 75%, rgba(255,255,255,.12) 0 2px, transparent 3px),
                radial-gradient(circle at 30% 85%, rgba(255,255,255,.15) 0 2px, transparent 3px),
                radial-gradient(circle at 90% 60%, rgba(255,255,255,.12) 0 2px, transparent 3px);
            background-size: 220px 220px;
            animation: promo-snow 14s linear infinite;
            pointer-events: none;
        }
        @keyframes promo-snow {
            from { background-position: 0 0; }
            to { background-position: 0 220px; }
        }
        .promo-hero .promo-badge {
            display: inline-block;
            background: #ffd166;
            color: #7a1f1f;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 8px 22px;
            border-radius: 999px;
            font-size: 14px;
            margin-bottom: 22px;
            box-shadow: 0 6px 18px rgba(0,0,0,.25);
        }
        .promo-hero h1 {
            font-size: clamp(34px, 6vw, 64px);
            font-weight: 900;
            margin: 0 0 18px;
            text-shadow: 0 4px 14px rgba(0,0,0,.35);
        }
        .promo-hero .promo-sub {
            font-size: clamp(16px, 2.4vw, 22px);
            opacity: .95;
            max-width: 760px;
            margin: 0 auto;
        }
        .promo-hero .promo-scroll {
            margin-top: 42px;
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            opacity: .85;
            animation: promo-bounce 1.6s ease-in-out infinite;
        }
        @keyframes promo-bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(8px); }
        }
        .promo-content {
            max-width: 860px;
            margin: -80px auto 60px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 20px 50px rgba(0,0,0,.18);
            padding: 48px 40px;
            position: relative;
            z-index: 2;
        }
        .promo-content .promo-title {
            color: #b3122e;
            font-weight: 900;
            font-size: clamp(26px, 4vw, 38px);
            margin-bottom: 18px;
            text-align: center;
        }
        .promo-content .promo-desc {
            font-size: 17px;
            line-height: 1.75;
            color: #333;
            text-align: center;
            margin-bottom: 34px;
        }
        .promo-form-title {
            background: linear-gradient(90deg, #b3122e, #1b7a3d);
            color: #fff;
            text-align: center;
            font-weight: 800;
            font-size: 20px;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 28px;
        }
        .promo-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .promo-form .promo-input {
            width: 100%;
            height: 50px;
            font-size: 16px;
            padding: 0 14px;
            border: 1px solid #d5dbe3;
            border-radius: 10px;
            margin-bottom: 18px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .promo-form .promo-input:focus {
            outline: none;
            border-color: #e63946;
            box-shadow: 0 0 0 3px rgba(230,57,70,.15);
        }
        .promo-form select.promo-input {
            height: 50px;
        }
        .promo-form .promo-submit {
            display: block;
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 18px;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #fff;
            background: linear-gradient(90deg, #b3122e, #e63946);
            box-shadow: 0 10px 25px rgba(179,18,46,.4);
            transition: transform .15s ease, box-shadow .15s ease;
            cursor: pointer;
        }
        .promo-form .promo-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(179,18,46,.5);
            color: #fff;
        }
        .promo-form .promo-submit:active {
            transform: translateY(0);
        }
        .promo-discount {
            display: inline-block;
            background: #1b7a3d;
            color: #fff;
            font-weight: 900;
            font-size: 22px;
            padding: 10px 26px;
            border-radius: 999px;
            margin: 10px auto 0;
            box-shadow: 0 6px 18px rgba(27,122,61,.4);
        }
        .promo-error {
            background: #fdecea;
            border: 1px solid #f5c6cb;
            color: #a71d2a;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 22px;
            text-align: center;
        }
        .promo-success {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            color: #1b5e20;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 22px;
            text-align: center;
        }
        .promo-footer-note {
            text-align: center;
            color: #888;
            font-size: 13px;
            padding: 0 20px 40px;
        }
        @media (max-width: 576px) {
            .promo-content { padding: 32px 20px; margin: -50px 12px 40px; }
        }
    </style>
</head>
<body>

    <section class="promo-hero">
        <img src="{{ asset('storage/' . $basicmedia['default_logo']) }}" alt="" class="promo-hero-logo">
        <div class="promo-badge">Különleges ajánlat</div>
        <h1>{{ $promotion->name }}</h1>
        @if($promotion->discount_percent)
            <div class="promo-discount">-{{ rtrim(rtrim(number_format((float) $promotion->discount_percent, 2, ',', ''), '0'), ',') }}% kedvezmény</div>
        @endif
        <p class="promo-sub">Ne hagyd ki a szezon legjobb klímalehetőségét — jelentkezz most!</p>
        <div class="promo-scroll">▼ Görgess a jelentkezéshez ▼</div>
    </section>

    <div class="promo-content">
        <h2 class="promo-title">{{ $promotion->name }}</h2>

        @if($promotion->description)
            <div class="promo-desc">{!! nl2br(e($promotion->description)) !!}</div>
        @endif

        @if($errors->any())
            <div class="promo-error">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('success'))
            <div class="promo-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="promo-form-title">Ha szeretnél résztvenni a prómócióban töltsd ki az alábbi adatokat:</div>

        <script src="https://www.google.com/recaptcha/api.js"></script>
        <script>
            function onSubmit(token) {
                document.getElementById("promotion-form").submit();
            }
        </script>

        <form method="POST" id="promotion-form" action="{{ route('promotion.apply') }}" class="promo-form">
            @csrf

            <input type="hidden" name="recaptcha_token" id="recaptcha_token">

            <div>
                <label for="name">Név*</label>
                <input type="text" name="name" id="name" class="promo-input" placeholder="Teljes név" value="{{ old('name') }}" required>
            </div>

            <div>
                <label for="address">Cím*</label>
                <input type="text" name="address" id="address" class="promo-input" placeholder="Irányítószám, város, utca, házszám" value="{{ old('address') }}" required>
            </div>

            <div>
                <label for="phone">Telefonszám*</label>
                <input type="tel" name="phone" id="phone" class="promo-input" placeholder="+36..." value="{{ old('phone') }}" required>
            </div>

            <div>
                <label for="email">Email*</label>
                <input type="email" name="email" id="email" class="promo-input" placeholder="pelda@email.hu" value="{{ old('email') }}" required>
            </div>

            <div>
                <label for="promotion_id">Melyik promócióban kíván részt venni?*</label>
                <select name="promotion_id" id="promotion_id" class="promo-input" required>
                    <option value="" disabled {{ old('promotion_id') ? '' : 'selected' }}>Válasszon promóciót</option>
                    @foreach($promotions as $promo)
                        <option value="{{ $promo->id }}" {{ old('promotion_id') == $promo->id ? 'selected' : '' }}>
                            {{ $promo->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button data-sitekey="6Le1aA4sAAAAAPRyuiMD79NOT2oYekHfOdhNC6Fr"
                    data-callback='onSubmit'
                    data-action='submit' type="submit" class="g-recaptcha promo-submit">JELENTKEZEM A PROMÓCIÓRA</button>
        </form>
    </div>

    <div class="promo-footer-note">© {{ date('Y') }} Jégvarázs — Minden jog fenntartva.</div>

</body>
</html>
