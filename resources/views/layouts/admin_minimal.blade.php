<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, user-scalable=no, maximum-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('storage/' . $basicmedia['favicon']) }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('storage/' . $basicmedia['favicon']) }}">

    <meta name="apple-mobile-web-app-title" content="Jégvarázs Admin">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>@yield('title', 'Admin')</title>
    @vite('resources/sass/admin.scss')
    <script>
        window.appConfig = {
            APP_URL: "{{ config('app.url') }}"
        };
    </script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.5/css/responsive.dataTables.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
</head>

<body>
<div class="container-fluid pt-3 page-content">
    @yield('content')
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

<script src="{{ asset('vendor/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('vendor/js/datatables.min.js') }}"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.5/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.5/js/responsive.dataTables.js"></script>

@vite('resources/js/admin.js')
@yield('scripts')
</body>

</html>
