<!-- Categories Section Begin -->
<section class="categories">
    <div class="container">
        <div class="row">
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
</section>
<!-- Categories Section End -->
