<!-- Categories Section Begin -->
<section class="categories">
    <div class="container">
        <div class="row">
            <div class="categories__slider owl-carousel">
                @foreach($categories as $category)
                    @php
                        $photo = $category->products->first()?->photos->firstWhere('is_main', true)?->path ??
                                 $category->products->first()?->photos->first()?->path ??
                                 'images/no-image.jpg';

                        $photoUrl = asset('storage/' . $photo);
                    @endphp
                    <div class="col-lg-3">
                        <div class="categories__item set-bg" data-setbg="{{ $photoUrl }}">
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
