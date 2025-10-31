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

                            @if(count($product_sub_categories) > 0)
                                @foreach($product_sub_categories as $subcategory)
                                    <div class="sidebar__item">

                                        @php

                                            $data = $subcategory->getFullSlugWithImage(); // slug + első kép
                                            $fullSlug = $data['slug'];
                                            $productWithImage = $data['product_with_image'];
                                        @endphp

                                        <div class="subcategory-item">
                                            <a href="{{ route('products.resolve', ['slugs' => $fullSlug]) }}" class="subcategory-link">
                                                <div class="subcategory-image">
                                                    <img src="{{ asset('storage/' . $productWithImage?->path ?? 'static_media/no-image.jpg') }}" alt="{{ $subcategory->title }}">
                                                </div>
                                                <span class="subcategory-title">{{ $subcategory->title }}</span>
                                            </a>
                                        </div>

                                    </div>
                                @endforeach
                            @endif


                        @if(count($tags) > 0)
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
                            @endif

                            @if(count($brands) > 0)
                                <div class="sidebar__item">
                                    <h4>Márkák</h4>


                                    @foreach($brands as $label => $id)
                                        <div class="sidebar__item__size">
                                            <label for="brand_{{ $id }}" id="brand_label_{{ $id }}" class="brand-label">
                                                {{ $label }}
                                                <input type="radio" id="brand_{{ $id }}" name="brand_id" value="{{ $id }}" class="brand-filter">
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($attributes->count() > 0)
                                @foreach($attributes as $name => $items)
                                    <div class="sidebar__item">
                                        <h4>{{ $name }}</h4>
                                        @foreach($items as $item)
                                            @php
                                                $attrKey = $item->id . ':' . $item->value;
                                            @endphp
                                            <div class="sidebar__item__size">
                                                <label class="attribute-label" data-attrkey="{{ $attrKey }}">
                                                    {{ $item->value }}
                                                    <input type="radio"
                                                           name="attribute_id"
                                                           class="attribute-filter"
                                                           data-id="{{ $item->id }}"
                                                           data-value="{{ $item->value }}">
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @endif



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
                                        <div class="filter__sort">
                                            <button class="site-btn" id="showFilters">Szűrés beállítása</button>
                                        </div>
                                        <div class="filter__found">
                                            <h6 class="mb-0"><span>{{ $product_count }}</span> találat</h6>
                                        </div>

                                        <div class="filter__found">
                                            <select class="form-control" style="min-width: 220px;" id="itemsPerPage" name="itemsPerPage">
                                                <option value="12" {{ request('itemsPerPage', 12) == 12 ? 'selected' : '' }}>12 találat / oldal</option>
                                                <option value="24" {{ request('itemsPerPage') == 24 ? 'selected' : '' }}>24 találat / oldal</option>
                                                <option value="36" {{ request('itemsPerPage') == 36 ? 'selected' : '' }}>36 találat / oldal</option>
                                                <option value="48" {{ request('itemsPerPage') == 48 ? 'selected' : '' }}>48 találat / oldal</option>
                                                <option value="60" {{ request('itemsPerPage') == 60 ? 'selected' : '' }}>60 találat / oldal</option>
                                            </select>
                                        </div>
                                        <div class="filter__sort d-flex align-items-center gap-2">
                                            <select class="form-control" style="min-width: 220px;" id="sortBy" name="sortBy">
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

                                    $fullSlug = $product->category->getFullSlug() . '/' . $product->slug;
                                    $email = $basicdata['support_email'];

                                    $subject = rawurlencode('Érdeklődés: ' . $product->title);

                                    $bodyText = "Tisztelt Ügyfélszolgálat,\n\nSzeretnék érdeklődni a következő termékről:\n" .
                                                "Név: {$product->title}\n" .
                                                "Azonosító: {$product->id}";

                                    $body = rawurlencode($bodyText);

                                    $mailto = "mailto:{$email}?subject={$subject}&body={$body}";
                                @endphp
                                <div class="col-6 col-sm-6 col-md-6 col-lg-4">

                                        <div class="product__item" onclick="window.location.href='{{ route('products.resolve', ['slugs' => $fullSlug]) }}'">

                                                <div class="product__item__pic set-bg" data-setbg="{{ asset('storage/' . $mainPhoto?->path ?? 'static_media/no-image.jpg') }}">
                                                    @auth('customer')
                                                        <ul class="product__item__pic__hover">
                                                            <!--<li><a href="#"><i class="fa fa-heart"></i></a></li>-->
                                                            @if($status['slug'] === 'in_stock')
                                                                <li><a href="#" onclick="event.stopPropagation(); addToCart({{ $product->id }}, {{ $product->unit_qty }}); return false;"><i class="fa fa-shopping-cart"></i></a></li>
                                                            @endif
                                                        </ul>
                                                    @endauth
                                                </div>
                                            <a href="{{ route('products.resolve', ['slugs' => $fullSlug]) }}">
                                                <div class="product__item__text">


                                                    <h6><a href="{{ route('products.resolve', ['slugs' => $fullSlug]) }}">{{ $product->title }}</a></h6>
                                                    @auth('customer')
                                                        <!--<h5>{{ number_format($product->display_gross_price, 0, ',', ' ') }} Ft</h5>-->
                                                        {!! $product->display_all_prices_on_list !!}
                                                        @if ($status)
                                                            @if ("backorder" === $status['slug'])
                                                                <a class="badge bg-{{ $status['color'] }} interesting-badge" href="#" product-id="{{ $product->id }}" product-title="{{ $product->title }}" >
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
                                            </a>
                                        </div>

                                </div>
                            @empty
                                <p>Nincs elérhető termék.</p>
                            @endforelse

                        </div>
                        <!--<div class="product__pagination">-->
                            {{ $products->onEachSide(5)->links() }}
                        <!--</div>-->
                    </div>
                </div>
            </div>
        </section>
        <!-- Product Section End -->


    </div>

    <!-- Modális ablak -->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="modalForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Termék érdeklődés elküldése</h5>
                        <button type="button" class="close cancelModalButton" data-dismiss="modal" aria-label="Bezárás">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            @php
                                $customer = auth('customer')->user();
                            @endphp
                            @if ($customer)
                                <label class="form-label">Név</label>
                                <p>{{ $customer->last_name }} {{ $customer->first_name }}</p>
                                <label class="form-label">E-mail cím</label>
                                <p>{{ $customer->email }}</p>
                                <label for="" class="form-label">Termék</label>
                                <p id="interesting_product"></p>
                            @endif

                        </div>
                        <div class="mb-3">
                            <label for="interesting_message" class="form-label">Üzenet</label>
                            <textarea class="form-control" id="interesting_message" name="interesting_message"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="sendInteresting">Küldés</button>
                        <button type="button" class="btn btn-secondary cancelModalButton" data-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $('.interesting-badge').click(function(event) {
                event.preventDefault();
                $('#interesting_product').text($(this).attr('product-title'));

                $('#modal').modal('show');
            });

            $('#sendInteresting').click(function(event) {

                event.preventDefault();
                fetch(window.appConfig.APP_URL + 'email/send', {
                    method: 'POST',
                    body: JSON.stringify({
                        email_type: 'product-interesting',
                        productID: $('.interesting-badge').attr('product-id'),
                        contact_message: $('#interesting_message').val()
                    }),

                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Hiba történt a beküldés során.');
                        }
                        return response.json(); // vagy .text() ha nem JSON-t vársz vissza
                    })
                    .then(data => {
                        if (data.result !== 'success') {
                            throw new Error(data.error_message || 'Ismeretlen hiba történt.');
                        }
                        showToast(data.message, 'success');
                        $('#interesting_message').val("");
                        $('#modal').modal('hide');
                    })
                    .catch(error => {
                        showToast(error || 'Ismeretlen hiba történt.', 'error');
                    });
            });

            $('.cancelModalButton').click(function () {
                $('#modal').modal('hide'); // Itt "#modal" a modál ablak ID-je
            });


        });
    </script>
@endsection
