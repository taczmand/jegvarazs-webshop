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
        <h3>A fizetés nem sikerült, megszakított tranzakció</h3>

        <p class="mb-4">
            Sajnos a fizetés nem tudott befejeződni, mert a tranzakció megszakadt. Kérlek, próbáld újra vagy válassz másik fizetési módot.
        </p>

        @if(isset($order->id))
            <p><strong>Rendelés azonosító:</strong> {{ $order->id }}</p>
        @endif

        @if(isset($transaction_id))
            <p><strong>Tranzakció azonosító:</strong> {{ $transaction_id }}</p>
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
