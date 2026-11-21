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
        .promo-hero {
            position: relative;
            width: 100%;
            overflow: hidden;
            background: url('{{ asset('static_media/karacsonyi_promocio_2026_banner.webp') }}') center / cover no-repeat, linear-gradient(135deg, #b3122e 0%, #e63946 45%, #1b7a3d 100%);
            color: #fff;
            min-height: 85vh;
            padding: 120px 20px 110px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-sizing: border-box;
        }
        .promo-hero-img-mobile { display: none; }
        @media (max-width: 768px) {
            .promo-hero {
                background: linear-gradient(135deg, #b3122e 0%, #e63946 45%, #1b7a3d 100%);
                min-height: 0;
                padding: 0 0 50px;
            }
            .promo-hero-img-mobile {
                display: block;
                width: 100%;
                height: auto;
            }
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
        .promo-form .promo-terms {
            margin-bottom: 18px;
            font-size: 14px;
        }
        .promo-form .promo-terms label {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-weight: 400;
            margin-bottom: 0;
            cursor: pointer;
        }
        .promo-form .promo-terms input {
            margin-top: 3px;
            flex-shrink: 0;
        }
        .promo-form .promo-terms a {
            color: #b3122e;
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
    
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-WS3SM6VQ');</script>
    <!-- End Google Tag Manager -->
</head>
<body>
    <section class="promo-hero">
        <img src="{{ asset('static_media/karacsonyi_promocio_2026_banner.webp') }}" alt="Karácsonyi klímareklám" class="promo-hero-img-mobile">
        @if($promotion->discount_percent)
            <div class="promo-discount">-{{ rtrim(rtrim(number_format((float) $promotion->discount_percent, 2, ',', ''), '0'), ',') }}% kedvezmény</div>
        @endif

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
            function disablePromoSubmit() {
                var btn = document.querySelector('#promotion-form .promo-submit');
                if (btn) {
                    btn.disabled = true;
                    btn.style.opacity = '.6';
                    btn.style.cursor = 'not-allowed';
                }
            }
            function onSubmit(token) {
                disablePromoSubmit();
                document.getElementById("promotion-form").submit();
            }
            document.addEventListener('DOMContentLoaded', function () {
                var form = document.getElementById('promotion-form');
                if (form) {
                    form.addEventListener('submit', disablePromoSubmit);
                }
            });
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
                    <option value="2 szett klímaberendezés mellé ajándék Dual Zone Air Fryer készülék" {{ old('promotion_id') == '2 szett klímaberendezés mellé ajándék Dual Zone Air Fryer készülék' ? 'selected' : '' }}>2 szett klímaberendezés mellé ajándék Dual Zone Air Fryer készülék</option>
                    <option value="3 szett klímaberendezés mellé ajándék 40&quot; Smart LED TV" {{ old('promotion_id') == '3 szett klímaberendezés mellé ajándék 40" Smart LED TV' ? 'selected' : '' }}>3 szett klímaberendezés mellé ajándék 40" Smart LED TV</option>
                </select>
            </div>
            @if($regulation)
            <div class="promo-terms">
                <label for="terms">
                    <input type="checkbox" name="terms" id="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
                    Elfogadom a <a href="{{ asset('storage/' . $regulation->file_path) }}" target="_blank" rel="noopener">{{ $regulation->file_name }}</a> szabályzatot. *
                </label>
            </div>
            @endif
            <button data-sitekey="6Le1aA4sAAAAAPRyuiMD79NOT2oYekHfOdhNC6Fr"
                    data-callback='onSubmit'
                    data-action='submit' type="submit" class="g-recaptcha promo-submit">JELENTKEZEM A PROMÓCIÓRA</button>
        </form>
    </div>
    <div class="promo-footer-note">© {{ date('Y') }} Jégvarázs — Minden jog fenntartva.</div>
</body>
</html>
