<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="globalToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div id="globalToastMessage" class="toast-body">
                Művelet sikeres!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Bezárás"></button>
        </div>
    </div>
</div>


<!-- Vendor Scripts -->
<script src="{{ asset('vendor/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('vendor/js/datatables.min.js') }}"></script>

<!-- Custom Scripts with Vite load -->
@vite('resources/js/admin.js')
@yield('scripts')
</body>

</html>
