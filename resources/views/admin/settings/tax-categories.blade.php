@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Pénzügyi beállítások / Adó osztályok</h1>
            <button class="btn btn-success" id="addTax"><i class="fas fa-plus me-1"></i> Új adóosztály</button>
        </div>

        <table class="table table-bordered" id="taxesTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Adó érték (%)</th>
                <th>Adó megnevezés</th>
                <th>Adó leírás</th>
                <th>Műveletek</th>
            </tr>
            </thead>
        </table>
    </div>


    <!-- Modális ablak -->
    <div class="modal fade" id="taxModal" tabindex="-1" aria-labelledby="taxModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="taxForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="taxModalLabel">Adó osztály szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="tax_id" name="id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Adó érték (%)</label>
                            <input type="number" class="form-control" id="tax_value" name="tax_value" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Adó megnevezés</label>
                            <input type="text" class="form-control" id="tax_name" name="tax_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Adó leírás</label>
                            <input type="text" class="form-control" id="tax_description" name="tax_description">
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
                tableId: 'taxesTable',
                modalId: 'taxModal',
                formId: 'taxForm',
                addButtonId: 'addTax',
                dataUrl: '{{ route('admin.tax-categories.data') }}',
                storeUrl: '{{ route('admin.tax-categories.store') }}',
                destroyUrl: '{{ url('/admin/beallitasok/ado-osztalyok/') }}',
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                columns: [
                    { data: 'id' },
                    { data: 'tax_value' },
                    { data: 'tax_name' },
                    { data: 'tax_description' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                fillFormFn: (row) => {
                    document.getElementById('id').value = row.id;
                    document.getElementById('tax_value').value = row.tax_value;
                    document.getElementById('tax_name').value = row.tax_name;
                    document.getElementById('tax_description').value = row.tax_description;
                }
            });
        });
    </script>
@endsection
