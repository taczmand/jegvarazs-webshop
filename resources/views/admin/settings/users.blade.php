@extends('layouts.admin')

@section('content')
    <div class="container p-0">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Beállítások / Felhasználók</h1>
            <button class="btn btn-success" id="addUser"><i class="fas fa-plus me-1"></i> Új felhasználó</button>
        </div>

        <table class="table table-bordered" id="usersTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Név</th>
                <th>E-mail cím</th>
                <th>Szerepkörök</th>
                <th>Létrehozva</th>
                <th>Műveletek</th>
            </tr>
            </thead>
        </table>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="userForm" enctype="multipart/form-data">
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
                                            <input type="password" class="form-control" name="repassword" id="repassword" required>
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
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.settings.users.data') }}',
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'roles' },
                    { data: 'created_at' },
                    { data: 'action', orderable: false, searchable: false }
                ]
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
