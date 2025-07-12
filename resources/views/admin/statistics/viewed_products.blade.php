@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h2 class="h5 text-primary mb-0"><i class="fa-solid fa-chart-simple text-primary me-2"></i> Jelentések / Megtekintett termékek</h2>
        </div>

        @if(auth('admin')->user()->can('view-viewed-products'))

            <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                <div class="filter-group">
                    <i class="fa-solid fa-filter text-gray-500"></i>
                </div>

                <div class="filter-group flex-grow-1 flex-md-shrink-0">
                    <input type="text" placeholder="Terméknév" class="filter-input form-control" data-column="1">
                </div>
            </div>

            <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th data-priority="1">Terméknév</th>
                    <th data-priority="2">Megtekintések száma</th>
                </tr>
                </thead>
            </table>
        @else
            <div class="alert alert-warning" role="alert">
                Nincs jogosultságod a megtekintett termékek megtekintésére.
            </div>
        @endif
    </div>


@endsection

@section('scripts')
    <script type="module">

        document.addEventListener('DOMContentLoaded', () => {
            initCrud({
                tableId: 'adminTable',
                dataUrl: '{{ route('admin.stats.watched_products.data') }}',
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                columns: [
                    { data: 'product_id' },
                    { data: 'product_title' },
                    { data: 'number_of_hits' }
                ]
            });
        });
    </script>
@endsection
