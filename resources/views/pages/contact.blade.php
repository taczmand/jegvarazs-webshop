@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection



@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Kapcsolat',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Kapcsolat', 'url' => route('contact')]
        ],
    ]
    ])

    <!-- Contact Section Begin -->
    <section class="contact spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-6 text-center">
                    <div class="contact__widget">
                        <span class="icon_phone"></span>
                        <h4>Telefon</h4>
                        <p>{{ $basicdata['company_phone'] ?? '' }}</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 text-center">
                    <div class="contact__widget">
                        <span class="icon_pin_alt"></span>
                        <h4>Cím</h4>
                        <p>{{ $basicdata['company_address'] ?? '' }}</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 text-center">
                    <div class="contact__widget">
                        <span class="icon_clock_alt"></span>
                        <h4>Nyitvatartás</h4>
                        <p>{{ $basicdata['company_open'] ?? '' }}</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 text-center">
                    <div class="contact__widget">
                        <span class="icon_mail_alt"></span>
                        <h4>E-mail</h4>
                        <p>{{ $basicdata['company_email'] ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Contact Section End -->




    <!-- Map Begin -->
    <div class="map">

        <iframe
            src="{{ $basicdata['company_address_maps_link'] ?? '' }}"
            height="500"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>

        <div class="map-inside">
            <i class="icon_pin"></i>
            <div class="inside-widget">
                <h4>{{ $basicdata['company_name'] ?? '' }}</h4>
                <ul>
                    <li>{{ $basicdata['company_address'] ?? '' }}</li>
                    <li>Telefon: {{ $basicdata['company_phone'] ?? '' }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Map End -->

    @if(!empty($company_sites))
        <div class="container py-5">

            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h2>Telephelyeink</h2>
                    </div>
                </div>
            </div>

            <div class="row">
                @foreach ($company_sites as $site)
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm rounded-3 h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-map mr-3 text-primary"></i>{{ $site['name'] }}
                                </h5>
                                <p class="card-text mb-2">
                                    <i class="fas fa-location-dot mr-3 text-secondary"></i>
                                    {{ $site['zip_code'] }} {{ $site['city'] }}, {{ $site['address_line'] }}, {{ $site['country'] }}
                                </p>
                                <p class="card-text mb-2">
                                    <i class="fas fa-phone mr-3 text-secondary"></i>
                                    <a href="tel:{{ $site['phone'] }}">{{ $site['phone'] }}</a>
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-envelope mr-3 text-secondary"></i>
                                    <a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
        @endif


    @if(!empty($employees))
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h2>Kollégáink</h2>
                    </div>
                </div>
            </div>

            <div class="row gy-4">
                @foreach ($employees as $employee)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0 rounded-3 text-center p-3">
                            <img src="{{ asset('storage/' . $employee['profile_photo_path']) }}"
                                 class="rounded-circle mx-auto mb-3"
                                 alt="{{ $employee['name'] }}"
                                 style="width: 100px; height: 100px; object-fit: cover;">

                            <div class="card-body">
                                <h5 class="card-title mb-1">{{ $employee['name'] }}</h5>
                                <p class="text-muted mb-2">{{ $employee['position'] }}</p>

                                <p class="mb-1">
                                    <i class="fas fa-phone mr-3 text-secondary"></i>
                                    <a href="tel:{{ $employee['phone'] }}">{{ $employee['phone'] }}</a>
                                </p>

                                <p>
                                    <i class="fas fa-envelope mr-3 text-secondary"></i>
                                    <a href="mailto:{{ $employee['email'] }}">{{ $employee['email'] }}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection
