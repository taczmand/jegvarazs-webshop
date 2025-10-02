<div class="hero__item set-hero-bg" data-setbg="{{ asset('storage/' . ($basicmedia['hero_image'] ?? 'static_media/no-image.jpg')) }}">
    <div class="hero__text">
        <span>{!! $basicdata['hero_top_title'] ?? '' !!} </span>
        <h2>{!! $basicdata['hero_main_title'] ?? '' !!}</h2>
        <p>{!! $basicdata['hero_subtitle'] ?? '' !!}</p>
        @guest('customer')
          <a href="{{ route('registration') }}" class="cta-btn">Regisztráció</a>
        @endguest

    </div>
</div>
<div class="row mt-3" style="width:95%; padding-left: 5%">
    <div class="categories__slider owl-carousel">
        @foreach($all_categories as $category)
            @php
                $firstProduct = $category->products->first();
                $photo = 'static_media/no-image.jpg';

                if ($firstProduct) {
                    $mainPhoto = $firstProduct->photos->first();
                    if ($mainPhoto) {
                        $photo = 'storage/' . $mainPhoto->path;
                    }
                }
            @endphp

            <div class="col-lg-3 pr-3">
                <div class="categories__item set-bg" data-setbg="{{ asset($photo) }}">
                    <h5>
                        <a href="{{ route('products.resolve', ['slugs' => $category->getFullSlug()]) }}">
                            {{ $category->title }}
                        </a>
                    </h5>
                </div>
            </div>
        @endforeach
    </div>
</div>
