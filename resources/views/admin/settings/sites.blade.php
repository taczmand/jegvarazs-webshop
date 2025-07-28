@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Tartalomkezelés / Telephelyek</h2>
            @if(auth('admin')->user()->can('create-site'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új telephely</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-sites'))

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

                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Megnevezés</th>
                        <th>Ország</th>
                        <th>Irányítószám</th>
                        <th>Város</th>
                        <th>Cím</th>
                        <th>Telefonszám</th>
                        <th>E-mail cím</th>
                        <th>Létrehozva</th>
                        <th>Módosítva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Nincs jogosultságod a telephelyek megtekintéséhez.
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
                        <h5 class="modal-title" id="adminModalLabel">Telephely szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="site_id" name="id">
                        <div class="mb-3">
                            <label for="site_name" class="form-label">Megnevezés*</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="site_country" class="form-label">Ország*</label>
                            <select name="site_country" class="form-control w-100" id="site_country">
                                @foreach(config('countries') as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="site_zip" class="form-label">Irányítószám</label>
                            <input type="text" class="form-control" id="site_zip" name="site_zip">
                        </div>

                        <div class="mb-3">
                            <label for="site_city" class="form-label">Város</label>
                            <input type="text" class="form-control" id="site_city" name="site_city">
                        </div>
                        <div class="mb-3">
                            <label for="site_address" class="form-label">Cím</label>
                            <input type="text" class="form-control" id="site_address" name="site_address">
                        </div>
                        <div class="mb-3">
                            <label for="site_phone" class="form-label">Telefonszám</label>
                            <input type="text" class="form-control" id="site_phone" name="site_phone">
                        </div>
                        <div class="mb-3">
                            <label for="site_email" class="form-label">E-mail cím</label>
                            <input type="email" class="form-control" id="site_email" name="site_email">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success save-btn" id="saveSite">Mentés</button>
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
                    url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.settings.sites.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'country' },
                    { data: 'zip_code' },
                    { data: 'city' },
                    { data: 'address_line' },
                    { data: 'phone' },
                    { data: 'email' },
                    { data: 'created' },
                    { data: 'updated' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            // Szűrők beállítása

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            // Új letöltési tétel létrehozása modal megjelenítése
            $('#addButton').on('click', async function () {
                resetForm('Új telephely létrehozása');
                adminModal.show();
            });

            // Telephely szerkesztése

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Telephely szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                $('#site_id').val(row_data.id);
                $('#site_name').val(row_data.name);
                $('#site_country').val(row_data.country);
                $('#site_zip').val(row_data.zip_code);
                $('#site_city').val(row_data.city);
                $('#site_address').val(row_data.address_line);
                $('#site_phone').val(row_data.phone);
                $('#site_email').val(row_data.email);


                adminModal.show();
            });

            // Telephely mentése

            $('#saveSite').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const site_id = $('#site_id').val();

                let url = '{{ route('admin.settings.sites.store') }}';
                let method = 'POST';  // Alapértelmezett metódus

                if (site_id) {
                    url = `${window.appConfig.APP_URL}admin/telephelyek/${site_id}`;  // update URL, ha van ID
                    formData.append('_method', 'PUT');  // PUT metódus jelzése
                }

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success(response) {
                        showToast(response.message || 'Sikeres!', 'success');
                        table.ajax.reload();
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

            // Telephely törlése
            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const site_id = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt a telephelyet?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/telephelyek') }}/${site_id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Telephely sikeresen törölve!', 'success');
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a telephely törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt a telephely törlésekor', 'danger');
                }
            });

            function resetForm(title = null) {
                $('#adminModalForm')[0].reset();
                $('#adminModalLabel').text(title);
            }
        });
    </script>
@endsection
