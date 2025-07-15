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
                        <p>+36 (20) 778-9928</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 text-center">
                    <div class="contact__widget">
                        <span class="icon_pin_alt"></span>
                        <h4>Cím</h4>
                        <p>5100 Jászberény, Bercsényi út 15</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 text-center">
                    <div class="contact__widget">
                        <span class="icon_clock_alt"></span>
                        <h4>Nyitvatartás</h4>
                        <p>9 - 17 óráig</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 text-center">
                    <div class="contact__widget">
                        <span class="icon_mail_alt"></span>
                        <h4>E-mail</h4>
                        <p>info@jegvarazsbolt.hu</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Contact Section End -->

    <!-- Map Begin -->
    <div class="map">

        <iframe
            src="{{ $basicdata['company_address_maps_link'] }}"
            height="500"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>

        <div class="map-inside">
            <i class="icon_pin"></i>
            <div class="inside-widget">
                <h4>Jászberény</h4>
                <ul>
                    <li>Bercsényi út 15.</li>
                    <li>Telefon: +36 (20) 778-9928</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Map End -->
@endsection
