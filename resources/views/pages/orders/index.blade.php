@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Rendelések',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Rendelések', 'url' => route('customer.orders')]
        ],
    ]
    ])

    <div class="container mt-3">

        @if($errors->any())
            <div class="shop-validation-error">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>Rendelés azonosító</th>
                    <th>Dátum</th>
                    <th>Fizetendő összeg</th>
                    <th>Rendelés állapota</th>
                    <th>Megtekintés</th>
                </tr>
                </thead>
                <tbody>
                @forelse($orders as $order)
                    @php
                        $order_total = $order->items->sum(function($item) {
                            return $item->gross_price * $item->quantity;
                        });
                    @endphp
                    <tr>
                        <td><strong>{{ $order->id }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y.m.d H:i:s') }}</td>
                        <td>{{ $order_total }}</td>
                        <td>{{ $order->status_label }}</td>
                        <td class="text-center">
                            <a href="{{ route('customer.order.show', ['id' => $order->id]) }}" class="site-btn">
                                <i class="fas fa-eye me-1"></i> Megtekintés
                            </a>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Nem található rendelés.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
