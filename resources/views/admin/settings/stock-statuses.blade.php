@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Beállítások / Raktári állapotok</h1>
        </div>

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

@endsection
