@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Termékek / Gyártók</h2>
            @if(auth('admin')->user()->can('create-brand'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új gyártó</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-brands'))

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
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Név</th>
                        <th>Logo</th>
                        <th>Állapot</th>
                        <th>Létrehozva</th>
                        <th>Módosítva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a gyártók megtekintésére.
                </div>
            @endif
        </div>
    </div>


    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="adminForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Gyártó szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="brand_id" name="id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Név*</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3 d-none" id="exist_image">
                            <img src="" id="exist_file_image" class="img-fluid mt-2" style="max-width: 100%; max-height: 200px;">
                        </div>
                        <div class="mb-3" id="empty_file_area">
                            <label for="file_upload" class="form-label">Fájl feltöltése</label>
                            <input type="file" class="form-control" id="file_upload" name="file_upload">
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
                        <button type="submit" class="btn btn-primary save-btn" id="saveBrand">Mentés</button>
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
                ajax: '{{ route('admin.brands.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'title' },
                    { data: 'logo' },
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

            // Új gyártó tétel létrehozása modal megjelenítése
            $('#addButton').on('click', async function () {

                resetForm('Új gyártó létrehozása');
                adminModal.show();
            });

            // Gyártó szerkesztése

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Gyártó szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                $('#brand_id').val(row_data.id);
                $('#title').val(row_data.title);


                const statusCheckbox = $('#brand_status');
                const statusLabel = $('label[for="brand_status"]');

                if (row_data.status === 'Aktív') {
                    statusCheckbox.prop('checked', true);
                    statusLabel.text('Állapot (Aktív)');
                } else {
                    statusCheckbox.prop('checked', false);
                    statusLabel.text('Állapot (Inaktív)');
                }

                if (row_data.logo) {
                    $('#exist_image').removeClass('d-none');
                    $('#exist_file_image').attr("src", window.appConfig.APP_URL + "storage/" + row_data.logo);
                } else {
                    $('#exist_image').addClass('d-none');
                }


                adminModal.show();
            });

            // Gyártó mentése

            $('#saveBrand').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('adminForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const brandId = $('#brand_id').val();

                let url = '{{ route('admin.brands.store') }}';
                let method = 'POST';  // Alapértelmezett metódus

                if (brandId) {
                    url = `${window.appConfig.APP_URL}admin/gyartok/${brandId}`;  // update URL, ha van ID
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

            // Gyártó törlése
            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const brandId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt a gyártót?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/gyartok') }}/${brandId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Gyártó sikeresen törölve!', 'success');
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a gyártó törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt a gyártó törlésekor', 'danger');
                }
            });

            function resetForm(title = null) {
                $('#exist_image').addClass('d-none');
                $('#adminForm')[0].reset();
                $('#adminModalLabel').text(title);
                $('#brand_id').val('');
            }
        });
    </script>
@endsection
