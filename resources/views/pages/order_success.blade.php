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
    @php
        // Kosár kiürítése a sikeres rendelés után
        $customer = auth('customer')->user();
        $cart = $customer->cart;
        $cart->items()->delete();
    @endphp

    <div class="w-100 p-4 bg-light rounded shadow-sm">
        <h3>Köszönjük a rendelést!</h3>

        <p>Rendelés azonosítója: <strong>#{{ $order->id }}</strong></p>

        <p>Megrendelt termékek:</p>
        <ul class="list-unstyled mb-4">
            @foreach($order->items as $item)
                <li>{{ $item->product_name }} - {{ $item->quantity }} db</li>
            @endforeach
        </ul>

        @if (session('message'))
            <div class="alert alert-info">{{ session('message') }}</div>
        @endif

        <a href="{{ route('index') }}" class="site-btn">Visszatérés a főoldalra</a>
    </div>

@endsection
