@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Profil',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Profil módosítása', 'url' => route('customer.profile')]
        ],
    ]
    ])

    <div class="container mt-3">

        @if($errors->any())
            <div class="shop-validation-error">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="shop-profile">

            <h4 class="mt-5 mb-5 d-flex justify-content-between align-items-center toggle-header" data-bs-toggle="collapse" data-bs-target="#profileData" aria-expanded="true" aria-controls="profileData" style="cursor: pointer;">
                Profiladatok módosítása
                <span class="toggle-arrow">▼</span>
            </h4>

            <form action="{{ route('customer.profile.update') }}" method="POST">
                @csrf
                <div class="collapse" id="profileData">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">Keresztnév</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', auth('customer')->user()->first_name) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Vezetéknév</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', auth('customer')->user()->last_name) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail cím</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', auth('customer')->user()->email) }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefonszám</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', auth('customer')->user()->phone) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="mb-3">
                                <label for="password" class="form-label">Új jelszó (opcionális)</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Új jelszó">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Jelszó megerősítése</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Jelszó megerősítése">
                            </div>
                        </div>
                        @if(auth('customer')->user()->is_partner)
                            <div class="col-md-6 mb-3">
                                <div class="mb-3">
                                    <label for="fgaz" class="form-label">F-Gáz azonosító</label>
                                    <input type="text" class="form-control" id="fgaz" name="fgaz" value="{{ old('fgaz', auth('customer')->user()->fgaz) }}">
                                </div>
                            </div>
                        @endif

                        <div class="col-md-12 mb-3">
                            <button type="submit" name="profile_save" class="site-btn"><i class="fa-solid fa-floppy-disk"></i> Profil mentése</button>
                        </div>
                    </div>
                </div>
            </form>

            <h4 class="mt-5 mb-5 d-flex justify-content-between align-items-center toggle-header" data-bs-toggle="collapse" data-bs-target="#billingData" aria-expanded="true" aria-controls="billingData" style="cursor: pointer;">
                Számlázási adatok
                <span class="toggle-arrow">▼</span>
            </h4>

            <form action="{{ route('customer.profile.update') }}" method="POST">
                @csrf

            <div class="collapse" id="billingData">
                <div class="responsive-table">
                    <div class="table-row table-header">
                        <div class="table-cell" style="max-width: 20px">#</div>
                        <div class="table-cell" style="min-width: 150px">Név</div>
                        <div class="table-cell">Adószám</div>
                        <div class="table-cell">Ország</div>
                        <div class="table-cell">Irányítószám</div>
                        <div class="table-cell">Város</div>
                        <div class="table-cell" style="min-width: 160px">Cím</div>
                        <div class="table-cell">Mentés</div>
                        <div class="table-cell">Törlés</div>
                    </div>
                        @php
                            $billingCounter = 0;
                        @endphp

                        @forelse($billingAddress as $billingAddressItem)
                            @php
                                $billingCounter++;
                            @endphp

                                <div class="table-row">

                                    <div class="table-cell" data-label="Sorszám">{{ $billingCounter }}</div>

                                    <div class="table-cell" data-label="Név">
                                        <input type="text" name="billing_addresses[{{ $billingAddressItem->id }}][billing_name]" class="form-control"
                                               value="{{ $billingAddressItem->name }}">
                                    </div>

                                    <div class="table-cell" data-label="Adószám">
                                        <input type="text" name="billing_addresses[{{ $billingAddressItem->id }}][billing_tax_number]" class="form-control"
                                               value="{{ $billingAddressItem->tax_number }}">
                                    </div>

                                    <div class="table-cell" data-label="Ország">
                                        <select name="billing_addresses[{{ $billingAddressItem->id }}][billing_country]" class="form-control">
                                            @foreach(config('countries') as $code => $name)
                                                <option value="{{ $code }}" {{ $billingAddressItem->country === $code ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="table-cell" data-label="Irányítószám">
                                        <input type="text" name="billing_addresses[{{ $billingAddressItem->id }}][billing_zip]" class="form-control"
                                               value="{{ $billingAddressItem->zip_code }}">
                                    </div>

                                    <div class="table-cell" data-label="Város">
                                        <input type="text" name="billing_addresses[{{ $billingAddressItem->id }}][billing_city]" class="form-control"
                                               value="{{ $billingAddressItem->city }}">
                                    </div>

                                    <div class="table-cell" data-label="Cím">
                                        <input type="text" name="billing_addresses[{{ $billingAddressItem->id }}][billing_address]" class="form-control"
                                               value="{{ $billingAddressItem->address_line }}">
                                    </div>

                                    <div class="table-cell" data-label="Mentés">
                                        <button type="submit" name="billing_save_id" class="site-btn" value="{{ $billingAddressItem->id }}">
                                            <i class="fa-solid fa-floppy-disk"></i>
                                        </button>
                                    </div>

                                    <div class="table-cell" data-label="Törlés">
                                        <button type="submit" name="billing_delete_id" class="site-btn-delete" value="{{ $billingAddressItem->id }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>


                                </div>
                    @empty
                        <div class="table-row">
                            <div class="table-cell" colspan="5" style="text-align:center;">
                                Nincs rögzített számlázási cím.
                            </div>
                        </div>

                    @endforelse


                </div>
            </form>
            </div>

            <h4 class="mt-5 mb-5 d-flex justify-content-between align-items-center toggle-header" data-bs-toggle="collapse" data-bs-target="#shippingData" aria-expanded="true" aria-controls="shippingData" style="cursor: pointer;">
                Szállítási adatok
                <span class="toggle-arrow">▼</span>
            </h4>

            <form action="{{ route('customer.profile.update') }}" method="POST">
                @csrf

                <div class="collapse" id="shippingData">
                    <div class="responsive-table">
                        <div class="table-row table-header">
                            <div class="table-cell" style="max-width: 20px">#</div>
                            <div class="table-cell" style="min-width: 150px">Név</div>
                            <div class="table-cell">E-mail cím</div>
                            <div class="table-cell">Telefonszám</div>
                            <div class="table-cell">Ország</div>
                            <div class="table-cell">Irányítószám</div>
                            <div class="table-cell">Város</div>
                            <div class="table-cell" style="min-width: 160px">Cím</div>
                            <div class="table-cell">Mentés</div>
                            <div class="table-cell">Törlés</div>
                        </div>
                        @php
                            $shippingCounter = 0;
                        @endphp
                        @forelse($shippingAddress as $shippingAddressItem)
                            @php
                                $shippingCounter++;
                            @endphp

                            <div class="table-row">
                                <div class="table-cell" data-label="Sorszám">{{ $shippingCounter }}</div>

                                <div class="table-cell" data-label="Név">
                                    <input type="text" name="shipping_addresses[{{ $shippingAddressItem->id }}][shipping_name]" class="form-control"
                                           value="{{ $shippingAddressItem->name }}">
                                </div>

                                <div class="table-cell" data-label="E-mail cím">
                                    <input type="text" name="shipping_addresses[{{ $shippingAddressItem->id }}][shipping_email]" class="form-control"
                                           value="{{ $shippingAddressItem->email }}">
                                </div>

                                <div class="table-cell" data-label="Telefonszám">
                                    <input type="text" name="shipping_addresses[{{ $shippingAddressItem->id }}][shipping_phone]" class="form-control"
                                           value="{{ $shippingAddressItem->phone }}">
                                </div>

                                <div class="table-cell" data-label="Ország">
                                    <select name="shipping_addresses[{{ $shippingAddressItem->id }}][shipping_country]" class="form-control">
                                        @foreach(config('countries') as $code => $name)
                                            <option value="{{ $code }}" {{ $shippingAddressItem->country === $code ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="table-cell" data-label="Irányítószám">
                                    <input type="text" name="shipping_addresses[{{ $shippingAddressItem->id }}][shipping_zip]" class="form-control"
                                           value="{{ $shippingAddressItem->zip_code }}">
                                </div>

                                <div class="table-cell" data-label="Város">
                                    <input type="text" name="shipping_addresses[{{ $shippingAddressItem->id }}][shipping_city]" class="form-control"
                                           value="{{ $shippingAddressItem->city }}">
                                </div>

                                <div class="table-cell" data-label="Cím">
                                    <input type="text" name="shipping_addresses[{{ $shippingAddressItem->id }}][shipping_address]" class="form-control"
                                           value="{{ $shippingAddressItem->address_line }}">
                                </div>

                                <div class="table-cell" data-label="Mentés">
                                    <button type="submit" name="shipping_save_id" class="site-btn" value="{{ $shippingAddressItem->id }}">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                    </button>
                                </div>

                                <div class="table-cell" data-label="Törlés">
                                    <button type="submit" name="shipping_delete_id" class="site-btn-delete" value="{{ $shippingAddressItem->id }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                        @empty
                            <div class="table-row">
                                <div class="table-cell" colspan="5" style="text-align:center;">
                                    Nincs rögzített szállítási cím.
                                </div>
                            </div>
                        @endforelse

                    </div>
                </div>
            </form>
        </div>

    </div>

@endsection
