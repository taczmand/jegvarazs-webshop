@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Raktározás / Raktárak</h2>
            @if(auth('admin')->user()->can('create-warehouse'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új raktár</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-warehouses'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Megnevezés" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Kód" class="filter-input form-control" data-column="2">
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Megnevezés</th>
                        <th>Kód</th>
                        <th>Ország</th>
                        <th>Irányítószám</th>
                        <th>Város</th>
                        <th>Cím</th>
                        <th>Aktív</th>
                        <th>Megjegyzés</th>
                        <th>Létrehozva</th>
                        <th>Módosítva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Nincs jogosultságod a raktárak megtekintéséhez.
                </div>
            @endif
        </div>
    </div>


    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="adminModalForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Raktár szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="warehouse_id" name="id">

                        <div class="mb-3">
                            <label for="name" class="form-label">Megnevezés*</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="code" class="form-label">Kód</label>
                            <input type="text" class="form-control" id="code" name="code">
                        </div>

                        <div class="mb-3">
                            <label for="country" class="form-label">Ország*</label>
                            <select name="country" class="form-control w-100" id="country">
                                @foreach(config('countries') as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="zip_code" class="form-label">Irányítószám</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code">
                        </div>

                        <div class="mb-3">
                            <label for="city" class="form-label">Város</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>

                        <div class="mb-3">
                            <label for="address_line" class="form-label">Cím</label>
                            <input type="text" class="form-control" id="address_line" name="address_line">
                        </div>

                        <div class="mb-3">
                            <label for="is_active" class="form-label">Aktív</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1">Igen</option>
                                <option value="0">Nem</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">Megjegyzés</label>
                            <textarea class="form-control" id="note" name="note" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success save-btn" id="saveWarehouse">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">

        const adminModalDOM = document.getElementById('adminModal');
        const adminModal = new bootstrap.Modal(adminModalDOM);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function resetForm(title) {
            $('#adminModalLabel').text(title);
            $('#warehouse_id').val('');
            $('#name').val('');
            $('#code').val('');
            $('#country').val('HU');
            $('#zip_code').val('');
            $('#city').val('');
            $('#address_line').val('');
            $('#is_active').val('1');
            $('#note').val('');
        }

        $(document).ready(function() {
            const table = $('#adminTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.warehouses.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'code' },
                    { data: 'country' },
                    { data: 'zip_code' },
                    { data: 'city' },
                    { data: 'address_line' },
                    { data: 'is_active' },
                    { data: 'note' },
                    { data: 'created' },
                    { data: 'updated' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            $('#addButton').on('click', async function () {
                resetForm('Új raktár létrehozása');
                adminModal.show();
            });

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Raktár szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                $('#warehouse_id').val(row_data.id);
                $('#name').val(row_data.name);
                $('#code').val(row_data.code);
                $('#country').val(row_data.country);
                $('#zip_code').val(row_data.zip_code);
                $('#city').val(row_data.city);
                $('#address_line').val(row_data.address_line);
                $('#is_active').val(row_data.is_active ? '1' : '0');
                $('#note').val(row_data.note || '');

                adminModal.show();
            });

            $('#saveWarehouse').on('click', function (e) {
                e.preventDefault();

                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const warehouse_id = $('#warehouse_id').val();

                let url = '{{ route('admin.warehouses.store') }}';
                let method = 'POST';

                if (warehouse_id) {
                    url = `${window.appConfig.APP_URL}admin/raktarozas/raktarak/${warehouse_id}`;
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success(response) {
                        showToast(response.message || 'Sikeres!', 'success');
                        table.ajax.reload(null, false);
                        adminModal.hide();
                    },
                    error(xhr) {
                        let msg = 'Hiba!';
                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');
                    },
                    complete: () => {
                        $(this).html(originalSaveButtonHtml).prop('disabled', false);
                    }
                });

            });

            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const warehouse_id = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt a raktárat?')) return;

                $.ajax({
                    url: `{{ url('/admin/raktarozas/raktarak') }}/${warehouse_id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        showToast(response.message || 'Raktár sikeresen törölve!', 'success');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON?.message || 'Hiba történt a törlés közben!', 'danger');
                    }
                });
            });
        });

    </script>
@endsection
