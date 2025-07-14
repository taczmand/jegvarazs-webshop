@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h2 class="h5 text-primary mb-0"><i class="fa-solid fa-file-lines text-primary me-2"></i> Tartalomkezelés / Letöltések</h2>
            @if(auth('admin')->user()->can('create-download'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új letöltés</button>
            @endif
        </div>

        @if(auth('admin')->user()->can('view-downloads'))

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
                    <select class="form-select filter-input" data-column="4">
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
                    <th data-priority="1">Fájlnév</th>
                    <th>Elérési út</th>
                    <th>Leírás</th>
                    <th>Állapot</th>
                    <th>Létrehozva</th>
                    <th>Módosítva</th>
                    <th data-priority="2">Műveletek</th>
                </tr>
                </thead>
            </table>
        @else
            <div class="alert alert-warning" role="alert">
                Nincs jogosultságod a letöltések megtekintésére.
            </div>
        @endif
    </div>


    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="adminModalForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Letöltés szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="download_id" name="id">
                        <div class="mb-3">
                            <label for="file_name" class="form-label">Név</label>
                            <input type="text" class="form-control" id="file_name" name="file_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="file_description" class="form-label">Leírás</label>
                            <textarea class="form-control" id="file_description" name="file_description"></textarea>
                        </div>
                        <div class="mb-3 d-none" id="exist_file_area">
                            <a href="" id="exist_file_url" target="_blank">Fájl megtekintése</a>
                        </div>
                        <div class="mb-3 d-none" id="empty_file_area">
                            <label for="file_upload" class="form-label">Fájl feltöltése</label>
                            <input type="file" class="form-control" id="file_upload" name="file_upload" required>
                        </div>
                        <div class="form-check mb-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="download_status"
                                name="status"
                                value="active"
                            >
                            <label class="form-check-label" for="download_status">
                                Állapot (Aktív)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-btn" id="saveDownload">Mentés</button>
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
                ajax: '{{ route('admin.settings.downloads.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'file_name' },
                    { data: 'file_path' },
                    { data: 'file_description' },
                    { data: 'status' },
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

                resetForm('Új letöltés létrehozása');
                $('#empty_file_area').removeClass('d-none');

                adminModal.show();
            });

            // Letöltés szerkesztése

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Letöltés szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                $('#download_id').val(row_data.id);
                $('#file_name').val(row_data.file_name);
                $('#file_description').val(row_data.file_description);

                const statusCheckbox = $('#download_status');
                const statusLabel = $('label[for="download_status"]');

                if (row_data.status === 'Aktív') {
                    statusCheckbox.prop('checked', true);
                    statusLabel.text('Állapot (Aktív)');
                } else {
                    statusCheckbox.prop('checked', false);
                    statusLabel.text('Állapot (Inaktív)');
                }

                $('#exist_file_area').removeClass('d-none');
                $('#exist_file_url').attr("href", window.appConfig.APP_URL + "storage/" + row_data.file_path);
                adminModal.show();
            });

            // Letöltés mentése

            $('#saveDownload').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const downloadId = $('#download_id').val();

                let url = '{{ route('admin.settings.downloads.store') }}';
                let method = 'POST';  // Alapértelmezett metódus

                if (downloadId) {
                    url = `${window.appConfig.APP_URL}admin/letoltesek/${downloadId}`;  // update URL, ha van ID
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

            // Letöltés törlése
            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const downloadId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt a letöltést?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/letoltesek') }}/${downloadId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Letöltés sikeresen törölve!', 'success');
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a letöltés törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt a kategória törlésekor', 'danger');
                }
            });

            function resetForm(title = null) {
                $('#empty_file_area').addClass('d-none');
                $('#exist_file_area').addClass('d-none');
                $('#adminModalForm')[0].reset();
                $('#adminModalLabel').text(title);
            }
        });
    </script>
@endsection
