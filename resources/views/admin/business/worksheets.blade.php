@extends('layouts.admin')

@section('content')
    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyviteli folyamatok / Munkalapok</h2>
            <div>
                <!--<button class="btn btn-dark" id="showCalendar"><i class="fas fa-calendar me-1"></i> Naptár</button>-->
                <button class="btn btn-dark d-none" id="hideCalendar"><i class="fa-solid fa-table"></i> Táblázat</button>
                @if(auth('admin')->user()->can('create-worksheet'))
                    <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új munkalap</button>
                @endif
            </div>
        </div>


        <!--<div class="d-none rounded-xl bg-white shadow-lg p-4" id="calendarContainer">

            <div class="calendar-nav">
                <button class="btn btn-light" id="prevWeek">⬅ Előző hét</button>
                <h5 id="weekLabel">Heti naptár</h5>
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

                </tbody>
            </table>
            <div id="calendarLoader" style="display: none; text-align: center; margin: 1rem 0;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Betöltés...</span>
                </div>
            </div>
        </div>-->

        <div class="rounded-xl bg-white shadow-lg p-4" id="worksheetTableArea">
            @if(auth('admin')->user()->can('view-worksheets'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center" id="worksheetFilters">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Ügyfélnév" class="filter-input form-control" data-column="2">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="4">
                            <option value="">Összes típus</option>
                            <option value="Karbantartás">Karbantartás</option>
                            <option value="Szerelés">Szerelés</option>
                            <option value="Felmérés">Felmérés</option>
                        </select>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Munkalap adatok pl.: hitelre" class="filter-input form-control" data-column="5">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="7">
                            <option value="">Állapot (összes)</option>
                            <option value="Folyamatban">Folyamatban</option>
                            <option value="Kész">Kész</option>
                            <option value="Lezárva">Lezárva</option>
                        </select>
                    </div>

                </div>

                <div id="worksheet_table">
                    <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th data-priority="1">Dátum</th>
                            <th data-priority="0">Ügyfélnév</th>
                            <th data-priority="2">Város</th>
                            <th data-priority="3">Munka típusa</th>
                            <th>Munkalap adatok</th>
                            <th data-priority="5">Szerelők</th>
                            <th data-priority="4">Állapot</th>
                            <th>Szerződés</th>
                            <th>Készítette</th>
                            <th>Létrehozva</th>
                            <th>Látta</th>
                            <th data-priority="2">Műveletek</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a munkalapok megtekintésére.
                </div>
            @endif
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
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#workers" type="button">Szerelők</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#products" type="button">Termékek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#local_images" type="button">Helyszíni képek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#datatable_images" type="button">Adattábla képek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#certificate_images" type="button">Tanúsítvány képek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#install_images" type="button">Szerelés képek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#billing_files" type="button">Számlák</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#worksheet" type="button">Munkalap</button></li>
                        </ul>

                        <div class="tab-content mt-3">
                            @php
                                $display = auth('admin')->user()->can('view-own-worksheets') ? 'none' : '';
                            @endphp



                                <!-- Alapadatok tab -->

                            <div class="tab-pane fade show active" id="basic">
                                <table class="table table-bordered worksheet-basic-table" style="display: {{ $display }}">
                                    <tbody>
                                    <tr>
                                        <td class="w-25">Munka megnevezése</td>
                                        <td><input type="text" class="form-control" id="work_name" name="work_name" required></td>
                                    </tr>
                                    <tr>
                                        <td class="w-25">Munka típusa</td>
                                        <td>
                                            <select name="work_type" id="work_type" class="form-control">
                                                <option value="Karbantartás">Karbantartás</option>
                                                <option value="Szerelés">Szerelés</option>
                                                <option value="Felmérés">Felmérés</option>
                                            </select>
                                        </td>
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

                            <!-- Szerelők tab-->

                            <div class="tab-pane fade" id="workers">
                                <div style="max-height: 300px; overflow-y: auto">
                                    <table class="table table-bordered" id="workerManagerTable" style="display: {{ $display }}">
                                        <thead>
                                        <tr>
                                            <th>Kiválasztás</th>
                                            <th>Szerelő neve</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <!-- Szerelők betöltése itt -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Termékek tab-->

                            <div class="tab-pane fade" id="products">
                                <input type="text" class="form-control mb-3" id="productSearch" placeholder="Keresés a termékek között...">
                                <div style="max-height: 300px; overflow-y: auto">
                                    <table class="table table-bordered" id="productManagerTable" style="display: {{ $display }}">
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

                            <!-- Helyszíni felmérés képek tab-->

                            <div class="tab-pane fade" id="local_images">
                                <h5>Helyszíni felmérés képek</h5>
                                <div class="mb-3">
                                    <label class="form-label">Új képek feltöltése a helyszínen</label>
                                    <input type="file" class="form-control" name="new_photos_to_local[]" multiple accept="image/*">
                                </div>

                                <div id="worksheetLocalPhotos" class="mt-3"></div>
                            </div>

                            <!-- Adattábla képek tab-->

                            <div class="tab-pane fade" id="datatable_images">
                                <h5>Adattábla képek</h5>
                                <div class="mb-3">
                                    <label class="form-label">Új képek feltöltése adattábláról</label>
                                    <input type="file" class="form-control" name="new_photos_to_datatable[]" multiple accept="image/*">
                                </div>

                                <div id="worksheetDataTablePhotos" class="mt-3"></div>
                            </div>

                            <!-- Telepítési tanúsítvány képek tab-->

                            <div class="tab-pane fade" id="certificate_images">
                                <h5>Telepítési tanúsítvány képek</h5>
                                <div class="mb-3">
                                    <label class="form-label">Új képek feltöltése telepítési tanúsítványról</label>
                                    <input type="file" class="form-control" name="new_photos_to_certificate[]" multiple accept="image/*">
                                </div>

                                <div id="worksheetCertificatePhotos" class="mt-3"></div>
                            </div>

                            <!-- Szerelés képek tab-->

                            <div class="tab-pane fade" id="install_images">
                                <h5>Szerelés képek</h5>
                                <div class="mb-3">
                                    <label class="form-label">Új képek feltöltése szerelésről</label>
                                    <input type="file" class="form-control" name="new_photos_to_install[]" multiple accept="image/*">
                                </div>

                                <div id="worksheetInstallPhotos" class="mt-3"></div>
                            </div>

                            <!-- Számlák tab-->

                            <div class="tab-pane fade" id="billing_files">
                                <h5>Számlák</h5>
                                <div class="mb-3">
                                    <label class="form-label">Új számlák feltöltése</label>
                                    <input type="file" class="form-control" name="new_billings[]" multiple accept="*">
                                </div>

                                <div id="worksheetBillings" class="mt-3"></div>
                            </div>

                            <!-- Munkalap tab-->

                            <div class="tab-pane fade" id="worksheet">

                                <div id="worksheet_szereles" class="d-none">
                                    <div class="mb-3">
                                        <label for="pipe" class="form-label">Mennyi plusz csövet használtál?*</label>
                                        <input type="text" name="extra_data[pipe]" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label for="console" class="form-label">Milyen konzolt használtál?*</label>
                                        <input type="text" name="extra_data[console]" class="form-control">
                                    </div>
                                </div>

                                <div id="worksheet_karbantartas" class="d-none">
                                    <div class="mb-3">
                                        <label for="cleaning_type" class="form-label">Tisztítás típusa*</label>
                                        <select id="cleaning_type" name="extra_data[cleaning_type]" class="form-control">
                                            <option value="">Válassz</option>
                                            <option value="basic_clean">Alaptisztítás</option>
                                            <option value="full_clean">Teljes mosás</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="device_qty" class="form-label">Hány darab készülék?*</label>
                                        <input type="text" name="extra_data[device_qty]" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="self_installation" class="form-label">Saját telepítés?*</label>
                                        <select id="self_installation" name="extra_data[self_installation]" class="form-control">
                                            <option value="">Válassz</option>
                                            <option value="igen">Igen</option>
                                            <option value="nem">Nem</option>
                                        </select>
                                    </div>
                                </div>

                                <div id="worksheet_felmeres" class="d-none">
                                    <div class="mb-3">
                                        <label for="exist_contract" class="form-label">Szerződéskötés történt?*</label>
                                        <select id="exist_contract" name="extra_data[exist_contract]" class="form-control">
                                            <option value="">Válassz</option>
                                            <option value="igen">Igen</option>
                                            <option value="nem">Nem</option>
                                            <option value="hitel">Hitelre lesz</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Fizetés módja?</label>
                                    <select id="payment_method" name="payment_method" class="form-control">
                                        <option value="cash">Készpénz</option>
                                        <option value="transfer">Átutalás</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="maintenance_payment_amount" class="form-label">Átvett készpénz összege:</label>
                                    <input type="number" name="maintenance_payment_amount" id="maintenance_payment_amount" class="form-control">
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
                            <option value="Folyamatban">Folyamatban</option>
                            <option value="Kész">Kész</option>
                            <option value="Lezárva">Lezárva</option>
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

            const urlParams = new URLSearchParams(window.location.search);
            const searchId = urlParams.get('id');
            const makeWorksheet = urlParams.get('make_worksheet');
            const installationDate = urlParams.get('installation_date');

            const adminModalDOM = document.getElementById('adminModal');
            const adminModal = new bootstrap.Modal(adminModalDOM);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const table = $('#adminTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.worksheets.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'installation_date' },
                    { data: 'name' },
                    { data: 'city' },
                    { data: 'work_type' },
                    { data: 'data' },
                    { data: 'worker_name' },
                    { data: 'work_status' },
                    //{ data: 'work_status_icon', name: 'work_status_icon', orderable: false, searchable: false  },
                    { data: 'contract_id' },
                    { data: 'creator_name' },
                    { data: 'created' },
                    { data: 'viewed_by' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            if (searchId) {
                $('.filter-input[data-name="id"]').val(searchId);
                const input = $('.filter-input[data-name="id"]');
                const i = input.attr('data-column');
                const v = input.val();
                table.columns(i).search(v).draw();

                editWorksheet(searchId);
            }

            if (makeWorksheet) {
                showCreateForm(installationDate);
            }

            // Szűrők beállítása

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            $('#work_type').change(function() {
                const workType = $(this).val();
                renderWorkTypeFields(workType);
            });

            function renderWorkTypeFields(workType) {

                $('#worksheet_szereles').addClass('d-none');
                $('#worksheet_karbantartas').addClass('d-none');
                $('#worksheet_felmeres').addClass('d-none');

                if ("Szerelés" === workType) {
                    $('#worksheet_szereles').removeClass('d-none');
                }
                if ("Karbantartás" === workType) {
                    $('#worksheet_karbantartas').removeClass('d-none');
                }
                if ("Felmérés" === workType) {
                    $('#worksheet_felmeres').removeClass('d-none');
                }
            }

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

                sendViewRequest("worksheet", row_data.id);
                table.ajax.reload(null, false);

                editWorksheet(row_data.id);
            });

            async function editWorksheet(id) {
                const worksheet_data = await loadWorksheetWithAttachedData(id);

                const worksheet = worksheet_data || {};
                const worksheet_products = worksheet.products || [];
                const worksheet_workers = worksheet.workers || [];

                // Alapadatok

                $('#worksheet_id').val(worksheet.id);
                $('#work_name').val(worksheet.work_name);
                $('#work_type').val(worksheet.work_type);
                $('#installation_date').val(worksheet.installation_date);
                $('#contact_name').val(worksheet.name);
                $('#contact_country').val(worksheet.country);
                $('#contact_zip_code').val(worksheet.zip_code);
                $('#contact_city').val(worksheet.city);
                $('#contact_address_line').val(worksheet.address_line);
                $('#contact_description').val(worksheet.description);
                $('#contact_phone').val(worksheet.phone);
                $('#contact_email').val(worksheet.email);
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

                // Szerelők
                loadWorkers(worksheet_workers);

                // Termékek
                loadProducts(worksheet_products);

                // Képek
                renderPhotos(worksheet.photos);

                // Munkalap specifikus mezők
                renderWorkTypeFields(worksheet.work_type);

                adminModal.show();
            }

            $(document).on('input', '#productSearch', function () {
                const searchValue = $(this).val().toLowerCase();

                $('#productManagerTable tbody tr').each(function () {
                    const row = $(this);

                    // csak a termék sorokra keresünk, a kategória sort meghagyjuk
                    if (!row.hasClass('table-secondary')) {
                        const productName = row.find('label').text().toLowerCase();

                        if (productName.includes(searchValue)) {
                            row.show();
                        } else {
                            row.hide();
                        }
                    }
                });
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
                        renderCalendar();
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

            function loadWorkers(workers = []) {
                const workerManagerTable = $('#workerManagerTable tbody');
                workerManagerTable.empty();

                fetch(`${window.appConfig.APP_URL}admin/fetch-felhasznalok`)
                    .then(response => response.json())
                    .then(data => {
                        // Átalakítjuk a meglévő workers tömböt egy gyors lookup objektummá
                        const selectedWorkers = {};
                        workers.forEach(w => {
                            selectedWorkers[w.id] = true; // csak a kiválasztottakat tároljuk
                        });

                        data.forEach(worker => {
                            const isChecked = selectedWorkers.hasOwnProperty(worker.id);
                            const row = `
                                <tr>
                                    <td>
                                        <input
                                            readonly
                                            type="checkbox"
                                            name="workers[${worker.id}][selected]"
                                            value="1"
                                            ${isChecked ? 'checked' : ''}
                                        >
                                    </td>
                                    <td>${worker.name}</td>
                                </tr>
                            `;
                            workerManagerTable.append(row);
                        });
                    })
                    .catch(error => {
                        console.error('Hiba a szerelők betöltésekor:', error);
                    });
            }

            function loadProducts(products = []) {
                const productManagerTable = $('#productManagerTable tbody');
                productManagerTable.empty();

                fetch(`${window.appConfig.APP_URL}admin/munkalapok/munkalap-termekek`)
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
                                                readonly
                                                type="checkbox"
                                                name="products[${item.id}][selected]"
                                                value="1"
                                                id="product_${item.id}"
                                                ${isChecked ? 'checked' : ''}
                                            >
                                        </td>
                                        <td><label for="product_${item.id}">${item.title}</label></td>
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

                const canDeletePhoto = @json(auth('admin')->user()?->can('delete-worksheet-image'));

                const containerForLocalPhotos = $('#worksheetLocalPhotos');
                const containerForDatatablePhotos = $('#worksheetDataTablePhotos');
                const containerForCertificatePhotos = $('#worksheetCertificatePhotos');
                const containerForInstallPhotos = $('#worksheetInstallPhotos');
                const containerForBillings = $('#worksheetBillings');

                containerForLocalPhotos.empty();
                containerForDatatablePhotos.empty();
                containerForCertificatePhotos.empty();
                containerForInstallPhotos.empty();
                containerForBillings.empty();

                if (!photos.length) {
                    containerForLocalPhotos.append('<p class="text-muted">Nincs feltöltött adattábla kép.</p>');
                    containerForDatatablePhotos.append('<p class="text-muted">Nincs feltöltött adattábla kép.</p>');
                    containerForCertificatePhotos.append('<p class="text-muted">Nincs feltöltött tanúsítvány kép.</p>');
                    containerForInstallPhotos.append('<p class="text-muted">Nincs feltöltött szerelés kép.</p>');
                    containerForBillings.append('<p class="text-muted">Nincs feltöltött számla.</p>');
                    return;
                }

                const containers = {
                    'Helyszíni felmérés': containerForLocalPhotos,
                    'Adattábla': containerForDatatablePhotos,
                    'Telepítési tanúsítvány': containerForCertificatePhotos,
                    'Szerelés': containerForInstallPhotos,
                    'Számla': containerForBillings
                };

                const tables = {
                    'Helyszíni felmérés': createPhotoTable(),
                    'Adattábla': createPhotoTable(),
                    'Telepítési tanúsítvány': createPhotoTable(),
                    'Szerelés': createPhotoTable(),
                    'Számla': createPhotoTable()
                };

                photos.forEach(photo => {
                    const type = photo.image_type;
                    const container = containers[type];
                    const table = tables[type];

                    if (!container || !table) return;

                    const description = photo.description || '';
                    const fileUrl = `${window.appConfig.APP_URL}admin/worksheets/${photo.image_path}`;
                    const extension = photo.image_path.split('.').pop().toLowerCase();

                    let previewHtml = '';

                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                        // kép megjelenítése thumbnailként
                        previewHtml = `
                            <a href="${fileUrl}" target="_blank">
                                <img src="${fileUrl}" alt="${description}" class="img-thumbnail" style="width: 100px;">
                            </a>
                        `;
                    } else if (['pdf'].includes(extension)) {
                        // PDF ikon vagy szöveg
                        previewHtml = `
                            <a href="${fileUrl}" target="_blank">
                                <i class="fas fa-file-pdf fa-2x text-danger"></i> ${description || 'PDF fájl'}
                            </a>
                        `;
                    } else if (['doc', 'docx'].includes(extension)) {
                        // DOC ikon vagy szöveg
                        previewHtml = `
                            <a href="${fileUrl}" target="_blank">
                                <i class="fas fa-file-word fa-2x text-primary"></i> ${description || 'Word dokumentum'}
                            </a>
                        `;
                    } else {
                        // Ismeretlen fájltípus – csak link
                        previewHtml = `
                            <a href="${fileUrl}" target="_blank">
                                ${description || photo.image_path}
                            </a>
                        `;
                    }

                    /*const row = $(`
                        <tr data-photo-id="${photo.id}">
                            <td>${previewHtml}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger delete-photo" data-photo-id="${photo.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);*/

                    const deleteButton = canDeletePhoto
                        ? `<button type="button" class="btn btn-sm btn-danger delete-photo" data-photo-id="${photo.id}">
                                    <i class="fas fa-trash"></i>
                               </button>`
                        : '';

                    const row = $(`
                            <tr data-photo-id="${photo.id}">
                                <td>${previewHtml}</td>
                                <td class="text-center">
                                    ${deleteButton}
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

                // --- Állomány törlése
                $('.delete-photo').off('click').on('click', function () {
                    const photoId = $(this).data('photo-id');
                    const row = $(this).closest('tr');

                    if (!confirm('Biztosan törölni szeretnéd ezt az állományt?')) return;

                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/munkalapok/delete-photo`,
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
                                        <th>Feltöltött állomány</th>
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
                    $('#worksheetLocalPhotos').empty();
                    $('#worksheetDataTablePhotos').empty();
                    $('#worksheetCertificatePhotos').empty();
                    $('#worksheetInstallPhotos').empty();
                    $('#worksheet_id').val(''); // Munkalap ID törlése
                }
            }
        });
    </script>
@endsection
