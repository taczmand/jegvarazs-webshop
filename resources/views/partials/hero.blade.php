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
                    <ul class="categories-list">
                        @foreach($categories as $category)
                            @include('partials.category-item', ['category' => $category])
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="hero__search">
                    <div class="hero__search__form">
                        <form action="{{ route('search') }}" method="GET">
                            <input type="text" placeholder="Kereséshez gépeljen ide..." name="query" value="{{ request()->input('query') }}">
                            <button type="submit" class="site-btn">Keresés</button>
                        </form>
                    </div>
                    <div class="hero__search__phone">
                        <div class="hero__search__phone__icon">
                            <i class="fa fa-phone"></i>
                        </div>
                        <div class="hero__search__phone__text">
                            <h5><a href="tel:{{ $basicdata['support_phone'] ?? '' }}" class="text-dark">{{ $basicdata['support_phone'] ?? '' }}</a></h5>
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
