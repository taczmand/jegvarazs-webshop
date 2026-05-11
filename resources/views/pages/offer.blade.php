@extends('layouts.shop')

@section('content')

    <style>
        :root { --offer-card-radius: 1.75rem; }

        .offer-input-icon-wrap { position: relative; }
        .offer-input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            opacity: .65;
            pointer-events: none;
        }
        .offer-input-icon-input { padding-left: 44px; }

        .offer-textarea-icon { top: 14px; transform: none; }
        .offer-textarea { padding-top: 10px; }

        .offer-form-title-row { display: flex; align-items: flex-start; gap: 12px; }
        .offer-form-title-icon {
            width: 44px;
            height: 44px;
            border-radius: 9999px;
            background: #0b5ed7;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 44px;
        }
        .offer-form-title-icon i { font-size: 22px; line-height: 1; }
        .offer-form-title { margin: 0; }
        .offer-form-subtitle { margin: 0; opacity: .85; }

        .offer-banner-wrap { margin-bottom: -56px; border-radius: var(--offer-card-radius) var(--offer-card-radius) 0 0 !important; overflow: hidden; }
        .offer-form-card { position: relative; z-index: 2; border-radius: var(--offer-card-radius) !important; }
        .offer-submit-btn { border-radius: var(--offer-card-radius) !important; background: #0b5ed7; border-color: #0b5ed7; }
        .offer-banner-img { border-radius: var(--offer-card-radius) var(--offer-card-radius) 0 0 !important; }

        .offer-benefits { display: grid; grid-template-columns: 1fr; gap: .5rem; }
        .offer-benefit {
            display: inline-flex;
            align-items: flex-start;
            gap: .55rem;
            padding: .55rem .7rem;
            font-weight: 700;
            color: #0d6efd;
            background: rgba(13, 110, 253, .08);
            border-radius: 0;
            box-shadow: 0 .25rem .75rem rgba(0,0,0,.06);
            line-height: 1.15;
            font-size: .78rem;
            min-width: 0;
        }
        .offer-benefit i { font-size: 1.25rem; line-height: 1; flex: 0 0 auto; width: 1.35rem; text-align: center; margin-top: .05rem; }
        .offer-benefit i.offer-benefit-icon-green { color: #198754; }
        .offer-benefit-text { display: flex; flex-direction: column; gap: .15rem; min-width: 0; }
        .offer-benefit-title { display: block; white-space: normal; color: #0b5ed7; text-transform: uppercase; letter-spacing: .02em; }
        .offer-benefit-sub { display: block; font-weight: 600; opacity: 1; white-space: normal; font-size: .95em; color: #000; }

        @media (min-width: 380px) {
            .offer-benefits { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (min-width: 992px) {
            .offer-benefits { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (min-width: 992px) {
            .offer-banner-wrap { margin-bottom: -140px; }
            .offer-banner-img { height: 60vh; object-fit: cover; }
        }

        @media (max-width: 576px) {
            .offer-form-title { font-size: 1.15rem; }
            .offer-form-subtitle { font-size: .95rem; }
        }
    </style>

    @if($errors->any())
        <div class="shop-validation-error">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>

        <script>
            if (typeof fbq === 'function') {
                fbq('track', 'Lead');
            }
        </script>
    @endif

    <script src="https://www.google.com/recaptcha/api.js"></script>

    <script>
        function onSubmit(token) {
            document.getElementById("contact-form").submit();
        }
    </script>


    @if(!empty($basicmedia['offer_banner']))
        <div class="offer-banner-wrap">
            <img src="{{ asset('storage/' . $basicmedia['offer_banner']) }}" alt="Ajánlatkérés banner" class="img-fluid w-100 offer-banner-img">
        </div>
    @endif


    <form method="POST" id="contact-form" action="{{ route('offer.post') }}" class="w-100 p-4 bg-light rounded shadow-sm light-box mb-5 offer-form-card">
        @csrf

        <input type="hidden" name="recaptcha_token" id="recaptcha_token">

        <div class="offer-form-title-row mb-4">
            <div class="offer-form-title-icon">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <h4 class="offer-form-title">Ajánlatkérés</h4>
                <p class="offer-form-subtitle">Kérjük, adja meg az adatait és igényeit!</p>
            </div>
        </div>

        <div class="mb-3">
            <div class="offer-input-icon-wrap">
                <i class="fa-solid fa-user offer-input-icon"></i>
                <input type="text" name="name" id="name" class="form-control offer-input-icon-input" placeholder="Teljes név*" value="{{ request()->query('full_name') }}" required>
            </div>
        </div>

        <div class="mb-3">
            <div class="offer-input-icon-wrap">
                <i class="fa-solid fa-phone offer-input-icon"></i>
                <input type="text" name="phone" id="phone" class="form-control offer-input-icon-input" value="{{ request()->query('phone') }}" placeholder="Telefonszám* (+36...)" required>
            </div>
        </div>

        <div class="mb-3">
            <div class="offer-input-icon-wrap">
                <i class="fa-solid fa-envelope offer-input-icon"></i>
                <input type="email" name="email" id="email" class="form-control offer-input-icon-input" value="{{ request()->query('email_address') }}" placeholder="E-mail cím*" required>
            </div>
        </div>

        <div class="mb-3">
            <div class="offer-input-icon-wrap">
                <i class="fa-solid fa-location-dot offer-input-icon"></i>
                <input type="text" name="location" id="location" class="form-control offer-input-icon-input" value="{{ request()->query('location') ?? request()->query('city') }}" placeholder="Helyszín*" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label" style="display:block">Megjegyzés</label>
            <div class="offer-input-icon-wrap">
                <i class="fa-solid fa-comment-dots offer-input-icon offer-textarea-icon"></i>
                <textarea name="message" id="message" class="form-control offer-input-icon-input offer-textarea" rows="2" placeholder="Pl.: 2 szobás lakásba szeretnék 1 db split klímát"></textarea>
            </div>
        </div>

        <div class="offer-benefits mb-3">
            <div class="offer-benefit">
                <i class="fa-solid fa-clipboard-check"></i>
                <span class="offer-benefit-text">
                    <span class="offer-benefit-title">Ingyenes felmérés és szaktanácsadás</span>
                    <span class="offer-benefit-sub">Nincs rejett költség, teljesen díjmentes.</span>
                </span>
            </div>
            <div class="offer-benefit">
                <i class="fa-solid fa-shield-halved"></i>
                <span class="offer-benefit-text">
                    <span class="offer-benefit-title">Garancia</span>
                    <span class="offer-benefit-sub">Minden munkára és készlékre teljes körű garanciát vallalunk.</span>
                </span>
            </div>
        </div>

        <button data-sitekey="6Le1aA4sAAAAAPRyuiMD79NOT2oYekHfOdhNC6Fr"
                data-callback='onSubmit'
                data-action='submit' type="submit" class="g-recaptcha site-btn w-100 offer-submit-btn">Ajánlatkérés elküldése</button>
    </form>


@endsection
