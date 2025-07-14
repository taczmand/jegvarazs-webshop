<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Webshop')</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;900&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">

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
    @include('partials.contact-form')
</main>

<!--<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
    <div id="globalToast" class="toast bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true" style="display: none;">
        <div class="d-flex">
            <div id="globalToastMessage" class="toast-body">
                Üzenet szövege
            </div>
            <button type="button" class="close text-white m-auto me-2" onclick="hideToast()">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
</div>-->

<div id="myCoolToastContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; padding: 1rem"></div>






@include('partials.footer')

<!-- Vendor Scripts -->
<script src="{{ asset('vendor/js/jquery-3.3.1.min.js') }}"></script>

<!-- Custom Scripts with Vite load -->
@vite('resources/js/shop.js')
</body>
</html>
