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
                    <ul class="category-menu list-unstyled">
                        @foreach ($categories as $category)
                            <li class="category-item">
                                <a href="{{ route('products.resolve', ['slugs' => $category->getFullSlug()]) }}" class="category-link">
                                    <div class="category-header">
                                        {{ $category->title }}
                                        @if($category->children->count())
                                            <button class="subcategory-toggle" aria-label="Almenü megnyitása">
                                                <i class="fa fa-chevron-down"></i>
                                            </button>
                                        @endif
                                    </div>
                                </a>

                                @if($category->children->count())
                                    <div class="subcategory-container">
                                        <div class="subcategory-grid">
                                            @foreach ($category->children as $sub)
                                                <div class="subcategory-item">
                                                    <a href="{{ route('products.resolve', ['slugs' => $sub->getFullSlug()]) }}" class="subcategory-link level-1">
                                                        {{ $sub->title }}
                                                    </a>

                                                    @if($sub->children->count())
                                                        <div class="subcategory-sublist">
                                                            @foreach ($sub->children as $child)
                                                                <a href="{{ route('products.resolve', ['slugs' => $child->getFullSlug()]) }}" class="subcategory-link level-2">
                                                                    {{ $child->title }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </li>
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
            </div>
        </div>
    </div>
</section>
<!-- Hero Section End -->
