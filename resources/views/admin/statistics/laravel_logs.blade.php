@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Laravel logok</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-admin-logs'))

                <form method="GET" class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-0" for="file">Log fájl</label>
                        <select id="file" name="file" class="form-select" onchange="this.form.submit()">
                            <option value="">-- válassz --</option>
                            @foreach($files as $f)
                                <option value="{{ $f['name'] }}" @selected($selected === $f['name'])>
                                    {{ $f['name'] }} ({{ number_format(($f['size'] ?? 0) / 1024, 0, ',', ' ') }} KB)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label mb-0" for="lines">Sorok</label>
                        <input id="lines" name="lines" type="number" min="10" max="5000" class="form-control" value="{{ $lines ?? 500 }}">
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label mb-0" for="q">Keresés</label>
                        <input id="q" name="q" type="text" class="form-control" value="{{ $q ?? '' }}" placeholder="szöveg...">
                    </div>

                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Megjelenítés</button>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.stats.laravel_logs') }}">Törlés</a>
                    </div>
                </form>

                @if($error)
                    <div class="alert alert-danger" role="alert">
                        {{ $error }}
                    </div>
                @endif

                @if($selected)
                    <div class="mb-2 text-muted">
                        <strong>Fájl:</strong> {{ $selected }}
                        <span class="ms-2"><strong>Sorok:</strong> {{ $lines ?? 0 }}</span>
                        @if($q)
                            <span class="ms-2"><strong>Keresés:</strong> {{ $q }}</span>
                        @endif
                    </div>
                @endif

                @if(!is_null($content))
                    <pre style="max-height: 70vh; overflow: auto; background: #0b1220; color: #e5e7eb; padding: 12px; border-radius: 8px; white-space: pre;">
{{ $content }}
                    </pre>
                @else
                    <div class="alert alert-info" role="alert">
                        Válassz egy log fájlt.
                    </div>
                @endif

            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a logok megtekintésére.
                </div>
            @endif
        </div>
    </div>


@endsection
