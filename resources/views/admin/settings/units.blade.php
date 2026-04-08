@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Webshop / Mértékegységek</h2>
            @if(auth('admin')->user()->can('create-unit'))
                <button class="btn btn-success" id="addUnit"><i class="fas fa-plus me-1"></i> Új mértékegység</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-units'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Név" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Rövidítés" class="filter-input form-control" data-column="2">
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="unitsTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Név</th>
                        <th>Rövidítés</th>
                        <th>Státusz</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a mértékegységek megtekintésére.
                </div>
            @endif
        </div>
    </div>


    <div class="modal fade" id="unitModal" tabindex="-1" aria-labelledby="unitModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="unitForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="unitModalLabel">Mértékegység szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="unit_id" name="id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Név</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="abbreviation" class="form-label">Rövidítés</label>
                            <input type="text" class="form-control" id="abbreviation" name="abbreviation" required>
                        </div>
                        <div class="mb-3">
                            <label for="active" class="form-label">Aktív</label>
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" class="" id="active" name="active" value="1" checked>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-btn">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">

        document.addEventListener('DOMContentLoaded', () => {
            initCrud({
                tableId: 'unitsTable',
                modalId: 'unitModal',
                formId: 'unitForm',
                addButtonId: 'addUnit',
                dataUrl: '{{ route('admin.units.data') }}',
                storeUrl: '{{ route('admin.units.store') }}',
                destroyUrl: '{{ url('/admin/beallitasok/mertekegysegek') }}',
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'abbreviation' },
                    { data: 'active' },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@endsection
