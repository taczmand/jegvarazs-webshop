@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection

@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Rendelés sikertelen',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Rendelés sikertelen', 'url' => route('index')]
        ],
    ]
    ])

    <div class="w-100 p-4 bg-light rounded shadow-sm light-box">
        <h3>A fizetés nem sikerült</h3>

        <p class="mb-1">
            Sikertelen tranzakció.
        </p>
        <p class="mb-4">
            SimplePay tranzakció azonosító: {{ $transaction_id }}
        </p>
        <p class="mb-1">
            Kérjük, ellenőrizze a tranzakció során megadott adatok helyességét.
        </p>
        <p class="mb-4">
            Amennyiben minden adatot helyesen adott meg, a visszautasítás okának kivizsgálása kapcsán kérjük, szíveskedjen kapcsolatba lépni kártyakibocsátó bankjával.
        </p>

        @if(isset($order->id))
            <p><strong>Rendelés azonosító:</strong> {{ $order->id }}</p>
        @endif

        @if(!empty($result))
            <p><strong>Hiba részletei:</strong></p>
            <pre class="bg-gray-100 p-2 rounded">{{ json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        @endif

        <div class="mt-6">
            <a href="{{ route('checkout') }}" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded">
                Vissza a fizetéshez
            </a>
        </div>
    </div>

@endsection
