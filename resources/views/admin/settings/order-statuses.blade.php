@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Beállítások / Rendelési állapotok</h1>
        </div>

        <table class="table table-bordered" id="adminTable">
            <thead>
            <tr>
                <th>Megnevezés</th>
                <th>Slug</th>
                <th>Szín</th>
                <th>Leírás</th>
                <th>Állapot</th>
            </tr>
            </thead>
            <tbody>
            @foreach (config('order_statuses') as $status)
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
