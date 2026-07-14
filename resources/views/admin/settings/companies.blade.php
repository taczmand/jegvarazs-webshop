@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Tartalomkezelés / Cégek</h2>
            @if(auth('admin')->user()->can('create-company'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új cég</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-companies'))

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
                        <input type="text" placeholder="Adószám" class="filter-input form-control" data-column="2">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Város" class="filter-input form-control" data-column="6">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="10">
                            <option value="">Állapot (összes)</option>
                            <option value="active">Aktív</option>
                            <option value="inactive">Inaktív</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Név</th>
                        <th>Adószám</th>
                        <th>EU VAT</th>
                        <th>Ország</th>
                        <th>Irányítószám</th>
                        <th>Város</th>
                        <th>Cím</th>
                        <th>E-mail</th>
                        <th>Telefon</th>
                        <th>Állapot</th>
                        <th>Alapértelmezett</th>
                        <th>Létrehozva</th>
                        <th>Módosítva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Nincs jogosultságod a cégek megtekintéséhez.
                </div>
            @endif
        </div>
    </div>


    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="adminModalForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Cég szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="company_id" name="id">

                        <div class="mb-3">
                            <label for="name" class="form-label">Név*</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label for="tax_number" class="form-label">Adószám</label>
                                <input type="text" class="form-control" id="tax_number" name="tax_number">
                            </div>
                            <div class="col-6">
                                <label for="vat_number" class="form-label">EU VAT</label>
                                <input type="text" class="form-control" id="vat_number" name="vat_number">
                            </div>
                        </div>

                        <div class="row g-2 mt-1">
                            <div class="col-4">
                                <label for="country" class="form-label">Ország</label>
                                <input type="text" class="form-control" id="country" name="country" maxlength="2" value="HU">
                            </div>
                            <div class="col-4">
                                <label for="zip_code" class="form-label">Irányítószám</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code">
                            </div>
                            <div class="col-4">
                                <label for="city" class="form-label">Város</label>
                                <input type="text" class="form-control" id="city" name="city">
                            </div>
                        </div>

                        <div class="mb-3 mt-2">
                            <label for="address_line" class="form-label">Cím</label>
                            <input type="text" class="form-control" id="address_line" name="address_line">
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="col-6">
                                <label for="phone" class="form-label">Telefon</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                        </div>

                        <div class="mb-3 mt-2">
                            <label for="bank_account" class="form-label">Bankszámlaszám</label>
                            <input type="text" class="form-control" id="bank_account" name="bank_account">
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label for="status" class="form-label">Állapot</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active">Aktív</option>
                                    <option value="inactive">Inaktív</option>
                                </select>
                            </div>
                            <div class="col-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="is_default" name="is_default">
                                    <label class="form-check-label" for="is_default">Alapértelmezett</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success save-btn" id="saveCompany">Mentés</button>
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

        $(document).ready(function() {
            const table = $('#adminTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.settings.companies.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'tax_number' },
                    { data: 'vat_number' },
                    { data: 'country' },
                    { data: 'zip_code' },
                    { data: 'city' },
                    { data: 'address_line' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'status' },
                    { data: 'is_default' },
                    { data: 'created' },
                    { data: 'updated' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                columnDefs: [
                    {
                        targets: 11,
                        render: function (data) {
                            return data ? 'igen' : 'nem';
                        }
                    }
                ],
            });

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            $('#addButton').on('click', async function () {
                resetForm('Új cég létrehozása');
                adminModal.show();
            });

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Cég szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                $('#company_id').val(row_data.id);
                $('#name').val(row_data.name);
                $('#tax_number').val(row_data.tax_number);
                $('#vat_number').val(row_data.vat_number);
                $('#country').val(row_data.country || 'HU');
                $('#zip_code').val(row_data.zip_code);
                $('#city').val(row_data.city);
                $('#address_line').val(row_data.address_line);
                $('#email').val(row_data.email);
                $('#phone').val(row_data.phone);
                $('#bank_account').val(row_data.bank_account);
                $('#status').val(row_data.status || 'active');
                $('#is_default').prop('checked', !!row_data.is_default);

                adminModal.show();
            });

            $('#saveCompany').on('click', function (e) {
                e.preventDefault();

                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const companyId = $('#company_id').val();

                let url = '{{ route('admin.settings.companies.store') }}';
                let method = 'POST';

                if (companyId) {
                    url = `${window.appConfig.APP_URL}admin/cegek/${companyId}`;
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
                const companyId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt a céget?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/cegek') }}/${companyId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Cég sikeresen törölve!', 'success');
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a cég törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt a cég törlésekor', 'danger');
                }
            });

            function resetForm(title = null) {
                $('#adminModalForm')[0].reset();
                $('#adminModalLabel').text(title);
                $('#company_id').val('');
                $('#country').val('HU');
                $('#status').val('active');
                $('#is_default').prop('checked', false);
            }
        });
    </script>
@endsection
