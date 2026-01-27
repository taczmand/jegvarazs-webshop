@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Szenzorok</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-sensor-reports'))
                @if(isset($deviceIds) && count($deviceIds) > 0)
                    <div class="list-group">
                        @foreach($deviceIds as $deviceId)
                            <a class="list-group-item list-group-item-action" href="{{ route('admin.stats.sensors.device', ['deviceId' => $deviceId, 'year' => $year]) }}">
                                {{ $deviceId }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info" role="alert">
                        Nincs még szenzor adat.
                    </div>
                @endif
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a szenzor riportok megtekintésére.
                </div>
            @endif
        </div>
    </div>


@endsection
