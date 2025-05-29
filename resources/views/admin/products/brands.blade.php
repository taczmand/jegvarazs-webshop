@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Termékek / Gyártók</h1>
            <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új gyártó</button>
        </div>

        <table class="table table-bordered" id="adminTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Név</th>
                <th>Slug</th>
                <th>Állapot</th>
                <th>Létrehozva</th>
                <th>Módosítva</th>
                <th>Műveletek</th>
            </tr>
            </thead>
        </table>
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
                        <div class="mb-3">
                            <label for="name" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" disabled>
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
                    { data: 'slug' },
                    { data: 'status' },
                    { data: 'created' },
                    { data: 'updated' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                fillFormFn: (row) => {
                    document.getElementById('id').value = row.id;
                    document.getElementById('title').value = row.title;
                    document.getElementById('slug').value = row.slug;
                    document.getElementById('status').value = row.status;
                }
            });
        });
    </script>
@endsection
