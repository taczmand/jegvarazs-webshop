@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection



@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Adatkezelési nyilatkozat – SimplePay fizetéshez',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Adatkezelési nyilatkozat – SimplePay fizetéshez', 'url' => route('simplepay.adattovabbitasi_nyilatkozat')]
        ],
    ]
    ])

    <div class="container mx-auto my-12 max-w-3xl bg-white rounded-2xl p-8">

        <p class="mt-4 text-gray-700 leading-relaxed">
            Tudomásul veszem, hogy a(z)
            <strong>Gál-Ker Jégvarázs Klíma és Biztonságtechnikai Korlátolt Felelősségű Társaság</strong>
            (<strong>5136 Jászszentandrás, Kossuth út 64.</strong>) adatkezelő által a(z)
            <a href="https://www.jegvarazsbolt.hu" target="_blank" class="text-blue-600 underline">www.jegvarazsbolt.hu</a>
            felhasználói adatbázisában tárolt alábbi személyes adataim átadásra kerülnek a
            <strong>SimplePay Zrt.</strong>, mint adatfeldolgozó részére.
        </p>

        <p class="mb-4 text-gray-700 leading-relaxed">
            Az adatkezelő által továbbított adatok köre az alábbi:
            <em>név, e-mail cím, telefonszám, számlázási cím, szállítási cím, megrendelés azonosító, megrendelt termékek és fizetési összeg</em>.
        </p>

        <p class="mb-4 text-gray-700 leading-relaxed">
            Az adatfeldolgozó által végzett adatfeldolgozási tevékenység jellege és célja a SimplePay
            Adatkezelési tájékoztatóban, az alábbi linken tekinthető meg:
        </p>

        <p class="mb-6 text-center">
            <a href="https://simplepay.hu/adatkezelesi-tajekoztatok/" target="_blank"
               class="text-blue-600 font-medium underline">
                https://simplepay.hu/adatkezelesi-tajekoztatok/
            </a>
        </p>
    </div>
@endsection

