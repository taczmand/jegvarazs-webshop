<!-- Footer Section Begin -->
<footer class="footer spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="footer__about">
                    <div class="footer__about__logo">
                        <a href="{{ route('index') }}"><img src="{{ asset('storage/' . $basicmedia['default_logo']) }}" alt=""></a>
                    </div>
                    <ul>
                        <li>Cím: <a href="{{ $basicdata['company_address_maps_link'] }}" target="_blank">{{ $basicdata['company_address'] }}</a></li>
                        <li>Telefon: <a href="tel:{{ $basicdata['support_phone'] }}">{{ $basicdata['support_phone'] }}</a></li>
                        <li>Email: <a href="mailto:{{ $basicdata['support_email'] }}">{{ $basicdata['support_email'] }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 offset-lg-1">
                <div class="footer__widget">
                    <h6>Szabályzatok</h6>

                    <ul>
                        @foreach ($regulations as $regulation)
                            <a href="storage/{{ $regulation->file_path }}" target="_blank"><li>{{ $regulation->file_name }}</li></a>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="footer__widget">
                    <h6>Iratkozzon fel hírlevelünkre!</h6>
                    <p>Értesüljön elsőként híreinkről, ajánlatainkról és feliratkozóinknak szóló, különleges akcióinkról!</p>
                    <form>
                        <input type="text" placeholder="E-mail cím" id="subscription_email" required>
                        <button data-subscribe-button class="site-btn">Feliratkozás</button>
                    </form>
                    <div class="footer__widget__social">
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
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="footer__copyright">
                    <div class="footer__copyright__text"><p>
                        @if(!empty($basicdata['company_footer_text']))
                            {!! $basicdata['company_footer_text'] !!}
                        @endif
                    </div>
                    <div style="float: right; width: 250px">
                        <a href="{{ env('SIMPLEPAY_LOGO_URL') }}" target="_blank"><img src="{{ asset('static_media/'.env('SIMPLEPAY_LOGO')) }}"></a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</footer>
<!-- Footer Section End -->
