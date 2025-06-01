@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Beállítások / Szállítási módok</h1>
        </div>

        <table class="table table-bordered" id="adminTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Név</th>
                <th>Kód</th>
                <th>Extra költség</th>
                <th>Állapot</th>
                <th>Láthatóság</th>
            </tr>
            </thead>
            <tbody>
            @foreach (config('shipping_methods') as $method)
                <tr>
                    <td>{{ $method['name'] }}</td>
                    <td>{{ $method['code'] }}</td>
                    <td>{{ $method['fee'] }}</td>
                    <td>{{ $method['description'] }}</td>
                    <td>{{ $method['active'] ? 'active' : 'inactive' }}</td>
                    <td>{{ $method['public'] ? 'bárki számára' : 'csak partner számára' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection
