@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyviteli folyamatok / E-mail automatizáció</h2>
            @if(auth('admin')->user()->can('create-category'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új automatizáció</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-leads'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>
                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="E-mail cím" class="filter-input form-control" data-column="1">
                    </div>
                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Sablon" class="filter-input form-control" data-column="2">
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">E-mail cím</th>
                        <th data-priority="3">Sablon</th>
                        <th data-priority="4">Periódus értéke</th>
                        <th data-priority="5">Periódus</th>
                        <th>Utolsó küldés</th>
                        <th>Létrehozva</th>
                        <th>Módosítva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod az automatizációk megtekintéséhez.
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
                        <h5 class="modal-title" id="adminModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="automated_id" name="id">
                        <div class="mb-3">
                            <label for="email_address" class="form-label">E-mail cím*</label>
                            <input type="text" id="email_address" name="email_address" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="email_template" class="form-label">E-mail sablon*</label>
                            <select id="email_template" name="email_template" class="form-select">
                                @foreach(config('automated_email_templates') as $slug => $template)
                                    <option value="{{ $template['title'] }}">{{ $template['title'] }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="mb-3">
                            <label for="frequency_interval" class="form-label">Periódus értéke*</label>
                            <input type="text" id="frequency_interval" name="frequency_interval" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="frequency_unit" class="form-label">Periódus*</label>
                            <select id="frequency_unit" name="frequency_unit" class="form-select">
                                <option value="naponta">naponta</option>
                                <option value="hetente">hetente</option>
                                <option value="havonta">havonta</option>
                                <option value="évente">évente</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-btn" id="saveLead">Mentés</button>
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
                ajax: '{{ route('admin.automated-emails.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'email_address' },
                    { data: 'email_template' },
                    { data: 'frequency_interval' },
                    { data: 'frequency_unit' },
                    { data: 'last_sent_at' },
                    { data: 'created_at' },
                    { data: 'updated_at' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            // Szűrők beállítása

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            // Új automatizáció létrehozása modal megjelenítése
            $('#addButton').on('click', async function () {
                try {
                    resetForm('Új automatizáció létrehozása');


                } catch (error) {
                    showToast(error, 'danger');
                }
                adminModal.show();
            });

            // Automatizáció szerkesztése

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Automatizáció szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                $('#automated_id').val(row_data.id);

                $('#email_address').val(row_data.email_address);
                $('#email_template').val(row_data.email_template);
                $('#frequency_interval').val(row_data.frequency_interval);
                $('#frequency_unit').val(row_data.frequency_unit);

                adminModal.show();
            });

            // Automatizáció mentése

            $('#saveLead').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const automatedId = $('#automated_id').val();

                let url = '{{ route('admin.automated-emails.store') }}';
                let method = 'POST';  // Alapértelmezett metódus

                if (automatedId) {
                    url = `${window.appConfig.APP_URL}admin/automatizacio/${automatedId}`;  // update URL, ha van ID
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

            // Automatizáció törlése
            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const automatedId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd az automatizációt?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/automatizacio') }}/${automatedId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Automatizáció sikeresen törölve!', 'success');
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt az automatizáció törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt a automatizáció törlésekor', 'danger');
                }
            });

            function resetForm(title = null) {
                $('#adminModalForm')[0].reset();
                $('#automated_id').val('');
                $('#adminModalLabel').text(title);
            }
        });
    </script>
@endsection
