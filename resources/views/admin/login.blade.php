<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{ asset('storage/' . $basicmedia['favicon']) }}" type="image/x-icon">
    <!-- iOS ikon (Apple Touch Icon) -->
    <link rel="apple-touch-icon" href="{{ asset('storage/' . $basicmedia['favicon']) }}">

    <!-- App név a Home Screen-en -->
    <meta name="apple-mobile-web-app-title" content="Jégvarázs Admin">

    <!-- iOS „Add to Home Screen” app mód engedélyezése -->
    <meta name="apple-mobile-web-app-capable" content="yes">

    <!-- Status bar style (fekete/fehér háttér) -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Admin')</title>
    @vite('resources/sass/admin.scss')
</head>

<body class="bg-gradient-custom">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-lg-block bg-login-image">
                                <img src="{{ asset('storage/' . $basicmedia['default_logo']) }}" alt="Logo" class="">
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    @if($errors->any())
                                        <div class="admin-validation-error mb-5">
                                            {{ $errors->first() }}
                                        </div>
                                    @endif
                                    <form class="user" method="POST" action="{{ route('admin.login') }}">
                                        @csrf
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user" name="email" placeholder="E-mail cím..." required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" name="password">
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck">
                                                <label class="custom-control-label" for="customCheck">Emlékezz rám</label>
                                            </div>
                                        </div>

                                        <button class="btn btn-primary btn-user btn-block" type="submit">Bejelentkezés</button>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
    <script src="{{ asset('vendor/js/jquery-3.3.1.min.js') }}"></script>
    <!-- Scripts -->
    @vite('resources/js/admin.js')
</body>

</html>
