@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Letöltések',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Letöltések', 'url' => route('downloads')]
        ],
    ]
    ])

    <div class="container mt-3">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>Cím</th>
                    <th>Leírás</th>
                    <th>Dátum</th>
                    <th class="text-center">Letöltés</th>
                </tr>
                </thead>
                <tbody>
                @forelse($downloads as $download)
                    <tr>
                        <td><strong>{{ $download['file_name'] }}</strong></td>
                        <td>{{ $download['file_description'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($download['created_at'])->format('Y.m.d') }}</td>
                        <td class="text-center">
                            <a href="{{ asset('storage/' . $download['file_path']) }}" class="btn btn-sm btn-outline-primary" target="_blank" download>
                                <i class="fas fa-download me-1"></i> Letöltés
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Nincs elérhető letöltés.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
