@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Rólunk',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Kosár', 'url' => route('cart')]
        ],
    ]
    ])
    <section class="shoping-cart spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    @if(0 === $cart->items->count())
                        <div class="shoping__cart__item">
                            <h5>A kosár üres</h5>
                            <p>Vásároljon termékeket, hogy megtöltse a kosarát!</p>
                            <a href="{{ route('index') }}" class="primary-btn">Vásárlás</a>
                        </div>
                    @else
                        <div class="shoping__cart__table">
                            <table>
                                <thead>
                                <tr>
                                    <th class="shoping__product">Termékek</th>
                                    <th>Ár</th>
                                    <th>Mennyiség</th>
                                    <th>Összesen</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $total_item_amount = 0;
                                @endphp

                                    @foreach($cart->items as $item)
                                        @php
                                            $subtotal = $item->product->price * $item->quantity;
                                            $total_item_amount += $subtotal;
                                        @endphp
                                        <tr id="cart_item_{{ $item->id }}">
                                            <td class="shoping__cart__item">
                                                <img src="img/cart/cart-1.jpg" alt="">
                                                <h5>{{ $item->product->title }}</h5>
                                            </td>
                                            <td class="shoping__cart__price">
                                                {{ number_format($item->product->price, 0, ',', ' ') }} Ft
                                            </td>
                                            <td class="shoping__cart__quantity">
                                                <div class="quantity">
                                                    <div class="pro-qty" data-item-id="{{ $item->id }}">
                                                        <input type="text" value="{{ $item->quantity }}" min="1" onblur="changeQuantity({{ $item->id }}, this.value)">
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="shoping__cart__total">
                                                {{ number_format($item->product->price * $item->quantity, 0, ',', ' ') }} Ft
                                            </td>
                                            <td class="shoping__cart__item__close">
                                                <span class="icon_close" onclick="removeItemFromCart({{ $item->id }})"></span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            @if(0 < $cart->items->count())
                <div class="row">
                    <div class="col-lg-6">
                        <div class="shoping__continue">
                            <div class="shoping__discount">
                                <h5>Kuponkód</h5>
                                <form action="#">
                                    <input type="text" placeholder="Adja meg a kuponkódot">
                                    <button type="submit" class="site-btn">Beváltás</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="shoping__checkout">
                            <h5>Összesítő</h5>
                            <ul>
                                <li>Részösszeg <span>{{ number_format($total_item_amount, 0, ',', ' ') }} Ft</span></li>
                                <li>Kupon <span>- todo</span></li>
                                <li>Összesen <span>{{ number_format($total_item_amount, 0, ',', ' ') }} Ft</span></li>
                            </ul>
                            <a href="#" class="primary-btn">Tovább</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
    <!-- Shoping Cart Section End -->
@endsection
