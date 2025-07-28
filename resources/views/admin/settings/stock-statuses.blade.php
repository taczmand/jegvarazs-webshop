@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Webshop / Raktári állapotok</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            <table class="table table-bordered" id="adminTable">
                <thead>
                <tr>
                    <th>Megnevezés</th>
                    <th>Slug</th>
                    <th>Szín</th>
                    <th>Feltétel</th>
                    <th>Állapot</th>
                </tr>
                </thead>
                <tbody>
                @foreach (config('stock_statuses') as $status)
                    <tr>
                        <td>{{ $status['name'] }}</td>
                        <td>{{ $status['slug'] }}</td>
                        <td>{{ $status['color'] }}</td>
                        <td>{{ $status['description'] }}</td>
                        <td>{{ $status['active'] ? 'active' : 'inactive' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
