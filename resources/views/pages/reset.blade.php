@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Elfelejtett jelszó',
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

    <form method="POST" action="{{ route('password.update') }}" class="w-100 p-4 bg-light rounded shadow-sm light-box">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ old('email', $email) }}">

        <div class="mb-3">
            <label>Új jelszó:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Jelszó megerősítése:</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="site-btn w-100">Jelszó beállítása</button>
    </form>

@endsection


