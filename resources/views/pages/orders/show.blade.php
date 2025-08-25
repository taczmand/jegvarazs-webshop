@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Rendelés részletei',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Rendelések', 'url' => route('customer.orders')],
            ['title' => 'Rendelés részletei', 'url' => route('customer.order.show', ['id' => $order->id])],
        ],
    ]
    ])

    <div class="container mt-3">


        <div class="mb-3">
            <a href="{{ route('customer.orders') }}" class="site-btn">
                <i class="fas fa-arrow-left"></i> Vissza a rendelés listához
            </a>

        <h3 class="mt-3">Rendelés részletei</h3>

        <div class="mb-3">
            <strong>Rendelés ID:</strong> {{ $order->id }} <br>
            <strong>Dátum:</strong> {{ $order->created_at->format('Y-m-d H:i') }} <br>
            <strong>Státusz:</strong>
            @php
                $statusClass = match($order->status) {
                    'paid' => 'text-success',
                    'failed' => 'text-danger',
                    'pending' => 'text-warning',
                    default => 'text-secondary',
                };
            @endphp
            <span class="{{ $statusClass }}">{{ ucfirst($order->status_label) }}</span>
        </div>

        <h4>Rendelés tételei</h4>
        @if($order->items->isEmpty())
            <p>Nincsenek tételek a rendelésben.</p>
        @else
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Termék</th>
                    <th>Mennyiség</th>
                    <th>Egységár</th>
                    <th>Összeg</th>
                </tr>
                </thead>
                <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->gross_price, 0, ',', ' ') }} Ft</td>
                        <td>{{ number_format($item->gross_price * $item->quantity, 0, ',', ' ') }} Ft</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="text-end mt-3">
                <strong>Összesen: {{ number_format($order->items->sum(fn($i) => $i->gross_price * $i->quantity), 0, ',', ' ') }} Ft</strong>
            </div>
        @endif

        <h4 class="mt-4 mb-4">Kapcsolati adatok</h4>
        <table class="table table-bordered">
            <tr>
                <th>Név</th>
                <td>{{ $order->contact_last_name }} {{ $order->contact_first_name }}</td>
            </tr>
            <tr>
                <th>E-mail</th>
                <td>{{ $order->contact_email }}</td>
            </tr>
            <tr>
                <th>Telefon</th>
                <td>{{ $order->contact_phone }}</td>
            </tr>
        </table>
        <h4 class="mt-4 mb-4">Szállítási adatok</h4>
        <table class="table table-bordered">
            <tr>
                <th>Név</th>
                <td>{{ $order->shipping_name }}</td>
            </tr>
            <tr>
                <th>Cím</th>
                <td>{{ $order->shipping_country }} {{ $order->shipping_postal_code }} {{ $order->shipping_city }}, {{ $order->shipping_address_line }}</td>
            </tr>
        </table>
        <h4 class="mt-4 mb-4">Számlázási adatok</h4>
        <table class="table table-bordered">
            <tr>
                <th>Név</th>
                <td>{{ $order->billing_name }}</td>
            </tr>
            <tr>
                <th>Cím</th>
                <td>{{ $order->billing_country }} {{ $order->billing_postal_code }} {{ $order->billing_city }}, {{ $order->billing_address_line }}</td>
            </tr>
            <tr>
                <th>Adószám</th>
                <td>{{ $order->billing_tax_number ?? 'Nincs megadva' }}</td>
            </tr>
        </table>

        <h4 class="mt-4 mb-4">Megjegyzés</h4>
        <div class="mb-3">
            @if($order->comment)
                <p>{{ $order->comment }}</p>
            @else
                <p>Nincs megjegyzés a rendeléshez.</p>
            @endif
        </div>

        <h4 class="mt-4 mb-4">Összegzés</h4>

        <table class="table table-bordered">
            <tr>
                <th>Bruttó végösszeg</th>
                <td>{{ number_format($order->items->sum(fn($i) => $i->gross_price * $i->quantity), 0, ',', ' ') }} Ft</td>
            </tr>
            <tr>
                <th>Fizetési mód</th>
                <td>{{ $order->payment_label }}</td>
            </tr>
        </table>


        @if($order->status === 'payment_failed')
            <div class="mt-4">
                <a href="{{ route('customer.order.retry_payment', ['id' => $order->id]) }}" class="btn btn-primary">
                    Fizetés újrapróbálása
                </a>

                <form action="{{ route('customer.order.destroy', ['id' => $order->id]) }}" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Rendelés törlése</button>
                </form>
            </div>
        @endif
    </div>

@endsection
