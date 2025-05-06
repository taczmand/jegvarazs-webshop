<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Webshop')</title>
    @vite('resources/sass/app.scss')
</head>
<body class="">

@include('partials.humberger')

@include('partials.header')

@hasSection('hero')
    @yield('hero')
@endif



<main class="container mx-auto p-4">
    @yield('content')
</main>

@include('partials.footer')
@vite('resources/js/app.js')
</body>
</html>
