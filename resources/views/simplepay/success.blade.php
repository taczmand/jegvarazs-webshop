@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection

@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Fizetés elindítása sikeres',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Fizetés elindítása sikeres', 'url' => route('index')]
        ],
    ]
    ])

    <div class="w-100 p-4 bg-light rounded shadow-sm light-box">
        <h3>Köszönjük a rendelést!</h3>
        <p class="mb-4">
            A fizetési folyamatot sikeresen elindította. Kérjük, legyen türelemmel, a végleges visszaigazolás néhány pillanaton belül meg fog érkezni. Amint ez megtörténik, e-mailben is értesítést küldünk Önnek.
        </p>



        @if(isset($order))
            <p><strong>Rendelés azonosító:</strong> {{ $order->id }}</p>
            <p>Megrendelt termékek:</p>
            <ul class="list-unstyled mb-4">
                @foreach($order->items as $item)
                    <li>{{ $item->product_name }} - {{ $item->quantity }} db</li>
                @endforeach
            </ul>
            <p><strong>Összeg:</strong> {{ number_format($order_total, 0, ',', ' ') }} {{ $order->currency ?? 'HUF' }}</p>
        @endif

        <a href="{{ route('index') }}" class="site-btn">Visszatérés a főoldalra</a>
    </div>

@endsection
