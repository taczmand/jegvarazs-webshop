@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Fizetés újrapróbálása',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Fizetés újrapróbálása', 'url' => route('customer.order.retry_payment', ['id' => $order->id])],
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

        @php
            $order_total = $order->items->sum(function($item) {
                return $item->gross_price * $item->quantity;
            });
        @endphp

        <div class="w-100 p-4 bg-light rounded shadow-sm">
            <h3>Fizetés újrapróbálása</h3>

            <p>Rendelés azonosítója: <strong>#{{ $order->id }}</strong></p>

            <p>Megrendelt termékek:</p>
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>Termék</th>
                        <th>Mennyiség</th>
                        <th>Bruttó egységár</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }} db</td>
                            <td>{{ $item->gross_price }} Ft</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p>Összeg:<strong> {{ number_format($order_total, 0, ',', ' ') }} Ft </strong></p>

            <p>Fizetési mód kiválasztása:</p>
            <form action="{{ route('customer.order.process_retry_payment') }}" method="POST">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <input type="hidden" name="order_total" value="{{ $order_total }}">

                @foreach (config('payment_methods') as $method)
                    @php
                        $customer = auth('customer')->user();
                        $isPublic = $method['public'] ?? false;
                        $isPartner = $customer && $customer->is_partner;
                    @endphp

                    @if ($isPublic || (!$isPublic && $isPartner))
                        <div class="checkout__input__checkbox mb-2">
                            <label for="{{ $method['slug'] }}">
                                {{ $method['name'] }}
                                @if (!empty($method['description']))
                                    <small class="d-block text-muted">{{ $method['description'] }}</small>
                                @endif
                                <input type="radio"
                                       id="{{ $method['slug'] }}"
                                       name="payment_method"
                                       value="{{ $method['slug'] }}"
                                       @if ($loop->first) checked @endif>
                                <span class="checkmark"></span>
                            </label>
                        </div>
                    @endif
                @endforeach

                <button type="submit" class="site-btn">Fizetés</button>
            </form>
    </div>

@endsection
