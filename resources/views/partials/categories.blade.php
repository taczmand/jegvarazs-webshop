<!-- Categories Section Begin -->
<section class="categories">
    <div class="container">
        <div class="row">
            <div class="categories__slider owl-carousel">
                @foreach($all_categories as $category)
                    @php
                        $photo = 'static_media/no-image.jpg';

                        $firstProductWithPhoto = $category->products()
                            ->whereHas('photos') // csak olyan termék, aminek van fotója
                            ->orderBy('id', 'asc')
                            ->first();

                        if ($firstProductWithPhoto) {
                            $mainPhoto = $firstProductWithPhoto->photos()
                                ->orderBy('id', 'asc')
                                ->first();

                            if ($mainPhoto && !empty($mainPhoto->path)) {
                                $photo = 'storage/' . ltrim($mainPhoto->path, '/');
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
</section>
<!-- Categories Section End -->
