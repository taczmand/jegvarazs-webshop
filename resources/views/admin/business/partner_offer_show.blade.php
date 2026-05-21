@extends('layouts.admin')

@section('content')
    <div class="container p-0">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Partner ajánlat #{{ $offer->id }}</h2>
            <a href="{{ route('admin.partner-offers.index') }}" class="btn btn-secondary">Vissza</a>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">
            <div class="mb-2"><strong>Partner:</strong> {{ $offer->customer?->last_name }} {{ $offer->customer?->first_name }} ({{ $offer->customer?->email }})</div>
            <div class="mb-2"><strong>Cím:</strong> {{ $offer->title }}</div>
            <div class="mb-2"><strong>Címzett:</strong> {{ $offer->recipient_email }}</div>
            <div class="mb-2"><strong>Küldve:</strong> {{ $offer->sent_at ? \Carbon\Carbon::parse($offer->sent_at)->format('Y.m.d H:i') : '-' }}</div>

            @if($offer->note)
                <hr>
                <div class="mb-2"><strong>Megjegyzés</strong></div>
                <div style="white-space: pre-wrap;">{{ $offer->note }}</div>
            @endif

            <hr>
            <div class="mb-2"><strong>Tételek</strong></div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                    <tr>
                        <th>Tétel</th>
                        <th style="width:90px">Menny.</th>
                        <th style="width:140px">Br. ár</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($offer->items as $item)
                        <tr>
                            <td>{{ $item->title }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->gross_price, 0, ',', ' ') }} Ft</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if($offer->pdf_path)
                <a class="btn btn-outline-primary" href="{{ route('admin.partner-offers.pdf', ['id' => $offer->id]) }}" target="_blank">PDF megnyitása</a>
            @endif

            @if($offer->pdf_path)
                <hr>
                <div class="mb-2"><strong>PDF előnézet</strong></div>
                <div class="border rounded" style="overflow:hidden; background:#fff;">
                    <iframe
                        src="{{ route('admin.partner-offers.pdf-inline', ['id' => $offer->id]) }}#page=1&view=FitH"
                        style="width: 100%; height: 520px; border: 0;"
                        loading="lazy"
                    ></iframe>
                </div>
            @endif
        </div>
    </div>
@endsection
