@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection



@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Rendelés sikeres',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Rendelés sikeres', 'url' => route('contact')]
        ],
    ]
    ])

    <h1>Köszönjük a rendelést!</h1>

    <p>Rendelés azonosítója: <strong>#{{ $order->id }}</strong></p>

    @if (session('message'))
        <div class="alert alert-info">{{ session('message') }}</div>
    @endif

    <a href="{{ route('products.index') }}" class="btn btn-secondary">Új rendelés</a>

@endsection
