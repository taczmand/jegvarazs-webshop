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
                            <li class="category-item">
                                <a href="{{ route('products.resolve', ['slugs' => $category->getFullSlug()]) }}">
                                    {{ $category->title }}
                                </a>

                                @if($category->children->count() > 0)
                                    <ul class="subcategory-list">
                                        @foreach($category->children as $subcategory)
                                            <li>
                                                <a href="{{ route('products.resolve', ['slugs' => $subcategory->getFullSlug()]) }}">
                                                    {{ $subcategory->title }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach

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
