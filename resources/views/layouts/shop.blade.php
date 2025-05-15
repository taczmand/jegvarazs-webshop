<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Webshop')</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;900&display=swap" rel="stylesheet">
    @vite('resources/sass/shop.scss')
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


<main class="container mx-auto p-4">
    @yield('content')
    @include('partials.contact-form')
</main>

@include('partials.footer')

<!-- Vendor Scripts -->
<script src="{{ asset('vendor/js/jquery-3.3.1.min.js') }}"></script>

<!-- Custom Scripts with Vite load -->
@vite('resources/js/shop.js')
</body>
</html>
