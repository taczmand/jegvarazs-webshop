@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Gratulálunk!',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Rólunk', 'url' => route('about')]
        ],
    ]
    ])

    <h1>Regisztráció sikeres!</h1>
    <p>Hamarosan ellenőrizzük és aktiváljuk fiókját.</p>
@endsection

