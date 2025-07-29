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
    @inject('stockHelper', 'App\Helpers\StockStatusHelper')



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
                            {!! $product->display_all_prices !!}
                        @endauth

                        @php
                            $description = strip_tags($product->description);
                            $status = $stockHelper::resolve($product->stock);

                            // Ha a szöveg 500 karakternél hosszabb
                            if (strlen($description) > 500) {
                                $cutPosition = strrpos(substr($description, 0, 500), ' ');
                                $shortDescription = substr($description, 0, $cutPosition) . '...';
                            } else {
                                $shortDescription = $description;
                            }
                        @endphp

                        <p class="mt-5">
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
                                @if($status['slug'] === 'in_stock')
                                    <div class="product__details__quantity">
                                        <div class="quantity">
                                            <div class="pro-qty">
                                                <input type="text" value="1" min="1">
                                            </div>
                                        </div>
                                    </div>

                                    <a onclick="addToCart({{ $product->id }})" href="#" class="primary-btn">Kosárba</a>
                                @endif
                            <!--<a href="#" class="heart-icon"><span class="icon_heart_alt"></span></a>-->
                        @else
                            <a href="{{ route('login') }}" class="primary-btn mt-3">Jelentkezz be a vásárláshoz</a>
                        @endauth

                        @php
                            $fullSlug = $product->category->getFullSlug() . '/' . $product->slug;
                            $email = $basicdata['support_email'];

                            $interesting_subject = rawurlencode('Érdeklődés: ' . $product->title);

                            $interesting_bodyText = "Tisztelt Ügyfélszolgálat,\n\nSzeretnék érdeklődni a következő termékről:\n" .
                                        "Név: {$product->title}\n" .
                                        "Azonosító: {$product->id}";

                            $interesting_body = rawurlencode($interesting_bodyText);

                            $interesting_mailto = "mailto:{$email}?subject={$interesting_subject}&body={$interesting_body}";

                            $offer_subject = rawurlencode('Ajánlatkérés beszereléssel: ' . $product->title);
                            $offer_bodyText = "Tisztelt Ügyfélszolgálat,\n\nSzeretnék ajánlatot kérni a következő termékről beszereléssel együtt:\n" .
                                        "Név: {$product->title}\n" .
                                        "Azonosító: {$product->id}";
                            $offer_body = rawurlencode($offer_bodyText);
                            $offer_mailto = "mailto:{$email}?subject={$offer_subject}&body={$offer_body}";
                        @endphp

                        <a href="" class="btn btn-outline-primary mt-2 interesting-badge" product-id="{{ $product->id }}" interesting_type="install-interesting" product-title="{{ $product->title }}">
                            Ajánlatot szeretnék beszereléssel együtt <i class="fa fa-envelope"></i>
                        </a>

                        @auth('customer')
                                @if ("backorder" === $status['slug'])
                                    <ul>
                                        <li><b>Készlet</b>
                                            <a href="" class="btn btn-sm btn-outline-primary mt-2 interesting-badge" interesting_type="product-interesting" product-id="{{ $product->id }}" product-title="{{ $product->title }}">
                                                {{ $status['name'] }} <i class="fa fa-envelope"></i>
                                            </a>
                                        </li>
                                    </ul>
                                @else
                                    <ul>
                                        <li><b>Készlet</b>
                                            <span class="badge bg-{{ $status['color'] }}">
                                                {{ $status['name'] }}
                                            </span>
                                        </li>
                                    </ul>
                                @endif
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
                                {!! $product->display_all_prices !!}
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>
    <!-- Related Product Section End -->

    <!-- Modális ablak -->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="modalForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Ajánlatot szeretnék beépítéssel együtt</h5>
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
                        <button type="submit" class="btn btn-primary" id="sendInteresting" interesting_type="">Küldés</button>
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
                let interesting_type = $(this).attr('interesting_type');
                if (interesting_type == "install-interesting") {
                    $('#modalLabel').text('Ajánlatot szeretnék beépítéssel együtt');
                }
                if (interesting_type == "product-interesting") {
                    $('#modalLabel').text('Termék érdeklődés elküldése');
                }
                $('#interesting_product').text($(this).attr('product-title'));
                $('#sendInteresting').attr('interesting_type', $(this).attr('interesting_type'));
                $('#modal').modal('show');
            });

            $('#sendInteresting').click(function(event) {

                event.preventDefault();

                fetch(window.appConfig.APP_URL + 'email/send', {
                    method: 'POST',
                    body: JSON.stringify({
                        email_type: $(this).attr('interesting_type'),
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


