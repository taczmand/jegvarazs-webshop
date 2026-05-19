@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @inject('qdService', 'App\Services\Pricing\QuantityDiscountService')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Kosár',
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
                    @if($cart && $cart->items && 0 === $cart->items->count())
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
                                    <th>Nettó egységár</th>
                                    <th>Bruttó egységár</th>
                                    <th>Mennyiség</th>
                                    <th>Összesen</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $total_item_amount = 0;
                                @endphp
                                    @if($cart && $cart->items && $cart->items->count() > 0)

                                        @foreach($cart->items as $item)
                                            @php
                                                $subtotal = $item->discounted_row_gross_total ?? 0;
                                                $total_item_amount += $subtotal;

                                                $baseUnitGross = $item->product ? (float) $item->product->display_gross_price : null;
                                                $vatValue = $item->product ? (float) ($item->product->taxCategory?->tax_value ?? 0) : 0;
                                                $baseUnitNet = $baseUnitGross !== null
                                                    ? round((float) ($baseUnitGross / (1 + $vatValue / 100)), 2)
                                                    : null;

                                                $now = now();
                                                $rules = ($item->product?->quantityDiscounts ?? collect())
                                                    ->filter(fn ($d) => (bool) $d->is_active)
                                                    ->filter(fn ($d) => !$d->starts_at || $d->starts_at->lte($now))
                                                    ->filter(fn ($d) => !$d->ends_at || $d->ends_at->gte($now))
                                                    ->sortBy('min_quantity')
                                                    ->values();

                                                $nextRule = $rules->first(fn ($r) => (int) $r->min_quantity > (int) $item->quantity);
                                                $nextQty = $nextRule ? (int) $nextRule->min_quantity : null;
                                                $needMore = $nextQty ? max(0, $nextQty - (int) $item->quantity) : null;
                                                $nextUnitGross = ($nextRule && $item->product)
                                                    ? $qdService->discountedUnitGrossPrice($item->product, $nextQty, (float) $item->product->display_gross_price)
                                                    : null;
                                                $nextRowTotal = ($nextUnitGross !== null && $nextQty !== null)
                                                    ? round($nextUnitGross * $nextQty, 2)
                                                    : null;
                                            @endphp
                                            <tr id="cart_item_{{ $item->id }}">
                                                <td class="shoping__cart__item">
                                                    <img src="img/cart/cart-1.jpg" alt="">
                                                    <h5>{{ $item->product->title }}</h5>
                                                </td>
                                                <td class="shoping__cart__price">
                                                    {{ number_format($item->discounted_unit_net_price ?? 0, 0, ',', ' ') }} Ft
                                                    @if($item->discount_applied && $baseUnitNet !== null)
                                                        <div class="small text-muted"><del>{{ number_format($baseUnitNet, 0, ',', ' ') }} Ft</del></div>
                                                    @endif
                                                </td>
                                                <td class="shoping__cart__price">
                                                    {{ number_format($item->discounted_unit_gross_price ?? 0, 0, ',', ' ') }} Ft
                                                    @if($item->discount_applied && $baseUnitGross !== null)
                                                        <div class="small text-muted"><del>{{ number_format($baseUnitGross, 0, ',', ' ') }} Ft</del></div>
                                                    @endif
                                                </td>
                                                <td class="shoping__cart__quantity">
                                                    <div class="quantity">
                                                        <div class="pro-qty" data-item-id="{{ $item->id }}" data-unit-qty="{{ $item->product->unit_qty }}">
                                                            <input type="text" value="{{ $item->quantity }}" min="{{ $item->product->unit_qty }}" class="quanity_input" item-id="{{ $item->id }}">
                                                        </div>
                                                        @if($item->product && $item->product->unit)
                                                            <div class="text-muted mt-1" style="font-size: 0.85rem;">
                                                                {{ $item->product->unit->abbreviation ?? $item->product->unit->name }}
                                                            </div>
                                                        @endif

                                                        @if($needMore !== null && $needMore > 0 && $nextRowTotal !== null && $nextUnitGross !== null)
                                                            <div class="text-primary mt-1" style="font-size: 0.85rem; line-height: 1.2;">
                                                                Ha még <strong>{{ $needMore }}</strong> db-ot veszel, akkor <strong>{{ $nextQty }}</strong> db esetén csak
                                                                <strong>{{ number_format($nextRowTotal, 0, ',', ' ') }} Ft</strong> ({{ number_format($nextUnitGross, 0, ',', ' ') }} Ft/db)
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="shoping__cart__total">
                                                    {{ number_format($item->discounted_row_gross_total ?? 0, 0, ',', ' ') }} Ft
                                                </td>
                                                <td class="shoping__cart__item__close">
                                                    <span class="icon_close" onclick="removeItemFromCart({{ $item->id }})"></span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            @if($cart && $cart->items && 0 < $cart->items->count())
                <div class="row">
                    <div class="col-lg-8">
                        <!--    <div class="shoping__continue">
                                <div class="shoping__discount">
                                    <h5>Kuponkód</h5>
                                    <form action="#">
                                        <input type="text" placeholder="Adja meg a kuponkódot">
                                        <button type="submit" class="site-btn">Beváltás</button>
                                    </form>
                                </div>
                            </div>-->
                        </div>
                    <div class="col-lg-4">
                        <div class="shoping__checkout">

                            <ul>
                                <!--<li>Részösszeg <span>{{ number_format($total_item_amount, 0, ',', ' ') }} Ft</span></li>-->
                                <!--<li>Kupon <span>- </span></li>-->
                                <li>Összesen bruttó<span>{{ number_format($total_item_amount, 0, ',', ' ') }} Ft</span></li>
                            </ul>
                            <a href="{{ route('checkout') }}" class="site-btn" style="width: 100%; text-align: center">Pénztár</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
    <!-- Shoping Cart Section End -->
@endsection
