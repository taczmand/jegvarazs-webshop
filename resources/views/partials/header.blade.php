<!-- Header Section Begin -->
<header class="header">
    <div class="header__top">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="header__top__left">
                        <ul>
                            <li><i class="fa fa-envelope"></i> {{ $basicdata['support_email'] ?? '' }}</li>
                            <li>{{ $basicdata['header_message'] ?? '' }}</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="header__top__right">
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

                        <div class="header__top__right__auth">
                            <a href="{{ route('login') }}"><i class="fa fa-user"></i> Bejelentkezés</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="header__logo">
                    <a href="{{ route('index') }}"><img src="{{ asset('static_media/logo.jpg') }}" alt=""></a>
                </div>
            </div>
            <div class="col-lg-6">
                <nav class="header__menu">
                    <ul>
                        <li class="{{ Route::currentRouteName() === 'downloads' ? 'active' : '' }}"><a href="/letoltesek">Letöltések</a></li>
                        <li class="{{ Route::currentRouteName() === 'appointments' ? 'active' : '' }}"><a href="/idopontfoglalas">Időpontfoglalás</a></li>
                        <li class="{{ Route::currentRouteName() === 'contact' ? 'active' : '' }}"><a href="/kapcsolat">Kapcsolat</a></li>
                    </ul>
                </nav>
            </div>
            <div class="col-lg-3">
                <div class="header__cart">
                    <ul>
                        <li><a href="#"><i class="fa fa-heart"></i> <span>0</span></a></li>
                        <li><a href="#"><i class="fa fa-shopping-bag"></i> <span>0</span></a></li>
                    </ul>
                    <div class="header__cart__price">összesen: <span>0 Ft</span></div>
                </div>
            </div>
        </div>
        <div class="humberger__open">
            <i class="fa fa-bars"></i>
        </div>
    </div>
</header>
<!-- Header Section End -->
