@extends('layouts.admin')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Pénzügyi beállítások / Adó osztályok</h1>
    <div class="container">
        <button class="btn btn-success mb-3" id="addProduct">Új adóosztály</button>
        <table class="table table-bordered" id="taxes-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Adó</th>
                <th>Cím</th>
                <th>Leírás</th>
                <th>Műveletek</th>
            </tr>
            </thead>
        </table>
    </div>

    <!-- Modális ablak -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="productForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Termék</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="product_id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Név</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Ár</label>
                            <input type="number" class="form-control" id="price" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">


        $(document).ready(function() {
            var table = $('#taxes-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.tax-categories.data') }}',
                columns: [
                    { data: 'id' },
                    { data: 'tax_value' },
                    { data: 'tax_name' },
                    { data: 'tax_description' },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });

            // Új termék hozzáadása
            $('#addProduct').click(function() {
                $('#productForm')[0].reset();
                $('#product_id').val('');
                $('#productModal').modal('show');
            });

            // Termék szerkesztése
            $('#taxes-table').on('click', '.edit', function() {
                var id = $(this).data('id');
                $.get('/products/' + id, function(data) {
                    $('#product_id').val(data.id);
                    $('#name').val(data.name);
                    $('#price').val(data.price);
                    $('#productModal').modal('show');
                });
            });

            // Termék mentése
            $('#productForm').submit(function(e) {
                e.preventDefault();
                var id = $('#product_id').val();
                var url = id ? '/products/' + id : '/products';
                var method = id ? 'PUT' : 'POST';
                $.ajax({
                    url: url,
                    type: method,
                    data: {
                        name: $('#name').val(),
                        price: $('#price').val(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        $('#productModal').modal('hide');
                        table.ajax.reload();
                    }
                });
            });

            // Termék törlése
            $('#taxes-table').on('click', '.delete', function() {
                if (confirm('Biztosan törölni szeretnéd?')) {
                    var id = $(this).data('id');
                    $.ajax({
                        url: '/products/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function() {
                            table.ajax.reload();
                        }
                    });
                }
            });
        });
    </script>
@endsection
