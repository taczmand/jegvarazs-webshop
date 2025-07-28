@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Webshop / Fizetési módok</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            <table class="table table-bordered" id="adminTable">
                <thead>
                <tr>
                    <th>Fizetési mód</th>
                    <th>Slug</th>
                    <th>Extra költség</th>
                    <th>Leírás</th>
                    <th>Állapot</th>
                    <th>Láthatóság</th>
                </tr>
                </thead>
                <tbody>
                    @foreach (config('payment_methods') as $method)
                        <tr>
                            <td>{{ $method['name'] }}</td>
                            <td>{{ $method['slug'] }}</td>
                            <td>{{ $method['fee'] }}</td>
                            <td>{{ $method['description'] }}</td>
                            <td>{{ $method['active'] ? 'active' : 'inactive' }}</td>
                            <td>{{ $method['public'] ? 'bárki számára' : 'csak partner számára' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
