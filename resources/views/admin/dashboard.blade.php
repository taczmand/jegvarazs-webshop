@extends('layouts.admin')

@section('title', 'Vezérlőpult')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>
    <!-- Itt jön az SB Admin dashboard tartalom -->
    @section('content')
        <div class="container-fluid py-4">
            <h1 class="mb-4">Vezérlőpult</h1>

            <div class="row g-4">
                <!-- Összes rendelés -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-start border-primary border-4 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Rendelések</h6>
                            <h3 class="card-text">{{ $orderCount }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Termékek száma -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-start border-success border-4 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Termékek</h6>
                            <h3 class="card-text">{{ $productCount }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Vásárlók -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-start border-info border-4 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Vásárlók</h6>
                            <h3 class="card-text">{{ $customerCount }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Árbevétel -->
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-start border-warning border-4 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Árbevétel</h6>
                            <h3 class="card-text">{{ number_format($revenue, 0, ',', ' ') }} Ft</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
@endsection



