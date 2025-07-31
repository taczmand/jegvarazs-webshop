@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Gratulálunk!',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')]
        ],
    ]
    ])
    <div class="w-100 p-4 bg-light rounded shadow-sm light-box">
        <h3>Regisztráció sikeres!</h3>
        <p>Hamarosan ellenőrizzük és aktiváljuk fiókját.</p>
        <a class="site-btn w-100" href="{{ route('index') }}">Ugrás a főoldalra</a>
    </div>
@endsection

