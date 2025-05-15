<!-- Hero Section Begin -->
<section class="hero {{ $extra_class ?? '' }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="hero__categories">
                    <div class="hero__categories__all">
                        <i class="fa fa-bars"></i>
                        <span>Termékek</span>
                    </div>
                    <ul>
                        <li><a href="#">Klíma</a></li>
                        <li><a href="#">Rézcső</a></li>
                        <li><a href="#">Konzol</a></li>
                        <li><a href="#">Termosztát</a></li>
                        <li><a href="#">Légtechnika</a></li>
                        <li><a href="#">Tudom is én mi</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="hero__search">
                    <div class="hero__search__form">
                        <form action="#">
                            <input type="text" placeholder="Kereséshez gépeljen ide...">
                            <button type="submit" class="site-btn">Keresés</button>
                        </form>
                    </div>
                    <div class="hero__search__phone">
                        <div class="hero__search__phone__icon">
                            <i class="fa fa-phone"></i>
                        </div>
                        <div class="hero__search__phone__text">
                            <h5>+36 (20) 778-9928</h5>
                            <span>Várjuk hívását!</span>
                        </div>
                    </div>
                </div>

                {{-- Hero Item dinamikusan --}}
                @if (!empty($showHeroItem))
                    @include('partials.heroitem')
                @endif
            </div>
        </div>
    </div>
</section>
<!-- Hero Section End -->
