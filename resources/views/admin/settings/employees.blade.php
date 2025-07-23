@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h2 class="h5 text-primary mb-0"><i class="fa-solid fa-file-lines text-primary me-2"></i> Tartalomkezelés / Munkatársak</h2>
            @if(auth('admin')->user()->can('create-employee'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új munkatárs</button>
            @endif
        </div>

        @if(auth('admin')->user()->can('view-employees'))

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
                    <input type="text" placeholder="E-mail cím" class="filter-input form-control" data-column="2">
                </div>

            </div>

            <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                <thead>
                <tr>
                    <th>ID</th>
                    <th data-priority="1">Név</th>
                    <th>E-mail</th>
                    <th>Telefonszám</th>
                    <th>Beosztás</th>
                    <th>Profilkép</th>
                    <th>Létrehozva</th>
                    <th>Módosítva</th>
                    <th data-priority="2">Műveletek</th>
                </tr>
                </thead>
            </table>
        @else
            <div class="alert alert-warning" role="alert">
                Nincs jogosultságod a munkatársak megtekintésére.
            </div>
        @endif
    </div>


    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="adminForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Munkatárs szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="employee_id" name="id">
                        <div class="mb-3">
                            <label for="employee_name" class="form-label">Név</label>
                            <input type="text" class="form-control" id="employee_name" name="employee_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="employee_email" class="form-label">E-mail cím</label>
                            <input type="email" class="form-control" id="employee_email" name="employee_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="employee_phone" class="form-label">Telefonszám</label>
                            <input type="text" class="form-control" id="employee_phone" name="employee_phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="employee_position" class="form-label">Beosztás</label>
                            <input type="text" class="form-control" id="employee_position" name="employee_position" required>
                        </div>
                        <div class="mb-3 d-none" id="exist_image">
                            <img src="" id="exist_file_image" class="img-fluid mt-2" style="max-width: 100%; max-height: 200px;">
                        </div>
                        <div class="mb-3" id="empty_file_area">
                            <label for="file_upload" class="form-label">Fájl feltöltése</label>
                            <input type="file" class="form-control" id="file_upload" name="file_upload">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-btn" id="saveEmployee">Mentés</button>
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
                ajax: '{{ route('admin.settings.employees.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'position' },
                    { data: 'profile_photo_path' },
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

            // Új tétel létrehozása modal megjelenítése
            $('#addButton').on('click', async function () {

                resetForm('Új munkatárs létrehozása');
                adminModal.show();
            });

            // Munkatárs szerkesztése

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Munkatárs szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                $('#employee_id').val(row_data.id);
                $('#employee_name').val(row_data.name);
                $('#employee_email').val(row_data.email);
                $('#employee_phone').val(row_data.phone);
                $('#employee_position').val(row_data.position);

                if (row_data.profile_photo_path) {
                    $('#exist_image').removeClass('d-none');
                    $('#exist_file_image').attr("src", window.appConfig.APP_URL + "storage/" + row_data.profile_photo_path);
                } else {
                    $('#exist_image').addClass('d-none');
                }
                adminModal.show();
            });

            // Munkatárs mentése

            $('#saveEmployee').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('adminForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const employeeId = $('#employee_id').val();

                let url = '{{ route('admin.settings.employees.store') }}';
                let method = 'POST';  // Alapértelmezett metódus

                if (employeeId) {
                    url = `${window.appConfig.APP_URL}admin/munkatarsak/${employeeId}`;  // update URL, ha van ID
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

            // Munkatárs törlése
            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const downloadId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd a munkatárst?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/munkatarsak') }}/${downloadId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Munkatárs sikeresen törölve!', 'success');
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a munkatárs törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt a munkatárs törlésekor', 'danger');
                }
            });

            function resetForm(title = null) {
                $('#exist_image').addClass('d-none');
                $('#adminForm')[0].reset();
                $('#adminModalLabel').text(title);
            }
        });
    </script>
@endsection
