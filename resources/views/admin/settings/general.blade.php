@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Webshop beállítások / Általános</h1>
        </div>

        <table class="table table-bordered" id="adminTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Kulcs</th>
                <th>Érték</th>
                <th>Műveletek</th>
            </tr>
            </thead>
        </table>
    </div>


    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="adminModalForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="taxModalLabel">Általános beállítások szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="general_id" name="id">
                        <div class="mb-3">
                            <label for="basic_key" class="form-label">Kulcs</label>
                            <input type="text" class="form-control" id="basic_key" name="key" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="basic_value" class="form-label">Érték</label>
                            <input type="text" class="form-control" id="basic_value" name="value">
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
                formId: 'adminModalForm',
                addButtonId: null,
                dataUrl: '{{ route('admin.settings.general.data') }}',
                storeUrl: '{{ url('/admin/beallitasok/altalanos') }}',
                destroyUrl: null,
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                columns: [
                    { data: 'id' },
                    { data: 'key' },
                    { data: 'value' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                fillFormFn: (row) => {
                    document.getElementById('id').value = row.id;
                    document.getElementById('basic_key').value = row.key;
                    document.getElementById('basic_value').value = row.value;
                }
            });
        });
    </script>
@endsection
