@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Keresési találatok',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Találatok', 'url' => route('search')]
        ],
    ]
    ])

    <h1>Találatok</h1>
    <p>Összesen <strong>{{ $products->total() }}</strong> találat a(z) {{ request()->input('query') }} kifejezésre</p>
    <div class="row">
        @forelse($products as $product)
            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="product-item">
                    <div class="product-image">
                        @php
                            $fullSlug = $product->category->getFullSlug() . '/' . $product->slug;
                            $mainPhoto = $product->photos->firstWhere('is_main', true);
                        @endphp
                        <a href="{{ route('products.resolve', ['slugs' => $fullSlug]) }}">
                            <img src="{{ asset('storage/' . $mainPhoto?->path ?? 'static_media/no-image.jpg') }}" alt="{{ $product->name }}" class="img-fluid">
                        </a>
                    </div>
                    <div class="product-info">
                        <h5 class="product-title">
                            <a href="{{ route('products.resolve', ['slugs' => $fullSlug]) }}">{{ $product->name }}</a>
                        </h5>
                        <p class="product-price">{{ number_format($product->price, 0, ',', ' ') }} Ft</p>
                        <p class="product-description">{{ Str::limit($product->description, 100) }}</p>
                    </div>
                </div>
            </div>
        @empty
            <p>Nincs találat a keresésre.</p>
        @endforelse
    </div>
    <div class="pagination">
        {{ $products->links() }}
    </div>

@endsection
