@extends('layouts.admin')

@section('content')
    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Ügyviteli folyamatok / Munkalapok</h1>
            <div>
                <button class="btn btn-dark" id="showCalendar"><i class="fas fa-calendar me-1"></i> Naptár</button>
                <button class="btn btn-dark d-none" id="hideCalendar"><i class="fa-solid fa-table"></i> Táblázat</button>
                <button id="addButton" class="btn btn-success"><i class="fas fa-plus me-1"></i> Új munkalap</button>
            </div>
        </div>


        <div class="d-none" id="calendarContainer">

            <div class="calendar-nav">
                <button class="btn btn-light" id="prevWeek">⬅ Előző hét</button>
                <h2 id="weekLabel">Heti naptár</h2>
                <button class="btn btn-light" id="nextWeek">Következő hét ➡</button>
            </div>
            <table id="calendar">
                <thead>
                <tr>
                    <th>Hétfő</th>
                    <th>Kedd</th>
                    <th>Szerda</th>
                    <th>Csütörtök</th>
                    <th>Péntek</th>
                    <th>Szombat</th>
                    <th>Vasárnap</th>
                </tr>
                <tr>
                    @foreach(range(1, 7) as $i)
                        <th>
                            <button class="btn btn-sm btn-info new_contract_from_calendar mb-1">
                                <i class="fas fa-plus me-1"></i> Új szerződés
                            </button>
                            <button class="btn btn-sm btn-success new_worksheet_from_calendar">
                                <i class="fas fa-plus me-1"></i> Új munkalap
                            </button>
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                <!-- JavaScript tölti ki -->
                </tbody>
            </table>
            <div id="calendarLoader" style="display: none; text-align: center; margin: 1rem 0;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Betöltés...</span>
                </div>
            </div>
        </div>

        <div id="worksheet_table">
            <table class="table table-bordered" id="adminTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Ügyfélnév</th>
                    <th>Város</th>
                    <th>Munka</th>
                    <th>Szerelő</th>
                    <th>Állapot</th>
                    <th>Szerződés</th>
                    <th>Készítette</th>
                    <th>Létrehozva</th>
                    <th>Műveletek</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>


    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="adminModalForm" action="" enctype="multipart/form-data">
                <input type="hidden" id="worksheet_id" name="worksheet_id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Munkalap szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">

                        <ul class="nav nav-tabs" id="productTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#basic" type="button">Alapadatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#products" type="button">Termékek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#images" type="button">Képek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#worksheet" type="button">Munkalap</button></li>
                        </ul>

                        <div class="tab-content mt-3">

                            <!-- Alapadatok tab -->

                            <div class="tab-pane fade show active" id="basic">
                                <table class="table table-bordered worksheet-basic-table">
                                    <tbody>
                                    <tr>
                                        <td class="w-25">Munka megnevezése</td>
                                        <td><input type="text" class="form-control" id="work_name" name="work_name" required></td>
                                    </tr>
                                    <tr>
                                        <td class="w-25">Munka dátuma</td>
                                        <td><input type="date" class="form-control" id="installation_date" name="installation_date" required></td>
                                    </tr>
                                    <tr>
                                        <td class="w-25">Ügyfélnév</td>
                                        <td><input type="text" class="form-control" id="contact_name" name="contact_name" required></td>
                                    </tr>
                                    <tr>
                                        <td>Ország</td>
                                        <td>
                                            <select name="contact_country" class="form-control w-100" id="contact_country">
                                                @foreach(config('countries') as $code => $name)
                                                    <option value="{{ $code }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Irányítószám</td>
                                        <td><input type="text" class="form-control" id="contact_zip_code" name="contact_zip_code"></td>
                                    </tr>
                                    <tr>
                                        <td>Város</td>
                                        <td><input type="text" class="form-control" id="contact_city" name="contact_city"></td>
                                    </tr>
                                    <tr>
                                        <td>Cím</td>
                                        <td><input type="text" class="form-control" id="contact_address_line" name="contact_address_line"></td>
                                    </tr>
                                    <tr>
                                        <td>Telefonszám</td>
                                        <td><input type="text" class="form-control" id="contact_phone" name="contact_phone"></td>
                                    </tr>
                                    <tr>
                                        <td>E-mail cím</td>
                                        <td><input type="email" class="form-control" id="contact_email" name="contact_email"></td>
                                    </tr>
                                    <tr>
                                        <td>Szerelő hozzárendelése</td>
                                        <td>
                                            <select name="worker_id" class="form-control w-100" id="worker_id" name="worker_id">
                                                <option value=""></option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">
                                                        {{ $user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Szerződés hozzárendelése</td>
                                        <td>
                                            <select name="contract_id" class="form-control w-100" id="contract_id" name="contract_id">
                                                <option value=""></option>
                                                @foreach($contracts as $contract)
                                                    <option value="{{ $contract->id }}">
                                                        {{ $contract->name }} (anyja neve: {{ $contract->mothers_name }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Munka leírása</td>
                                        <td><textarea class="form-control" id="contact_description" name="contact_description" rows="3"></textarea></td>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Termékek tab-->

                            <div class="tab-pane fade" id="products">
                                <div style="max-height: 300px; overflow-y: auto">
                                    <table class="table table-bordered" id="productManagerTable">
                                        <thead>
                                        <tr>
                                            <th>Kiválasztás</th>
                                            <th>Termék</th>
                                            <th>Darabszám</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <!-- Termékek betöltése itt -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Képek tab-->

                            <div class="tab-pane fade" id="images">
                                <h5>Adattábla képek</h5>
                                <div class="mb-3">
                                    <label class="form-label">Új képek feltöltése adattábláról</label>
                                    <input type="file" class="form-control" name="new_photos_to_datatable[]" multiple accept="image/*">
                                </div>

                                <div id="worksheetDataTablePhotos" class="mt-3"></div>

                                <hr>

                                <h5>Telepítési tanúsítvány képek</h5>
                                <div class="mb-3">
                                    <label class="form-label">Új képek feltöltése telepítési tanúsítványról</label>
                                    <input type="file" class="form-control" name="new_photos_to_certificate[]" multiple accept="image/*">
                                </div>

                                <div id="worksheetCertificatePhotos" class="mt-3"></div>

                                <hr>

                                <h5>Szerelés képek</h5>
                                <div class="mb-3">
                                    <label class="form-label">Új képek feltöltése szerelésről</label>
                                    <input type="file" class="form-control" name="new_photos_to_install[]" multiple accept="image/*">
                                </div>

                                <div id="worksheetInstallPhotos" class="mt-3"></div>
                            </div>

                            <!-- Munkalap tab-->

                            <div class="tab-pane fade" id="worksheet">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Fizetés típusa?</label>
                                    <select id="payment_method" name="payment_method" class="form-control">
                                        <option value="cash">Készpénz</option>
                                        <option value="transfer">Átutalás</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_amount" class="form-label">Átvett készpénz összege:</label>
                                    <input type="number" name="payment_amount" id="payment_amount" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="pipe" class="form-label">Mennyi plusz csövet használtál?*</label>
                                    <input type="text" name="extra_data[pipe]" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="console" class="form-label">Milyen konzolt használtál?*</label>
                                    <input type="text" name="extra_data[console]" class="form-control">
                                </div>
                                <label for="worker_report">Szerelő megjegyzése:</label>
                                <div class="mb-3">
                                    <textarea name="worker_report" id="worker_report" rows="3" class="form-control"></textarea>
                                </div>
                            </div>




                        </div>
                    </div>
                    <div class="modal-footer d-flex align-items-center gap-2">
                        <label for="work_status" class="mb-0">Állapot:</label>

                        <select class="form-control form-control-sm w-auto" name="work_status" id="work_status">
                            <option value="Szerelésre vár">Szerelésre vár</option>
                            <option value="Felszerelve">Felszerelve</option>
                        </select>

                        <button type="submit" class="btn btn-success btn-sm ms-auto" id="saveWorksheet">Mentés</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Mégse</button>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">

        $(document).ready(function() {

            const calendarBody = document.querySelector('#calendar tbody');
            const weekLabel = document.getElementById('weekLabel');

            let currentMonday = new Date();
            currentMonday.setDate(currentMonday.getDate() - (currentMonday.getDay() + 6) % 7); // hétfőre igazítás

            function formatDate(date) {
                return date.toISOString().split('T')[0];
            }

            function getWeekDays(monday) {
                const days = [];
                for (let i = 0; i < 7; i++) {
                    const d = new Date(monday);
                    d.setDate(monday.getDate() + i);
                    days.push(d);
                }
                return days;
            }

            async function fetchWorksheets(startDate, endDate) {
                const url = new URL('{{ route("admin.worksheets.byweek") }}', window.location.origin);
                url.searchParams.append('start_date', startDate);
                url.searchParams.append('end_date', endDate);

                try {
                    const response = await fetch(url);
                    if (!response.ok) {
                        console.error('Hiba az adatok betöltésekor:', response.statusText);
                        return [];
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Fetch error:', error);
                    return [];
                }
            }

            async function renderCalendar() {

                // 🔹 Loader megjelenítése
                document.getElementById('calendarLoader').style.display = 'block';

                calendarBody.innerHTML = '';

                const days = getWeekDays(currentMonday);
                const startStr = formatDate(days[0]);
                const endStr = formatDate(days[6]);

                if (weekLabel) {
                    weekLabel.textContent = `Heti naptár: ${startStr} - ${endStr}`;
                }

                const worksheets = await fetchWorksheets(startStr, endStr);

                // 🔹 Loader elrejtése
                document.getElementById('calendarLoader').style.display = 'none';

                // 🔸 Meglévő <th>-ek dátum hozzárendelése (Blade-ből jöttek)
                const headerThs = document.querySelectorAll('#calendar thead tr:nth-child(2) th');
                headerThs.forEach((th, i) => {
                    const dateStr = formatDate(days[i]);
                    th.dataset.date = dateStr;

                    // 🔹 Előző dátumcímke eltávolítása, ha van
                    const oldLabel = th.querySelector('.calendar-date-label');
                    if (oldLabel) {
                        oldLabel.remove();
                    }

                    // 🔹 Új címke hozzáadása
                    const dateLabel = document.createElement('div');
                    dateLabel.textContent = formatDayLabel(days[i]);
                    dateLabel.classList.add('calendar-date-label');
                    th.prepend(dateLabel);

                    // Gombokra is rátesszük a dátumot
                    const contractBtn = th.querySelector('.new_contract_from_calendar');
                    const worksheetBtn = th.querySelector('.new_worksheet_from_calendar');

                    if (contractBtn) contractBtn.dataset.date = dateStr;
                    if (worksheetBtn) worksheetBtn.dataset.date = dateStr;
                });

                // 🔸 Tartalom cellák
                const tr = document.createElement('tr');
                days.forEach(day => {
                    const td = document.createElement('td');
                    td.dataset.date = formatDate(day);

                    const dayWorksheets = worksheets.filter(w => w.installation_date === td.dataset.date);

                    dayWorksheets.forEach((w, index) => {
                        const div = document.createElement('div');

                        if (w.worker_name) {
                            div.innerHTML += `
                                <i class="fa-solid fa-users-gear"></i>
                                ${w.worker_name}
                                <br>
                            `;
                        }

                        let statusIcon = '';
                        if (w.work_status === 'Szerelésre vár') {
                            statusIcon = '<i class="fas fa-tools text-danger me-1" title="Szerelésre vár"></i>';
                        } else if (w.work_status === 'Felszerelve') {
                            statusIcon = '<i class="fas fa-check-circle text-success me-1" title="Felszerelve"></i>';
                        } else {
                            statusIcon = '<i class="fas fa-question-circle text-muted me-1" title="Ismeretlen státusz"></i>';
                        }

                        div.innerHTML += `
                            ${statusIcon}<strong>${w.work_name}</strong><br>
                            ${w.name}<br>
                            ${w.city}
                        `;

                        div.classList.add('worksheet-entry');

                        if (index < dayWorksheets.length - 1) {
                            const separator = document.createElement('hr');
                            separator.classList.add('worksheet-separator');
                            td.appendChild(div);
                            td.appendChild(separator);
                        } else {
                            td.appendChild(div);
                        }
                    });


                    tr.appendChild(td);
                });

                calendarBody.appendChild(tr);
            }

            function formatDayLabel(date) {
                // 'MM-DD' formátumban
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day   = String(date.getDate()).padStart(2, '0');
                return `${month}-${day}`;
            }

            function changeWeek(offset) {
                currentMonday.setDate(currentMonday.getDate() + offset * 7);
                renderCalendar();
            }

            $('#prevWeek').click(function() {
                changeWeek(-1);
            });
            $('#nextWeek').click(function() {
                changeWeek(1);
            });

            $(document).on('click', '.new_contract_from_calendar', function () {
                const contract_date = $(this).data('date');
                if (!contract_date) return;

                const url = `${window.appConfig.APP_URL}admin/szerzodesek?make_contract=true&installation_date=${contract_date}`;
                window.open(url, '_blank');
            });

            $(document).on('click', '.new_worksheet_from_calendar', function () {
                const worksheet_date = $(this).data('date');
                showCreateForm(worksheet_date);
            });

            $('#showCalendar').click(function() {
                $('#hideCalendar').removeClass('d-none');
                $('#calendarContainer').removeClass('d-none');
                $('#showCalendar').addClass('d-none');
                $('#worksheet_table').addClass('d-none');
                renderCalendar();
            });

            $('#hideCalendar').click(function() {
                $('#hideCalendar').addClass('d-none');
                $('#calendarContainer').addClass('d-none');
                $('#worksheet_table').removeClass('d-none');
                $('#showCalendar').removeClass('d-none');
            });

            const adminModalDOM = document.getElementById('adminModal');
            const adminModal = new bootstrap.Modal(adminModalDOM);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');


            const table = $('#adminTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.worksheets.data') }}',
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'city' },
                    { data: 'work_name' },
                    { data: 'worker_name' },
                    { data: 'work_status_icon', name: 'work_status_icon', orderable: false, searchable: false  },
                    { data: 'contract_id' },
                    { data: 'creator_name' },
                    { data: 'created' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            // Új munkalap létrehozása modal megjelenítése
            $('#addButton').on('click', async function () {
                showCreateForm();
            });

            function showCreateForm(installation_date = null) {
                try {
                    resetForm('Új munkalap létrehozása');
                    if (installation_date) {
                        $('#installation_date').val(installation_date);
                    }
                    loadProducts();

                    $('.worksheet-basic-table').find('input, select, textarea').prop('disabled', false);

                } catch (error) {
                    showToast(error, 'danger');
                }
                adminModal.show();
            }

            // Munkalap szerkesztése

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Munkalap szerkesztése');
                //$('.offer-contact-table').find('input, select, textarea').prop('disabled', true);


                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();

                const worksheet_data = await loadWorksheetWithAttachedData(row_data.id);

                const worksheet = worksheet_data || {};
                const worksheet_products = worksheet.products || [];

                // Alapadatok

                $('#worksheet_id').val(worksheet.id);
                $('#work_name').val(worksheet.work_name);
                $('#installation_date').val(worksheet.installation_date);
                $('#contact_name').val(worksheet.name);
                $('#contact_name').val(worksheet.name);
                $('#contact_country').val(worksheet.country);
                $('#contact_zip_code').val(worksheet.zip_code);
                $('#contact_city').val(worksheet.city);
                $('#contact_address_line').val(worksheet.address_line);
                $('#contact_description').val(worksheet.description);
                $('#contact_phone').val(worksheet.phone);
                $('#contact_email').val(worksheet.email);
                $('#worker_id').val(worksheet.worker_id);
                $('#contract_id').val(worksheet.contract_id);
                $('#work_status').val(worksheet.work_status);
                $('#payment_method').val(worksheet.payment_method);
                $('#payment_amount').val(worksheet.payment_amount);

                const extraData = worksheet.data || {};

                Object.entries(extraData).forEach(([key, value]) => {
                    const $input = $(`[name="extra_data[${key}]"]`);
                    if ($input.length) {
                        $input.val(value);
                    }
                });


                // Termékek
                loadProducts(worksheet_products);

                // Képek
                renderPhotos(worksheet.photos);

                adminModal.show();
            });

            // Munkalap mentése

            $('#saveWorksheet').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                let url = '{{ route('admin.worksheet.store') }}';
                let method = 'POST';


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

            // Munkalap törlése
            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const worksheet_id = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt a munkalapot?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/munkalap-torlese') }}/${worksheet_id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Munkalap sikeresen törölve!', 'success');
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a munkalap törlésekor';
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

            function loadProducts(products = []) {
                const productManagerTable = $('#productManagerTable tbody');
                productManagerTable.empty();

                fetch(`/admin/munkalapok/munkalap-termekek`)
                    .then(response => response.json())
                    .then(data => {
                        // Átalakítjuk a meglévő products tömböt egy gyors lookup objektummá
                        const selectedProducts = {};
                        products.forEach(p => {
                            selectedProducts[p.id] = p.quantity ?? 1; // ha nincs quantity, akkor 1
                        });

                        data.forEach(category => {
                            const categoryRow = `
                                <tr class="table-secondary">
                                    <td colspan="3"><strong>${category.title}</strong></td>
                                </tr>
                            `;
                            productManagerTable.append(categoryRow);

                            category.products.forEach(item => {
                                const isChecked = selectedProducts.hasOwnProperty(item.id);
                                const quantity = isChecked ? selectedProducts[item.id] : 1;

                                const row = `
                                    <tr>
                                        <td>
                                            <input
                                                type="checkbox"
                                                name="products[${item.id}][selected]"
                                                value="1"
                                                ${isChecked ? 'checked' : ''}
                                            >
                                        </td>
                                        <td>${item.title}</td>
                                        <td>
                                            <input
                                                type="number"
                                                class="form-control"
                                                name="products[${item.id}][qty]"
                                                value="${quantity}"
                                                step="1"
                                            >
                                        </td>
                                    </tr>
                                `;
                                productManagerTable.append(row);
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Hiba a termékek betöltésekor:', error);
                    });
            }

            function renderPhotos(photos = []) {
                const containerForDatatablePhotos = $('#worksheetDataTablePhotos');
                const containerForCertificatePhotos = $('#worksheetCertificatePhotos');
                const containerForInstallPhotos = $('#worksheetInstallPhotos');

                containerForDatatablePhotos.empty();
                containerForCertificatePhotos.empty();
                containerForInstallPhotos.empty();

                if (!photos.length) {
                    containerForDatatablePhotos.append('<p class="text-muted">Nincs feltöltött adattábla kép.</p>');
                    containerForCertificatePhotos.append('<p class="text-muted">Nincs feltöltött tanúsítvány kép.</p>');
                    containerForInstallPhotos.append('<p class="text-muted">Nincs feltöltött szerelés kép.</p>');
                    return;
                }

                const containers = {
                    'Adattábla': containerForDatatablePhotos,
                    'Telepítési tanúsítvány': containerForCertificatePhotos,
                    'Szerelés': containerForInstallPhotos
                };

                const tables = {
                    'Adattábla': createPhotoTable(),
                    'Telepítési tanúsítvány': createPhotoTable(),
                    'Szerelés': createPhotoTable()
                };

                photos.forEach(photo => {
                    const type = photo.image_type;
                    const container = containers[type];
                    const table = tables[type];

                    if (!container || !table) return;

                    const description = photo.description || '';

                    const row = $(`
                        <tr data-photo-id="${photo.id}">
                            <td>
                                <a href="${window.appConfig.APP_URL}admin/worksheets/${photo.image_path}" target="_blank">
                                    <img src="${window.appConfig.APP_URL}admin/worksheets/${photo.image_path}" alt="${description}" class="img-thumbnail" style="width: 100px;">
                                </a>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger delete-photo" data-photo-id="${photo.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);

                    table.find('tbody').append(row);
                });

                Object.entries(tables).forEach(([type, table]) => {
                    const container = containers[type];
                    if (container) {
                        container.append(table);
                    }
                });

                // --- Kép törlése
                $('.delete-photo').off('click').on('click', function () {
                    const photoId = $(this).data('photo-id');
                    const row = $(this).closest('tr');

                    if (!confirm('Biztosan törölni szeretnéd ezt a képet?')) return;

                    $.ajax({
                        url: `/admin/munkalapok/delete-photo`,
                        method: 'DELETE',
                        data: { id: photoId, _token: $('meta[name="csrf-token"]').attr('content') },
                        success: () => {
                            row.remove();
                            showToast('Kép törölve', 'success');
                        },
                        error: () => showToast('Nem sikerült törölni a képet', 'danger')
                    });
                });

                function createPhotoTable() {
                    return $(`
                        <div style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-bordered table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Feltöltött képek</th>
                                        <th>Törlés</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    `);
                }
            }




            async function loadWorksheetWithAttachedData(id) {
                try {
                    const response = await fetch(`{{ url('/admin/munkalapok/adatok') }}/${id}`, {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    if (!response.ok) {
                        throw new Error('Hiba a termékek lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Lekérdezési hiba:', error);
                    return [];
                }
            }

            function resetForm(title = null) {
                $('#adminModalLabel').text(title);

                const form = document.getElementById('adminModalForm');
                if (form) {
                    form.reset(); // Alap HTML input mezők ürítése

                    // Egyéb dolgok resetelése:
                    $(form).find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
                    $(form).find('select').val('').trigger('change'); // select2 kompatibilitás
                    $(form).find('textarea').val('');
                    $(form).find('input[type="file"]').val(''); // fájlmezők törlése

                    // Ha szeretnél, eltüntethetsz előzőleg betöltött képeket is, pl.:
                    $('#worksheetDataTablePhotos').empty();
                    $('#worksheetCertificatePhotos').empty();
                    $('#worksheetInstallPhotos').empty();
                }
            }
        });
    </script>
@endsection
