

@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Bejelentkezés',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')]
        ],
    ]
    ])

    @if($errors->any())
        <div class="shop-validation-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="w-100 p-4 bg-light rounded shadow-sm light-box">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email cím</label>
            <input type="email" name="email" id="email" class="form-control" value="" placeholder="Email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Jelszó</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Jelszó" required>
        </div>

        <button type="submit" class="w-100 site-btn">Belépés</button>
    </form>
    <div class="text-center mt-3">
        <a href="{{ route('registration') }}">Regisztráció</a>
    </div>
    <div class="text-center mt-3">
        <a href="{{ route('password.request') }}">Elfelejtett jelszó?</a>
    </div>
@endsection


