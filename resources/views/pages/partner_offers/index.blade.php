@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection

@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Ajánlatok',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Ajánlatok', 'url' => route('partner.offers.index')]
        ],
    ]])

    <div class="container mt-3">
        @if($errors->any())
            <div class="shop-validation-error">{{ $errors->first() }}</div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Saját ajánlatok</h4>
            <a href="{{ route('partner.offers.create') }}" class="site-btn">+ Új ajánlat</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Cím</th>
                    <th>Címzett e-mail</th>
                    <th>Küldve</th>
                    <th>Létrehozva</th>
                    <th>Újraküldés</th>
                </tr>
                </thead>
                <tbody>
                @forelse($offers as $offer)
                    <tr>
                        <td><strong>#{{ $offer->id }}</strong></td>
                        <td>{{ $offer->title }}</td>
                        <td>{{ $offer->recipient_email }}</td>
                        <td>{{ $offer->sent_at ? \Carbon\Carbon::parse($offer->sent_at)->format('Y.m.d H:i') : '-' }}</td>
                        <td>{{ $offer->created_at ? \Carbon\Carbon::parse($offer->created_at)->format('Y.m.d H:i') : '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('partner.offers.resend', ['id' => $offer->id]) }}" class="d-flex gap-2">
                                @csrf
                                <input type="email" name="recipient_email" class="form-control" value="{{ $offer->recipient_email }}" required>
                                <button class="site-btn" type="submit">Küldés</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Nincs még ajánlat.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
