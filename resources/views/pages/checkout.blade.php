@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Pénztár',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Pénztár', 'url' => route('checkout')]
        ],
    ]
    ])

    @php
        $customer = auth('customer')->user();
    @endphp

    <!-- Checkout Section Begin -->
    <section class="checkout spad">
        <div class="container">
            <div class="checkout__form">
                <h4>Kapcsolattartó adatok</h4>
                <form action="{{ route('order.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-8 col-md-6">

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="checkout__input">
                                        <p>Vezetéknév<span>*</span></p>
                                        <input type="text" name="customer_last_name" value="{{ $customer->last_name }}" required>
                                        @error('customer_last_name')
                                            <div class="text-red-600 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="checkout__input">
                                        <p>Keresztnév<span>*</span></p>
                                        <input type="text" name="customer_first_name" value="{{ $customer->first_name }}" required>
                                        @error('customer_first_name')
                                            <div class="text-red-600 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="checkout__input">
                                        <p>Email cím<span>*</span></p>
                                        <input type="text" name="customer_email" value="{{ $customer->email }}" required>
                                        @error('customer_email')
                                            <div class="text-red-600 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="checkout__input">
                                        <p>Telefonszám<span>*</span></p>
                                        <input type="text" name="customer_phone" value="{{ $customer->phone }}" required>
                                        @error('customer_phone')
                                            <div class="text-red-600 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Számlázási adatok -->

                            <h4 class="mt-5 d-flex justify-content-between align-items-center toggle-header" data-bs-toggle="collapse" data-bs-target="#billingData" aria-expanded="true" aria-controls="billingData" style="cursor: pointer;">
                                Számlázási adatok
                                <span class="toggle-arrow">▼</span>
                            </h4>

                            <div class="collapse" id="billingData">

                                <div class="row">
                                    <div class="col-lg-12">

                                        <div class="checkout__input__checkbox">
                                            <label for="use_existing_billing">
                                                Az alábbi címet szeretném használni számlázási címként
                                                <input type="radio" id="use_existing_billing" name="billing_choice" value="exist" checked>
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>

                                        <select class="form-control w-100" name="selected_billing_address">
                                            @foreach ($billing_addresses as $billing_address)
                                                <option value="{{ $billing_address->id }}">
                                                    {{ $billing_address->name }} ({{ $billing_address->country }} - {{ $billing_address->postal_code }} {{ $billing_address->city }}, {{ $billing_address->address_line }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-lg-12">
                                        <div class="checkout__input__checkbox">
                                            <label for="new_billing_address">
                                                Új számlázási címet szeretnék megadni
                                                <input type="radio" id="new_billing_address" name="billing_choice" value="new">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-none" id="new_billing_address_fields">

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="checkout__input">
                                                <p>Név<span>*</span></p>
                                                <input type="text" name="billing_name" value="{{ old('billing_name') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="checkout__input">
                                                <p>Cég esetén adószám</p>
                                                <input type="text" name="billing_tax_number" value="{{ old('billing_tax_number') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="checkout__input">
                                                <p>Ország<span>*</span></p>
                                                <select name="billing_country" class="form-control w-100">
                                                    @foreach(config('countries') as $code => $name)
                                                        <option value="{{ $code }}" {{ old('billing_country') === $code ? 'selected' : '' }}>{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="checkout__input">
                                                <p>Irányítószám<span>*</span></p>
                                                <input type="text" name="billing_postal_code" value="{{ old('billing_postal_code') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="checkout__input">
                                                <p>Város<span>*</span></p>
                                                <input type="text" name="billing_city" value="{{ old('billing_city') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="checkout__input">
                                                <p>Cím<span>*</span></p>
                                                <input type="text" name="billing_address" value="{{ old('billing_address') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Szállítási adatok -->

                            <h4 class="mt-5 d-flex justify-content-between align-items-center toggle-header" data-bs-toggle="collapse" data-bs-target="#shippingData" aria-expanded="true" aria-controls="shippingData" style="cursor: pointer;">
                                Szállítási adatok
                                <span class="toggle-arrow">▼</span>
                            </h4>


                            <div class="collapse" id="shippingData">

                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="checkout__input__checkbox">
                                            <label for="use_existing_shipping">
                                                Az alábbi címet szeretném használni szállítási címként
                                                <input type="radio" id="use_existing_shipping" name="shipping_choice" value="exist" checked>
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>

                                        <select class="form-control w-100" name="selected_shipping_address">
                                            @foreach ($shipping_addresses as $shipping_address)
                                                <option value="{{ $shipping_address->id }}">
                                                    {{ $shipping_address->name }} ({{ $shipping_address->country }} - {{ $shipping_address->postal_code }} {{ $shipping_address->city }}, {{ $shipping_address->address_line }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-lg-12">

                                        <div class="checkout__input__checkbox">
                                            <label for="new_shipping_address">
                                                Új szállítási címet szeretnék megadni
                                                <input type="radio" id="new_shipping_address" name="shipping_choice" value="new">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>

                                    </div>
                                </div>

                                <div class="d-none" id="new_shipping_address_fields">

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="checkout__input">
                                                <p>Név<span>*</span></p>
                                                <input type="text" name="shipping_name" value="{{ old('shipping_name') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="checkout__input">
                                                <p>Ország<span>*</span></p>
                                                <select name="shipping_country" class="form-control w-100">
                                                    @foreach(config('countries') as $code => $name)
                                                        <option value="{{ $code }}" {{ old('shipping_country') === $code ? 'selected' : '' }}>{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="checkout__input">
                                                <p>Város<span>*</span></p>
                                                <input type="text" name="shipping_city" value="{{ old('shipping_city') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="checkout__input">
                                                <p>Irányítószám<span>*</span></p>
                                                <input type="text" name="shipping_zip" value="{{ old('shipping_zip') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="checkout__input">
                                                <p>Cím<span>*</span></p>
                                                <input type="text" name="shipping_address_line" value="{{ old('shipping_address_line') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Fizetési mód -->

                            <h4 class="mt-5 d-flex justify-content-between align-items-center toggle-header" data-bs-toggle="collapse" data-bs-target="#paymentData" aria-expanded="true" aria-controls="paymentData" style="cursor: pointer;">
                                Fizetési mód
                                <span class="toggle-arrow">▼</span>
                            </h4>


                            <div class="collapse" id="paymentData">
                                @foreach (config('payment_methods') as $method)
                                    @php
                                        $isPublic = $method['public'] ?? false;
                                        $isPartner = $customer && $customer->is_partner;
                                    @endphp

                                    @if ($isPublic || (!$isPublic && $isPartner))
                                        <div class="checkout__input__checkbox mb-2">
                                            <label for="{{ $method['slug'] }}">
                                                {{ $method['name'] }}
                                                @if (!empty($method['description']))
                                                    <small class="d-block text-muted">{{ $method['description'] }}</small>
                                                @endif
                                                <input type="radio"
                                                       id="{{ $method['slug'] }}"
                                                       name="payment_method"
                                                       value="{{ $method['slug'] }}"
                                                       @if ($loop->first) checked @endif>
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <h4 class="mt-5">
                                Megjegyzés
                            </h4>
                            <textarea name="comment" class="form-control" rows="3"></textarea>

                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="checkout__order">
                                <h4>Rendelés összesítése</h4>
                                <div class="checkout__order__products">Termékek <span>Összesen</span></div>
                                @if(0 < $cart_items->items->count())
                                    @php
                                        $total_item_amount = 0;
                                    @endphp
                                    <ul>
                                        @foreach($cart->items as $item)
                                            @php
                                                $subtotal = $item->product->gross_price * $item->quantity;
                                                $total_item_amount += $subtotal;
                                            @endphp
                                            <li>
                                                {{ $item->product->title }} <span>{{ number_format($item->product->gross_price * $item->quantity, 0, ',', ' ') }} Ft</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="checkout__order__subtotal">Részösszeg <span>{{ number_format($total_item_amount, 0, ',', ' ') }} Ft</span></div>
                                    <div class="checkout__order__total">Kupon <span>- todo</span></div>
                                    <div class="checkout__order__total">Összesen <span>{{ number_format($total_item_amount, 0, ',', ' ') }} Ft</span></div>


                                    <button type="submit" class="site-btn">Megrendelés</button>
                                @else
                                    <p class="text-center">A kosár üres. Kérjük, adjon hozzá termékeket a kosárhoz a rendeléshez.</p>
                                    <a href="{{ route('products.index') }}" class="site-btn">Vissza a termékekhez</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- Checkout Section End -->
@endsection

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const existingBillingRadio = document.getElementById('use_existing_billing');
            const newBillingRadio = document.getElementById('new_billing_address');
            const newBillingFields = document.getElementById('new_billing_address_fields');

            const existingShippingRadio = document.getElementById('use_existing_shipping');
            const newShippingRadio = document.getElementById('new_shipping_address');
            const newShippingFields = document.getElementById('new_shipping_address_fields');

            function toggleBillingFields() {
                if (newBillingRadio.checked) {
                    newBillingFields.classList.remove('d-none');
                } else {
                    newBillingFields.classList.add('d-none');
                }
            }

            function toggleShippingFields() {
                if (newShippingRadio.checked) {
                    newShippingFields.classList.remove('d-none');
                } else {
                    newShippingFields.classList.add('d-none');
                }
            }

            existingBillingRadio.addEventListener('change', toggleBillingFields);
            existingShippingRadio.addEventListener('change', toggleShippingFields);
            newBillingRadio.addEventListener('change', toggleBillingFields);
            newShippingRadio.addEventListener('change', toggleShippingFields);
        });
    </script>

