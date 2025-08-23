<!-- Humberger Begin -->
<div class="humberger__menu__overlay"></div>
<div class="humberger__menu__wrapper">
    <div class="humberger__menu__logo">
        <a href="#"><img src="{{ asset('static_media/logo.jpg') }}" alt=""></a>
    </div>
    <div class="humberger__menu__cart">
        <ul>
            <!--<li><a href="#"><i class="fa fa-heart"></i> <span>0</span></a></li>-->
            <li><a href="{{ route('cart') }}"><i class="fa fa-shopping-bag"></i> <span class="cart_count">0</span></a></li>
        </ul>
        <div class="header__cart__price">összesen: <span class="cart_total_item_amount">0</span></div>
    </div>
    <div class="humberger__menu__widget">
        <div class="header__top__right__auth dropdown">
            @auth('customer')
                <a class="" href="{{ route('customer.orders') }}">
                    <i class="fa fa-solid fa-list me-2"></i> Rendelések
                </a>
                <a class="" href="{{ route('customer.profile') }}" title="Profil szerkesztése">
                    <i class="fa fa-solid fa-user me-2"></i> {{ auth('customer')->user()->first_name }}
                </a>
                <a class="" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fa fa-sign-out-alt me-2"></i>Kijelentkezés
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="GET" class="d-none">
                    @csrf
                </form>
            @else
                <a class="" href="{{ route('login') }}">
                    <i class="fa fa-sign-in-alt me-2"></i>Bejelentkezés
                </a>

                <a class="mt-2" href="{{ route('registration') }}">
                    <i class="fa fa-user-plus me-2"></i>Regisztráció
                </a>
            @endauth
        </div>
    </div>
    <nav class="humberger__menu__nav mobile-menu">
        <ul>
            <li class="{{ Route::currentRouteName() === 'downloads' ? 'active' : '' }}"><a href="{{ config('app.url') }}letoltesek">Letöltések</a></li>
            <li class="{{ Route::currentRouteName() === 'blog' ? 'active' : '' }}"><a href="{{ config('app.url') }}blog">Blog</a></li>
            <li class="{{ Route::currentRouteName() === 'appointments' ? 'active' : '' }}"><a href="{{ config('app.url') }}idopontfoglalas">Időpontfoglalás</a></li>
            <li class="{{ Route::currentRouteName() === 'contact' ? 'active' : '' }}"><a href="{{ config('app.url') }}kapcsolat">Kapcsolat</a></li>
        </ul>
    </nav>
    <div id="mobile-menu-wrap"></div>
    <div class="header__top__right__social">
        @if(!empty($basicdata['social_facebook']))
            <a href="{{ $basicdata['social_facebook'] }}" target="_blank"><i class="fab fa-facebook"></i></a>
        @endif
        @if(!empty($basicdata['social_instagram']))
            <a href="{{ $basicdata['social_instagram'] }}" target="_blank"><i class="fab fa-instagram"></i></a>
        @endif
        @if(!empty($basicdata['social_twitter']))
            <a href="{{ $basicdata['social_twitter'] }}" target="_blank"><i class="fab fa-twitter"></i></a>
        @endif
        @if(!empty($basicdata['social_linkedin']))
            <a href="{{ $basicdata['social_linkedin'] }}" target="_blank"><i class="fab fa-linkedin"></i></a>
        @endif
        @if(!empty($basicdata['social_youtube']))
            <a href="{{ $basicdata['social_youtube'] }}" target="_blank"><i class="fab fa-youtube"></i></a>
        @endif
        @if(!empty($basicdata['social_tiktok']))
            <a href="{{ $basicdata['social_tiktok'] }}" target="_blank"><i class="fab fa-tiktok"></i></a>
        @endif
        @if(!empty($basicdata['social_pinterest']))
            <a href="{{ $basicdata['social_pinterest'] }}" target="_blank"><i class="fab fa-pinterest-p"></i></a>
        @endif
        @if(!empty($basicdata['social_whatsapp']))
            <a href="{{ $basicdata['social_whatsapp'] }}" target="_blank"><i class="fab fa-whatsapp"></i></a>
        @endif
        @if(!empty($basicdata['social_telegram']))
            <a href="{{ $basicdata['social_telegram'] }}" target="_blank"><i class="fab fa-telegram-plane"></i></a>
        @endif
        @if(!empty($basicdata['social_viber']))
            <a href="{{ $basicdata['social_viber'] }}" target="_blank"><i class="fab fa-viber"></i></a>
        @endif
        @if(!empty($basicdata['social_snapchat']))
            <a href="{{ $basicdata['social_snapchat'] }}" target="_blank"><i class="fab fa-snapchat-ghost"></i></a>
        @endif
        @if(!empty($basicdata['social_twitch']))
            <a href="{{ $basicdata['social_twitch'] }}" target="_blank"><i class="fab fa-twitch"></i></a>
        @endif
    </div>
    <div class="humberger__menu__contact">
        <ul>
            <li><i class="fa fa-map"></i> Cím: <a href="{{ $basicdata['company_address_maps_link'] }}" target="_blank">{{ $basicdata['company_address'] }}</a></li>
            <li><i class="fa fa-phone"></i> Telefon: <a href="tel:{{ $basicdata['support_phone'] }}">{{ $basicdata['support_phone'] }}</a></li>
            <li><i class="fa fa-envelope"></i> Email: <a href="mailto:{{ $basicdata['support_email'] }}">{{ $basicdata['support_email'] }}</a></li>
        </ul>
    </div>
</div>
<!-- Humberger End -->
