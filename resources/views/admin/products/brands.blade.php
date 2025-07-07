@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h2 class="h5 text-primary mb-0"><i class="fa-solid fa-list text-primary me-2"></i> Termékek / Gyártók</h2>
            @if(auth('admin')->user()->can('create-brand'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új gyártó</button>
            @endif
        </div>

        @if(auth('admin')->user()->can('view-brands'))

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
            </div>

            <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th data-priority="1">Név</th>
                    <th>Állapot</th>
                    <th>Létrehozva</th>
                    <th>Módosítva</th>
                    <th data-priority="2">Műveletek</th>
                </tr>
                </thead>
            </table>
        @else
            <div class="alert alert-warning" role="alert">
                Nincs jogosultságod a gyártók megtekintésére.
            </div>
        @endif
    </div>


    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="adminForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Gyártó szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="brand_id" name="id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Név</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-check mb-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="brand_status"
                                name="status"
                                value="active"
                            >
                            <label class="form-check-label" for="brand_status">
                                Állapot (Aktív)
                            </label>
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
                tableId: 'adminTable',
                modalId: 'adminModal',
                formId: 'adminForm',
                addButtonId: 'addButton',
                dataUrl: '{{ route('admin.brands.data') }}',
                storeUrl: '{{ route('admin.brands.store') }}',
                destroyUrl: '{{ url('/admin/gyartok/') }}',
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                columns: [
                    { data: 'id' },
                    { data: 'title' },
                    { data: 'status' },
                    { data: 'created' },
                    { data: 'updated' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                model: 'brands',
                fillFormFn: (row) => {
                    document.getElementById('id').value = row.id;
                    document.getElementById('title').value = row.title;
                    document.getElementById('status').value = row.status;
                }
            });
        });
    </script>
@endsection
