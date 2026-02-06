<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Webshop')</title>

    <meta property="og:title" content="{{ $basicmedia['facebook_og_title'] ?? 'Jégvarázs bolt' }}">
    <meta property="og:description" content="{{ $basicmedia['facebook_og_description'] ?? 'Klímaberendezések és szerelési segédanyagok kis- és nagykereskedelmi értékesítése' }}">
    <meta property="og:image" content="{{ isset($basicmedia['facebook_og_image']) && $basicmedia['facebook_og_image']
    ? asset('storage/' . $basicmedia['facebook_og_image'])
    : asset('images/default-og.jpg') }}">

    <meta property="og:url" content="https://jegvarazsbolt.hu/">
    <meta property="og:type" content="website">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;900&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="shortcut icon" href="{{ asset('storage/' . $basicmedia['favicon']) }}" type="image/x-icon">
    @vite('resources/sass/shop.scss')
    <script>
        window.appConfig = {
            APP_URL: "{{ config('app.url') }}"
        };
    </script>
    <!-- Meta Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '4502787593071439');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
                   src="https://www.facebook.com/tr?id=4502787593071439&ev=PageView&noscript=1"
        /></noscript>
    <!-- End Meta Pixel Code -->
</head>
<body class="">

@include('partials.humberger')

@include('partials.header')

@hasSection('hero')
    @yield('hero')
@endif
<!-- Page Preloder -->
<div id="preloder">
    <div class="loader"></div>
</div>


<main class="container mx-auto">
    @yield('content')
    @if (!request()->routeIs('checkout') && !request()->routeIs('appointment') && !request()->routeIs('offer') )
        @include('partials.contact-form')
    @endif
</main>

<div id="myCoolToastContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"></div>

@include('partials.footer')

<!-- Vendor Scripts -->
<script src="{{ asset('vendor/js/jquery-3.3.1.min.js') }}"></script>

<!-- Custom Scripts with Vite load -->
@vite('resources/js/shop.js')
@yield('scripts')
</body>
</html>
