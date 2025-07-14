@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection

@section('content')
    @php
        $category = $product->category ?? $product->categories->first();
        $categoryTrail = [];

        while ($category) {
            $categoryTrail[] = [
                'title' => $category->title,
                'url' => route('products.resolve', ['slugs' => $category->getFullSlug()])
            ];
            $category = $category->parent;
        }

        $breadcrumbs = [
            'page_title' => $product->title,
            'nav' => array_merge([
                ['title' => 'Főoldal', 'url' => route('index')],
                ['title' => 'Termékek', 'url' => route('products.index')],
            ], array_reverse($categoryTrail)),
        ];
    @endphp

    @include('partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])



    <!-- Product Details Section Begin -->
    <section class="product-details spad">
        <div class="container">
            <div class="row">
                <!-- Product Image -->
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__pic">
                        @php
                            $mainPhoto = $product->photos->firstWhere('is_main', true);
                        @endphp
                        <div class="product__details__pic__item">
                            @if($mainPhoto)
                                <img class="product__details__pic__item--large" src="{{ asset('storage/' . $mainPhoto->path) }}" alt="{{ $mainPhoto->alt ?? $product->title }}">
                            @else
                                <img class="product__details__pic__item--large" src="{{ asset('static_media/no-image.jpg') }}" alt="{{ $product->title }}">
                            @endif
                        </div>
                        <div class="product__details__pic__slider owl-carousel">
                            @foreach($product->photos as $image)
                                <img
                                    data-imgbigurl="{{ asset('storage/' . $image->path) }}"
                                    src="{{ asset('storage/' . $image->path) }}"
                                    alt="{{ $image->alt ?? $product->title }}">
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__text">
                        @if($product->tags->isNotEmpty())
                            <div class="mb-3">
                                @foreach($product->tags as $tag)
                                    <span class="badge bg-success p-2 text-dark">#{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        @endif
                        <h3>{{ $product->title }}</h3>
                        @auth('customer')
                            <div class="product__details__price">{{ number_format($product->gross_price, 0, ',', ' ') }} Ft</div>
                        @endauth

                        @php
                            $description = strip_tags($product->description);

                            // Ha a szöveg 500 karakternél hosszabb
                            if (strlen($description) > 500) {
                                $cutPosition = strrpos(substr($description, 0, 500), ' ');
                                $shortDescription = substr($description, 0, $cutPosition) . '...';
                            } else {
                                $shortDescription = $description;
                            }
                        @endphp

                        <p>
                            {!! $shortDescription !!}
                            <a href="#tabs-1">Tovább olvasom</a>
                        </p>

                        @if($product->attributes->isNotEmpty())
                            <ul>
                                @foreach($product->attributes as $attribute)
                                    <li><span>{{ $attribute->name }}:</span> {{ $attribute->pivot->value }}</li>
                                @endforeach
                            </ul>
                        @endif


                        @auth('customer')
                            <div class="product__details__quantity">
                                <div class="quantity">
                                    <div class="pro-qty">
                                        <input type="text" value="1" min="1">
                                    </div>
                                </div>
                            </div>
                            <a onclick="addToCart({{ $product->id }})" href="#" class="primary-btn">Kosárba</a>
                            <!--<a href="#" class="heart-icon"><span class="icon_heart_alt"></span></a>-->
                        @else
                            <a href="{{ route('login') }}" class="primary-btn mt-3">Jelentkezz be a vásárláshoz</a>
                        @endauth
                        @auth('customer')
                        <ul>
                            <li><b>Készlet</b> <span>{{ $product->in_stock ? 'In Stock' : 'Out of Stock' }}</span></li>
                        </ul>
                        @endauth
                    </div>
                </div>

                <!-- Tabs Section -->
                <div class="col-lg-12">
                    <div class="product__details__tab">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">Leírás</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tabs-1" role="tabpanel">
                                <div class="product__details__tab__desc">
                                    <h6>Termék leírás</h6>
                                    <p>{!! $product->description !!}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Product Details Section End -->


    <!-- Related Product Section Begin -->
    <section class="related-product">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title related__product__title">
                        <h2>Hasonló termékek</h2>
                    </div>
                </div>
            </div>
            <div class="row">

                <!-- Loop through related products -->
                @foreach($relatedProducts as $related_product)
                    @php
                        $fullSlug = $related_product->category->getFullSlug() . '/' . $related_product->slug;
                        $mainPhoto = $related_product->photos->firstWhere('is_main', true);
                    @endphp
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="product__item">
                            <div class="product__item__pic set-bg" data-setbg="{{ asset('storage/' . $mainPhoto?->path ?? 'static_media/no-image.jpg') }}">
                                <ul class="product__item__pic__hover">
                                    <!--<li><a href="#"><i class="fa fa-heart"></i></a></li>
                                    <li><a href="#"><i class="fa fa-retweet"></i></a></li>-->
                                    @auth('customer')
                                        <li><a href="#" onclick="addToCart({{ $related_product->id }})"><i class="fa fa-shopping-cart"></i></a></li>
                                    @endauth
                                </ul>
                            </div>
                            <div class="product__item__text">
                                <h6><a href="{{ route('products.resolve', ['slugs' => $fullSlug]) }}">{{ $related_product->title }}</a></h6>
                                <h5>{{ number_format($related_product->gross_price, 0, ',', ' ') }} Ft</h5>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>
    <!-- Related Product Section End -->

@endsection


