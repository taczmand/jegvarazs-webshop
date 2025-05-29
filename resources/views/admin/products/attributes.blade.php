@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Termékek / Egyedi tulajdonságok</h1>
            <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új tulajdonság</button>
        </div>

        <table class="table table-bordered" id="adminTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Név</th>
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
                        <h5 class="modal-title" id="adminModalLabel">Tulajdonság szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="attribute_id" name="id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Név</label>
                            <input type="text" class="form-control" id="name" name="name" required>
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
                dataUrl: '{{ route('admin.attributes.data') }}',
                storeUrl: '{{ route('admin.attributes.store') }}',
                destroyUrl: '{{ url('/admin/tulajdonsagok/') }}',
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'created' },
                    { data: 'updated' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                fillFormFn: (row) => {
                    document.getElementById('id').value = row.id;
                    document.getElementById('name').value = row.name;
                }
            });
        });
    </script>
@endsection
