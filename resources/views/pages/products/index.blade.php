@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
    @inject('stockHelper', 'App\Helpers\StockStatusHelper')

    <div class="container">


        <!-- Product Section Begin -->
        <section class="product spad">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-5">
                        <div class="sidebar">
                            <!--<div class="sidebar__item">
                                <h4>Ár</h4>
                                <div class="price-range-wrap">
                                    <div class="price-range ui-slider ui-corner-all ui-slider-horizontal ui-widget ui-widget-content"
                                         data-min="{{ $minPrice ?? 0 }}" data-max="{{ $maxPrice ?? 9999999 }}">
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
                            </div>-->
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
                                        <label for="tag_{{ $id }}" id="tag_label_{{ $id }}" class="tag-label">
                                            {{ $label }}
                                            <input type="radio" id="tag_{{ $id }}" name="tag_id" value="{{ $id }}" class="tag-filter">
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
                                                        $mainPhoto = $product->photos->firstWhere('is_main', true);
                                                    @endphp

                                                    <a href="{{ route('products.resolve', ['slugs' => $fullSlug]) }}" class="latest-product__item">
                                                        <div class="latest-product__item__pic">
                                                            <img src="{{ asset('storage/' . $mainPhoto?->path ?? 'static_media/no-image.jpg') }}" alt="{{ $product->title }}">
                                                        </div>
                                                        <div class="latest-product__item__text">
                                                            <h6>{{ $product->title }}</h6>
                                                            @auth('customer')
                                                                <span>{{ number_format($product->display_gross_price, 0, ',', ' ') }} Ft</span>
                                                            @endauth
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
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div class="filter__found">
                                            <h6 class="mb-0"><span>{{ $product_count }}</span> termék</h6>
                                        </div>

                                        <div class="filter__sort d-flex align-items-center gap-2">
                                            <span class="fw-bold">Rendezés:</span>
                                            <select class="form-select form-select-sm" style="min-width: 220px;" id="sortBy" name="sortBy">
                                                <option value="productAsc" {{ request('sortBy') === 'productAsc' ? 'selected' : '' }}>
                                                    Terméknév szerint növekvő
                                                </option>
                                                <option value="productDesc" {{ request('sortBy') === 'productDesc' ? 'selected' : '' }}>
                                                    Terméknév szerint csökkenő
                                                </option>
                                                <option value="priceAsc" {{ request('sortBy') === 'priceAsc' ? 'selected' : '' }}>
                                                    Ár szerint növekvő
                                                </option>
                                                <option value="priceDesc" {{ request('sortBy') === 'priceDesc' ? 'selected' : '' }}>
                                                    Ár szerint csökkenő
                                                </option>
                                            </select>

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            @forelse($products as $product)
                                @php
                                    $status = $stockHelper::resolve($product->stock);
                                    $mainPhoto = $product->photos->firstWhere('is_main', true);
                                @endphp
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <div class="product__item">
                                        <div class="product__item__pic set-bg" data-setbg="{{ asset('storage/' . $mainPhoto?->path ?? 'static_media/no-image.jpg') }}">
                                            @auth('customer')
                                                <ul class="product__item__pic__hover">
                                                    <!--<li><a href="#"><i class="fa fa-heart"></i></a></li>-->
                                                    @if($status['slug'] === 'in_stock')
                                                        <li><a href="#" onclick="addToCart({{ $product->id }})"><i class="fa fa-shopping-cart"></i></a></li>
                                                    @endif
                                                </ul>
                                            @endauth
                                        </div>
                                        <div class="product__item__text">
                                            @php
                                                $fullSlug = $product->category->getFullSlug() . '/' . $product->slug;
                                                $email = $basicdata['support_email'];

                                                $subject = rawurlencode('Érdeklődés: ' . $product->title);

                                                $bodyText = "Tisztelt Ügyfélszolgálat,\n\nSzeretnék érdeklődni a következő termékről:\n" .
                                                            "Név: {$product->title}\n" .
                                                            "Azonosító: {$product->id}";

                                                $body = rawurlencode($bodyText);

                                                $mailto = "mailto:{$email}?subject={$subject}&body={$body}";
                                            @endphp

                                            <h6><a href="{{ route('products.resolve', ['slugs' => $fullSlug]) }}">{{ $product->title }}</a></h6>
                                            @auth('customer')
                                                <h5>{{ number_format($product->display_gross_price, 0, ',', ' ') }} Ft</h5>
                                                @if ($status)
                                                    @if ("backorder" === $status['slug'])
                                                        <a class="badge bg-{{ $status['color'] }}" href="{{ $mailto }}" class="btn btn-sm btn-outline-primary mt-2">
                                                            {{ $status['name'] }} <i class="fa fa-envelope"></i>
                                                        </a>
                                                    @else
                                                        <span class="badge bg-{{ $status['color'] }}">
                                                            {{ $status['name'] }}
                                                        </span>
                                                    @endif

                                                @endif
                                            @endauth
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
