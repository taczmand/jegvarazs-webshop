@extends('layouts.admin')

@section('content')
    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Rendszer / Felhasználók</h2>
            @if(auth('admin')->user()->can('create-user'))
                <button class="btn btn-success" id="addUser"><i class="fas fa-plus me-1"></i> Új felhasználó</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-users'))

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

                <table class="table table-bordered display responsive nowrap" id="usersTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Név</th>
                        <th>E-mail cím</th>
                        <th>Létrehozva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a felhasználók megtekintésére.
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="userForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>

                    <div class="modal-body">
                        <ul class="nav nav-tabs" id="userTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#basic" type="button">Felhasználói adatok</button></li>
                        </ul>

                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="basic">
                                <input type="hidden" id="user_id" name="id">
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Felhasználói név*</label>
                                            <input type="text" class="form-control" name="name" id="name" name="name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">E-mail cím*</label>
                                            <input type="email" class="form-control" name="email" id="email" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Jelszó*</label>
                                            <input type="password" class="form-control" name="password" id="password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Jelszó újra*</label>
                                            <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" required>
                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Jogosultságok beállítása</label>
                                            <div id="permission_checkboxes" style="max-height: 300px; overflow-y: auto" class="">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" id="saveUser">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        const userModalDOM = document.getElementById('userModal');
        const userModal = new bootstrap.Modal(userModalDOM);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        $(document).ready(function() {
            const table = $('#usersTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.settings.users.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'created_at' },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });

            // Szűrők beállítása

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            // Új felhasználó létrehozása modal megjelenítése
            $('#addUser').on('click', async function () {
                try {
                    resetForm('Új felhasználó létrehozása');

                    const permissions = await getPermissions();

                    renderPermissions(permissions);

                } catch (error) {
                    showToast(error, 'danger');
                }
                userModal.show();
            });

            $('#usersTable').on('click', '.edit', async function () {

                resetForm('Felhasználó szerkesztése');

                const row_data = $('#usersTable').DataTable().row($(this).parents('tr')).data();
                $('#user_id').val(row_data.id);

                // Jogosultságok betöltése
                const user = await fetch(`${window.appConfig.APP_URL}admin/felhasznalo/${row_data.id}`);
                const userData = await user.json();

                $('#name').val(userData.name);
                $('#email').val(userData.email);
                $('#password').val(''); // Jelszó mező ürítése
                $('#password_confirmation').val(''); // Jelszó megerősítés mező ürítése


                try {
                    const permissions = await getPermissions();
                    renderPermissions(permissions, userData.permissions.map(p => p.name));
                } catch (error) {
                    showToast(error, 'danger');
                }
                userModal.show();
            });


            $('#saveUser').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('userForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const userId = $('#user_id').val();

                let url = '{{ route('admin.settings.users.store') }}';
                let method = 'POST';  // Alapértelmezett metódus

                if (userId) {
                    url = `${window.appConfig.APP_URL}admin/felhasznalok/${userId}`;  // update URL, ha van ID
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
                        userModal.hide();
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

            $('#usersTable').on('click', '.delete', async function () {
                const row_data = $('#usersTable').DataTable().row($(this).parents('tr')).data();
                const userId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd a felhasználót?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/felhasznalok') }}/${userId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Felhasználó sikeresen törölve!', 'success');
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a felhasználó törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt a felhasználó törlésekor', 'danger');
                }
            });


            function renderPermissions(permissions, assigned_permissions = []) {
                const container = document.getElementById('permission_checkboxes');
                container.innerHTML = ''; // előző checkboxok törlése

                Object.entries(permissions).forEach(([groupName, groupPermissions], groupIndex) => {
                    const groupWrapper = document.createElement('div');
                    groupWrapper.classList.add('mb-3', 'border', 'p-2', 'rounded');

                    // Csoport checkbox az összeshez
                    const groupCheckboxId = `group_permission_${groupIndex}`;
                    const groupHeader = document.createElement('div');
                    groupHeader.classList.add('form-check', 'mb-2');

                    groupHeader.innerHTML = `
                        <input class="form-check-input group-toggle" type="checkbox" id="${groupCheckboxId}">
                        <label class="form-check-label fw-bold" for="${groupCheckboxId}">
                            ${groupName}
                        </label>
                    `;

                    groupWrapper.appendChild(groupHeader);

                    // Jogosultságok a csoportban
                    groupPermissions.forEach(permission => {
                        const checkboxId = `permission_${permission.id}`;
                        const wrapper = document.createElement('div');
                        wrapper.classList.add('form-check', 'ms-3');

                        const isChecked = assigned_permissions.includes(permission.name) ? 'checked' : '';

                        wrapper.innerHTML = `
                            <input class="form-check-input group-permission-${groupIndex}" type="checkbox" value="${permission.name}" id="${checkboxId}" name="permissions[]" ${isChecked}>
                            <label class="form-check-label" for="${checkboxId}">
                                ${permission.label}
                            </label>
                        `;

                        groupWrapper.appendChild(wrapper);
                    });

                    container.appendChild(groupWrapper);

                    // Toggle event
                    setTimeout(() => {
                        const groupToggle = document.getElementById(groupCheckboxId);
                        const checkboxes = groupWrapper.querySelectorAll(`.group-permission-${groupIndex}`);

                        // Kezdeti állapot: ha minden pipa bent van, a fő checkbox is legyen pipás
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        groupToggle.checked = allChecked;

                        // Fő checkbox kattintás
                        groupToggle.addEventListener('change', function () {
                            checkboxes.forEach(cb => cb.checked = this.checked);
                        });

                        // Egyedi checkbox változás -> csoport checkbox frissítése
                        checkboxes.forEach(cb => {
                            cb.addEventListener('change', function () {
                                const allChecked = Array.from(checkboxes).every(c => c.checked);
                                groupToggle.checked = allChecked;
                            });
                        });
                    });
                });
            }


            async function getPermissions() {
                try {
                    const response = await fetch(`{{ url('/admin/felhasznalok/permissions') }}`);
                    if (!response.ok) {
                        throw new Error('Hiba a jogosultságok lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Jogosultság lekérdezési hiba:', error);
                    return [];
                }
            }

            function resetForm(title = null) {
                $('#userForm')[0].reset();
                $('#permission-checkboxes').empty();
                $('#userModalLabel').text(title);

                // Visszakapcsolás az első tabra
                const firstTab = new bootstrap.Tab(document.querySelector('#userTab .nav-link[data-bs-target="#basic"]'));
                firstTab.show();
            }
        });
    </script>
@endsection
