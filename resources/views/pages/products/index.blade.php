@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Termékek',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')]
        ],
    ]
    ])

    <div class="container">


        <!-- Product Section Begin -->
        <section class="product spad">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-5">
                        <div class="sidebar">
                            <div class="sidebar__item">
                                @isset($category)
                                    <h4>{{ $category->title }}</h4>
                                @else
                                    <h4>Kategóriák</h4>
                                @endisset
                                @isset($category)
                                    @if($category->children->isNotEmpty())
                                        <ul>
                                            @foreach($category->children as $child)
                                                <li>
                                                    <a href="{{ route('products.resolve', ['slugs' => $child->getFullSlug()]) }}">
                                                        {{ $child->title }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @endisset
                            </div>
                            <div class="sidebar__item">
                                <h4>Ár</h4>
                                <div class="price-range-wrap">
                                    <div class="price-range ui-slider ui-corner-all ui-slider-horizontal ui-widget ui-widget-content"
                                         data-min="{{ $minPrice ?? 0 }}" data-max="{{ $maxPrice ?? 999999999 }}">
                                        <div class="ui-slider-range ui-corner-all ui-widget-header"></div>
                                        <span tabindex="0" class="ui-slider-handle ui-corner-all ui-state-default"></span>
                                        <span tabindex="0" class="ui-slider-handle ui-corner-all ui-state-default"></span>
                                    </div>
                                    <div class="range-slider">
                                        <div class="price-input">
                                            <input type="text" id="minamount">
                                            <input type="text" id="maxamount">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--<div class="sidebar__item sidebar__item__color--option">
                                <h4>Colors</h4>
                                <div class="sidebar__item__color sidebar__item__color--white">
                                    <label for="white">
                                        White
                                        <input type="radio" id="white">
                                    </label>
                                </div>
                                <div class="sidebar__item__color sidebar__item__color--gray">
                                    <label for="gray">
                                        Gray
                                        <input type="radio" id="gray">
                                    </label>
                                </div>
                                <div class="sidebar__item__color sidebar__item__color--red">
                                    <label for="red">
                                        Red
                                        <input type="radio" id="red">
                                    </label>
                                </div>
                                <div class="sidebar__item__color sidebar__item__color--black">
                                    <label for="black">
                                        Black
                                        <input type="radio" id="black">
                                    </label>
                                </div>
                                <div class="sidebar__item__color sidebar__item__color--blue">
                                    <label for="blue">
                                        Blue
                                        <input type="radio" id="blue">
                                    </label>
                                </div>
                                <div class="sidebar__item__color sidebar__item__color--green">
                                    <label for="green">
                                        Green
                                        <input type="radio" id="green">
                                    </label>
                                </div>
                            </div>-->
                            <div class="sidebar__item">
                                <h4>Címkék</h4>


                                @foreach($tags as $label => $id)
                                    <div class="sidebar__item__size">
                                        <label for="tag_{{ $id }}">
                                            {{ $label }}
                                            <input type="radio" id="tag_{{ $id }}" name="tag_id" value="{{ $id }}">
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="sidebar__item">
                                <div class="latest-product__text">
                                    <h4>Legújabb termékek</h4>
                                    <div class="latest-product__slider owl-carousel">
                                        @foreach($latest_products->chunk(3) as $chunk)
                                            <div class="latest-prdouct__slider__item">
                                                @foreach($chunk as $product)

                                                    @php
                                                        $fullSlug = $product->category->getFullSlug() . '/' . $product->slug;
                                                    @endphp

                                                    <a href="{{ route('products.resolve', ['slugs' => $fullSlug]) }}" class="latest-product__item">
                                                        <div class="latest-product__item__pic">
                                                            <img src="{{ asset($product->image_path ?? 'images/no-image.jpg') }}" alt="{{ $product->title }}">
                                                        </div>
                                                        <div class="latest-product__item__text">
                                                            <h6>{{ $product->name }}</h6>
                                                            <span>{{ number_format($product->price, 0, ',', ' ') }} Ft</span>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endforeach

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Product Section Begin -->
                    <div class="col-lg-9 col-md-7">

                        <div class="filter__item">
                            <div class="row">
                                <div class="col-lg-4 col-md-5">
                                    <div class="filter__sort">
                                        <span>Sort By</span>
                                        <select>
                                            <option value="0">Default</option>
                                            <option value="0">Default</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4">
                                    <div class="filter__found">
                                        <h6><span>16</span> termék</h6>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-3">
                                    <div class="filter__option">
                                        <span class="icon_grid-2x2"></span>
                                        <span class="icon_ul"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @forelse($products as $product)
                                @php
                                    $mainPhoto = $product->photos->firstWhere('is_main', true);
                                @endphp
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <div class="product__item">
                                        <div class="product__item__pic set-bg" data-setbg="{{ asset($mainPhoto?->path ?? 'images/no-image.jpg') }}">
                                            <ul class="product__item__pic__hover">
                                                <li><a href="#"><i class="fa fa-heart"></i></a></li>
                                                <li><a href="#"><i class="fa fa-retweet"></i></a></li>
                                                <li><a href="#"><i class="fa fa-shopping-cart"></i></a></li>
                                            </ul>
                                        </div>
                                        <div class="product__item__text">
                                            @php
                                                $fullSlug = $product->category->getFullSlug() . '/' . $product->slug;
                                            @endphp
                                            <h6><a href="{{ route('products.resolve', ['slugs' => $fullSlug]) }}">{{ $product->title }}</a></h6>
                                            <h5>{{ number_format($product->price, 0, ',', ' ') }} Ft</h5>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p>Nincs elérhető termék.</p>
                            @endforelse

                        </div>
                        <div class="product__pagination_">
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Product Section End -->


    </div>
@endsection
