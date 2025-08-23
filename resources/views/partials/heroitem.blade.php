<div class="hero__item set-bg" data-setbg="{{ asset('storage/' . ($basicmedia['hero_image'] ?? 'static_media/no-image.jpg')) }}">
    <div class="hero__text">
        <span>{!! $basicdata['hero_top_title'] ?? '' !!} </span>
        <h2>{!! $basicdata['hero_main_title'] ?? '' !!}</h2>
        <p>{!! $basicdata['hero_subtitle'] ?? '' !!}</p>
        @guest('customer')
          <a href="{{ route('registration') }}" class="cta-btn">Regisztráció</a>
        @endguest

    </div>
</div>
