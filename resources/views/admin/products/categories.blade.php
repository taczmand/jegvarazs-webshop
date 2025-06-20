@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Termékek / Kategóriák</h1>
            <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új kategória</button>
        </div>

        <table class="table table-bordered" id="adminTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Kategórianév</th>
                <th>Leírás</th>
                <th>Szülőkategória</th>
                <th>Állapot</th>
                <th>Létrehozva</th>
                <th>Módosítva</th>
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
                        <h5 class="modal-title" id="adminModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="cat_id" name="id">
                        <div class="mb-3">
                            <label for="cat_title" class="form-label">Kategórianév</label>
                            <input type="text" class="form-control" id="cat_title" name="cat_title" required>
                        </div>
                        <div class="mb-3">
                            <label for="cat_description" class="form-label">Leírás</label>
                            <textarea class="form-control" id="cat_description" name="cat_description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="cat_parent_id" class="form-label">Szülőkategória</label>
                            <select class="form-select" id="cat_parent_id" name="cat_parent_id"></select>
                        </div>
                        <div class="form-check mb-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="cat_status"
                                name="status"
                                value="active"
                            >
                            <label class="form-check-label" for="cat_status">
                                Állapot (Aktív)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-btn" id="saveCategory">Mentés</button>
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
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.categories.data') }}',
                columns: [
                    { data: 'id' },
                    { data: 'title' },
                    { data: 'description' },
                    { data: 'parent_title' },
                    { data: 'status' },
                    { data: 'created' },
                    { data: 'updated' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            // Új kategória létrehozása modal megjelenítése
            $('#addButton').on('click', async function () {
                try {
                    resetForm('Új kategória létrehozása');

                    const allCategories = await getAllCategories();
                    renderCategories(allCategories);

                } catch (error) {
                    showToast(error, 'danger');
                }
                adminModal.show();
            });

            // Kategória szerkesztése

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Kategória szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                $('#cat_id').val(row_data.id);
                $('#cat_title').val(row_data.title);
                $('#cat_description').val(row_data.description);

                const statusCheckbox = $('#cat_status');
                const statusLabel = $('label[for="cat_status"]');

                if (row_data.status === 'active') {
                    statusCheckbox.prop('checked', true);
                    statusLabel.text('Állapot (Aktív)');
                } else {
                    statusCheckbox.prop('checked', false);
                    statusLabel.text('Állapot (Inaktív)');
                }
                const allCategories = await getAllCategories();
                renderCategories(allCategories, row_data.id, row_data.parent_title);
                adminModal.show();
            });

            // Kategória mentése

            $('#saveCategory').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const catId = $('#cat_id').val();

                let url = '{{ route('admin.categories.store') }}';
                let method = 'POST';  // Alapértelmezett metódus

                if (catId) {
                    url = `/admin/kategoriak/${catId}`;  // update URL, ha van ID
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

            // Kategória törlése
            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const catId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt a kategóriát?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/kategoriak') }}/${catId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Kategória sikeresen törölve!', 'success');
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a kategória törlésekor';
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

            function renderCategories(categories, currentCategoryId = null, currentParentTitle = null) {

                const select = $('#cat_parent_id');
                select.empty();
                select.append('<option value=""></option>');
                categories.forEach(category => {
                    if (category.id !== currentCategoryId) {
                        const selected = category.title === currentParentTitle ? 'selected' : '';
                        select.append(`<option value="${category.id}" ${selected}>${category.title}</option>`);
                    }
                });
            }

            async function getAllCategories() {
                try {
                    const response = await fetch(`{{ url('/admin/kategoriak/fetch') }}`);
                    if (!response.ok) {
                        throw new Error('Hiba a kategóriák lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Kategória hiba:', error);
                    return [];
                }
            }

            function resetForm(title = null) {
                $('#cat_warning').hide();
                $('#adminModalForm')[0].reset();
                $('#cat_id').val('');
                $('#adminModalLabel').text(title);
            }
        });
    </script>
@endsection
