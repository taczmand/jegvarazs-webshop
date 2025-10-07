<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Webshop')</title>
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
    @if (!request()->routeIs('checkout') && !request()->routeIs('appointment'))
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
