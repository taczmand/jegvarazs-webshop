<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin')</title>
    @vite('resources/sass/admin.scss')
</head>

<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">

    @include('admin.partials.sidebar') <!-- oldalsó menü -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            @include('admin.partials.topbar') <!-- felső sáv -->

            <!-- Begin Page Content -->
            <div class="container-fluid">
                @yield('content')
            </div>

        </div>

        @include('admin.partials.footer')

    </div>

</div>

<!-- Vendor Scripts -->
<script src="{{ asset('vendor/js/jquery-3.3.1.min.js') }}"></script>

<!-- Custom Scripts with Vite load -->
@vite('resources/js/admin.js')

</body>

</html>
