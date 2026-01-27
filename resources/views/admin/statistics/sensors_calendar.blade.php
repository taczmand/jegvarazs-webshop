@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Jelentések / Szenzorok / {{ $deviceId }}</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-sensor-reports'))

                <form method="GET" class="d-flex gap-2 align-items-center mb-3">
                    <label for="year" class="mb-0">Év</label>
                    <select id="year" name="year" class="form-select" style="max-width: 120px" onchange="this.form.submit()">
                        @for($y = $minYear; $y <= $maxYear; $y++)
                            <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                        @endfor
                    </select>

                    <a class="btn btn-outline-secondary" href="{{ route('admin.stats.sensors') }}">Vissza</a>
                </form>

                @php
                    $months = [
                        1 => 'Jan', 2 => 'Feb', 3 => 'Már', 4 => 'Ápr', 5 => 'Máj', 6 => 'Jún',
                        7 => 'Júl', 8 => 'Aug', 9 => 'Sze', 10 => 'Okt', 11 => 'Nov', 12 => 'Dec'
                    ];
                @endphp

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                        <tr>
                            <th style="width: 60px">Nap</th>
                            @foreach($months as $m => $label)
                                <th class="text-center">{{ $label }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @for($day = 1; $day <= 31; $day++)
                            <tr>
                                <th class="text-center">{{ $day }}</th>
                                @for($m = 1; $m <= 12; $m++)
                                    @php
                                        $valid = checkdate($m, $day, $year);
                                        $c = $valid ? ($counts[$m][$day] ?? 0) : null;

                                        $bucket = ($valid && $c !== null) ? (int) floor($c / 10) : 0;
                                        $bucket = min($bucket, 9);

                                        $bgClasses = [
                                            0 => '',
                                            1 => 'bg-primary bg-opacity-10',
                                            2 => 'bg-primary bg-opacity-25',
                                            3 => 'bg-primary bg-opacity-50',
                                            4 => 'bg-primary bg-opacity-75',
                                        ];

                                        $bgClass = $bgClasses[$bucket] ?? 'bg-primary';
                                        $textClass = $bucket >= 4 ? 'text-white' : '';
                                    @endphp
                                    <td class="text-center {{ $bgClass }} {{ $textClass }}" style="min-height: 28px; line-height: 28px; @if(!$valid) background-color: #f8f9fc; @endif">
                                        @if($valid)
                                            @if($c > 0)
                                                {{ $c }}
                                            @endif
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                        </tbody>
                    </table>
                </div>

            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a szenzor riportok megtekintésére.
                </div>
            @endif
        </div>
    </div>


@endsection
