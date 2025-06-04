

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
        <div style="color:red;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="w-100" style="max-width: 400px; margin: auto;">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email cím</label>
            <input type="email" name="email" id="email" class="form-control" value="teszt.elek@mail.com" placeholder="Email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Jelszó</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Jelszó" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Belépés</button>
    </form>
    <div class="text-center mt-3">
        <a href="{{ route('password.reset') }}">Elfelejtett jelszó?</a>
    </div>
@endsection


