@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
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
    <div class="modal fade admin-modal-soft" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="adminModalForm" action="" enctype="multipart/form-data">
                <input type="hidden" id="worksheet_id" name="worksheet_id">
                <input type="hidden" id="client_id" name="client_id">
                <input type="hidden" id="client_address_id" name="client_address_id">
                <input type="hidden" id="create_client" name="create_client" value="0">
                <input type="hidden" id="use_custom_address" name="use_custom_address" value="0">

                <div class="modal-content">
                    <div class="modal-header bg-gradient-custom">
                        <h5 class="modal-title" id="adminModalLabel">Munkalap szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">

                        <ul class="nav nav-tabs admin-modal-tabs" id="productTab" role="tablist">
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
                                $readonly = auth('admin')->user()->can('view-own-worksheets') ? 'readonly' : '';
                            @endphp



                                <!-- Alapadatok tab -->

                            <div class="tab-pane fade show active" id="basic">
                                <table class="table worksheet-basic-table admin-modal-form-table">
                                    <tbody>
                                    <tr>
                                        <td class="w-25">Ügyfél keresés</td>
                                        <td class="position-relative">
                                            <input type="text" class="form-control" id="client_search" placeholder="Név / e-mail / telefon..." autocomplete="off" {{ $readonly }}>
                                            <div id="client_search_results" class="list-group position-absolute w-100 admin-client-search-results" style="z-index: 1100; display:none; max-height: 260px; overflow-y: auto;"></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="w-25">Munka megnevezése</td>
                                        <td><input type="text" class="form-control" id="work_name" name="work_name" {{ $readonly }} required></td>
                                    </tr>
                                    <tr>
                                        <td class="w-25">Munka típusa</td>
                                        <td>
                                            <select name="work_type" id="work_type" class="form-control" {{ $readonly }}>
                                                <option value="Karbantartás">Karbantartás</option>
                                                <option value="Szerelés">Szerelés</option>
                                                <option value="Felmérés">Felmérés</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="w-25">Munka dátuma</td>
                                        <td><input type="date" class="form-control" id="installation_date" name="installation_date" {{ $readonly }} required></td>
                                    </tr>

                                    <tr class="worksheet-client-fields" style="display:none;">
                                        <td class="w-25">Ügyfélnév</td>
                                        <td><input type="text" class="form-control" id="contact_name" name="contact_name" {{ $readonly }} required></td>
                                    </tr>
                                    <tr class="worksheet-client-fields" style="display:none;">
                                        <td>Ország</td>
                                        <td>
                                            <select name="contact_country" class="form-control w-100" id="contact_country" {{ $readonly }}>
                                                @foreach(config('countries') as $code => $name)
                                                    <option value="{{ $code }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="worksheet-client-fields" style="display:none;">
                                        <td>Irányítószám</td>
                                        <td><input type="text" class="form-control" id="contact_zip_code" name="contact_zip_code" {{ $readonly }}></td>
                                    </tr>
                                    <tr class="worksheet-client-fields" style="display:none;">
                                        <td>Város</td>
                                        <td>
                                            <input type="text" class="form-control" id="contact_city" name="contact_city" {{ $readonly }}>
                                            <div id="zip_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                        </td>
                                    </tr>
                                    <tr class="worksheet-client-fields" style="display:none;">
                                        <td>Cím</td>
                                        <td>
                                            <input type="text" class="form-control" id="contact_address_line" name="contact_address_line" {{ $readonly }}>
                                            <div id="street_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                        </td>
                                    </tr>
                                    <tr class="worksheet-client-fields" style="display:none;">
                                        <td>Telefonszám <span class="ml-3 btn btn-primary" id="call_phone_number"><i class="fa fa-phone"></i> Hívás</span></td>
                                        <td><input type="text" class="form-control" id="contact_phone" name="contact_phone" {{ $readonly }}></td>
                                    </tr>
                                    <tr class="worksheet-client-fields" style="display:none;">
                                        <td>E-mail cím</td>
                                        <td><input type="email" class="form-control" id="contact_email" name="contact_email" {{ $readonly }}></td>
                                    </tr>
                                    <tr>
                                        <td>Szerződés hozzárendelése</td>
                                        <td>
                                            <select name="contract_id" class="form-control w-100" id="contract_id" name="contract_id" {{ $readonly }}>
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
                                        <td>Megjegyzés</td>
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
                                <div id="selectedProductsSummary" class="mb-3" style="display: none">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>Hozzárendelt termékek</strong>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleSelectedProducts">Elrejt</button>
                                    </div>
                                    <div id="selectedProductsBody" class="mt-2">
                                        <table class="table table-sm table-bordered mb-0" id="selectedProductsTable">
                                            <thead>
                                            <tr>
                                                <th>Termék</th>
                                                <th style="width: 120px">Darabszám</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                                <input type="text" class="form-control mb-3" id="productSearch" placeholder="Keresés a termékek között...">
                                <div style="max-height: 300px; overflow-y: auto">
                                    <table id="productTableForWorker" class="table table-bordered" style="display: none">
                                        <thead>
                                        <tr>
                                            <th>Termék</th>
                                            <th>Darabszám</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <!-- Termékek betöltése itt -->
                                        </tbody>
                                    </table>
                                    <table class="table table-bordered" id="productManagerTable" style="display: {{ $display }}" isWorker="{{ $readonly }}">
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
                                    <label for="payment_amount" class="form-label">Átvett készpénz összege:</label>
                                    <input type="number" name="payment_amount" id="payment_amount" class="form-control">
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

                        <select class="form-select form-select-sm w-auto" name="work_status" id="work_status">
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
    <style>
        select.snapshot-locked {
            pointer-events: none;
        }
    </style>
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

            let zipDebounceTimeout;

            $('#contact_zip_code').on('input', function () {
                clearTimeout(zipDebounceTimeout);

                zipDebounceTimeout = setTimeout(() => {
                    const zip = ($('#contact_zip_code').val() || '').trim();
                    const $suggestions = $('#zip_suggestions');
                    $suggestions.empty();

                    if (!zip) {
                        $suggestions.hide();
                        return;
                    }

                    $.ajax({
                        url: window.appConfig.APP_URL + 'api/postal-codes/search?zip=' + encodeURIComponent(zip),
                        type: 'GET',
                        success: function (data) {
                            $suggestions.empty();

                            if (Array.isArray(data) && data.length > 0) {
                                data.forEach(function (row) {
                                    $suggestions.append(`
                                        <button type="button" class="list-group-item list-group-item-action city-item" data_zip="${row.zip}">
                                            ${row.city}
                                        </button>
                                    `);
                                });

                                $suggestions.show();
                            } else {
                                $suggestions.hide();
                            }
                        },
                        error: function () {
                            $suggestions.hide();
                        }
                    });
                }, 300);
            });

            $('#zip_suggestions').on('click', 'button', function () {
                $('#contact_zip_code').val($(this).attr('data_zip'));
                $('#contact_city').val($(this).text().trim());
                $('#zip_suggestions').hide();
            });

            let streetDebounceTimeout;

            $('#contact_address_line').on('input', function () {
                clearTimeout(streetDebounceTimeout);

                streetDebounceTimeout = setTimeout(() => {
                    const city = ($('#contact_city').val() || '').trim();
                    const q = ($('#contact_address_line').val() || '').trim();

                    const $suggestions = $('#street_suggestions');
                    $suggestions.empty();

                    if (!city || q.length < 2) {
                        $suggestions.hide();
                        return;
                    }

                    $.ajax({
                        url: window.appConfig.APP_URL + 'api/streets/search?city=' + encodeURIComponent(city) + '&q=' + encodeURIComponent(q),
                        type: 'GET',
                        success: function (data) {
                            $suggestions.empty();

                            if (Array.isArray(data) && data.length > 0) {
                                data.forEach(function (name) {
                                    $suggestions.append(`
                                        <button type="button" class="list-group-item list-group-item-action street-item">
                                            ${name}
                                        </button>
                                    `);
                                });

                                $suggestions.show();
                            } else {
                                $suggestions.hide();
                            }
                        },
                        error: function () {
                            $suggestions.hide();
                        }
                    });
                }, 300);
            });

            $('#street_suggestions').on('click', 'button', function () {
                $('#contact_address_line').val($(this).text().trim());
                $('#street_suggestions').hide();
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

                    setSnapshotMode(true);

                } catch (error) {
                    showToast(error, 'danger');
                }
                adminModal.show();
            }

            let clientSearchDebounce;

            function clearClientSelection() {
                $('#client_id').val('');
                $('#client_address_id').val('');
                $('#create_client').val('0');
                $('#use_custom_address').val('0');

                $('#contact_name').val('');
                $('#contact_email').val('');
                $('#contact_phone').val('');
                $('#contact_country').val('HU');
                $('#contact_zip_code').val('');
                $('#contact_city').val('');
                $('#contact_address_line').val('');

                setClientFieldsVisible(false);
                setSnapshotMode(true);
            }

            $('#client_search').on('input', function () {
                const q = ($(this).val() || '').trim();
                clearTimeout(clientSearchDebounce);

                $('#client_search_results').hide().empty();

                if ($('#client_id').val() || $('#create_client').val() === '1') {
                    clearClientSelection();
                }

                if (!q || q.length < 2) {
                    return;
                }

                clientSearchDebounce = setTimeout(() => {
                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/ugyfelek/kereses?q=${encodeURIComponent(q)}`,
                        method: 'GET',
                        success: function (response) {
                            const clients = response?.clients || [];
                            const $list = $('#client_search_results');
                            $list.empty();

                            if (clients.length) {
                                clients.forEach(c => {
                                    const name = c.name || '';
                                    const idNumber = c.id_number || '';
                                    const email = c.email || '';
                                    const phone = c.phone || '';

                                    const headerParts = [idNumber, email].filter(Boolean).join(', ');

                                    const addresses = Array.isArray(c.addresses) ? c.addresses : [];

                                    $list.append(`
                                        <div class="list-group-item client-search-header">
                                            <div class="fw-bold">${escapeHtml(name || email || 'N/A')}${headerParts ? ' (' + escapeHtml(headerParts) + ')' : ''}</div>
                                        </div>
                                    `);

                                    if (!addresses.length) {
                                        $list.append(`
                                            <button type="button" class="list-group-item list-group-item-action client-new-address"
                                                data-id="${c.id}"
                                                data-id-number="${escapeHtml(idNumber)}"
                                                data-name="${escapeHtml(name)}"
                                                data-email="${escapeHtml(email)}"
                                                data-phone="${escapeHtml(phone)}">
                                                <div class="fw-bold"><i class="fa-solid fa-circle-plus me-2"></i>Új cím</div>
                                                <div class="small text-muted">Nincs rögzített cím</div>
                                            </button>
                                        `);
                                        return;
                                    }

                                    addresses.forEach(a => {
                                        const addrText = `${a.zip_code || ''} ${a.city || ''}, ${a.address_line || ''}`.trim();

                                        const subtitle = [addrText].filter(Boolean).join(' | ');

                                        $list.append(`
                                            <button type="button" class="list-group-item list-group-item-action client-address-item"
                                                data-id="${c.id}"
                                                data-address-id="${a?.id || ''}"
                                                data-id-number="${escapeHtml(idNumber)}"
                                                data-name="${escapeHtml(name)}"
                                                data-email="${escapeHtml(email)}"
                                                data-phone="${escapeHtml(phone)}"
                                                data-country="${escapeHtml(a?.country || '')}"
                                                data-zip="${escapeHtml(a?.zip_code || '')}"
                                                data-city="${escapeHtml(a?.city || '')}"
                                                data-line="${escapeHtml(a?.address_line || '')}">
                                                <div class="fw-bold">${escapeHtml(subtitle || 'N/A')}${a?.is_default ? ' (alapértelmezett)' : ''}</div>
                                            </button>
                                        `);
                                    });

                                    $list.append(`
                                        <button type="button" class="list-group-item list-group-item-action client-new-address"
                                            data-id="${c.id}"
                                            data-id-number="${escapeHtml(idNumber)}"
                                            data-name="${escapeHtml(name)}"
                                            data-email="${escapeHtml(email)}"
                                            data-phone="${escapeHtml(phone)}">
                                            <div class="fw-bold"><i class="fa-solid fa-circle-plus me-2"></i>Új cím</div>
                                        </button>
                                    `);
                                });
                            }

                            $list.append(`
                                <button type="button" class="list-group-item list-group-item-action client-create client-create-item">
                                    <div class="fw-bold">Új ügyfél létrehozása</div>
                                    <div class="small text-muted">Az alábbi mezőkben megadott adatokkal</div>
                                </button>
                            `);

                            $list.show();
                        },
                        error: function () {
                            const $list = $('#client_search_results');
                            $list.empty();
                            $list.append(`
                                <button type="button" class="list-group-item list-group-item-action client-create client-create-item">
                                    <div class="fw-bold">Új ügyfél létrehozása</div>
                                    <div class="small text-muted">A keresés sikertelen volt</div>
                                </button>
                            `);
                            $list.show();
                        }
                    });
                }, 300);
            });

            $('#client_search_results').on('click', '.client-address-item', function () {
                const $btn = $(this);

                const clientId = $btn.data('id');
                const addressId = $btn.data('address-id') || null;
                const idNumber = $btn.data('id-number') || null;
                const name = $btn.data('name') || null;
                const email = $btn.data('email') || null;
                const phone = $btn.data('phone') || null;
                const country = $btn.data('country') || null;
                const zip = $btn.data('zip') || null;
                const city = $btn.data('city') || null;
                const line = $btn.data('line') || null;

                $('#client_id').val(clientId);
                $('#client_address_id').val(addressId);
                $('#create_client').val('0');
                $('#use_custom_address').val('0');

                $('#contact_name').val(name);
                $('#contact_email').val(email);
                $('#contact_phone').val(phone);
                $('#contact_country').val(country || 'HU');
                $('#contact_zip_code').val(zip);
                $('#contact_city').val(city);
                $('#contact_address_line').val(line);

                setClientFieldsVisible(true);
                setSnapshotMode(true);

                const headerParts = [idNumber, email].filter(Boolean).join(', ');
                const display = `${name || ''}${headerParts ? ' (' + headerParts + ')' : ''}`.trim();
                $('#client_search').val(display);
                $('#client_search_results').hide().empty();
            });

            $('#client_search_results').on('click', '.client-new-address', function () {
                const $btn = $(this);

                const clientId = $btn.data('id');
                const idNumber = $btn.data('id-number') || null;
                const name = $btn.data('name') || null;
                const email = $btn.data('email') || null;
                const phone = $btn.data('phone') || null;

                $('#client_id').val(clientId);
                $('#client_address_id').val('');
                $('#create_client').val('0');
                $('#use_custom_address').val('1');

                $('#contact_name').val(name);
                $('#contact_email').val(email);
                $('#contact_phone').val(phone);
                $('#contact_country').val('HU');
                $('#contact_zip_code').val('');
                $('#contact_city').val('');
                $('#contact_address_line').val('');

                setClientFieldsVisible(true);
                setSnapshotMode(false);

                const headerParts = [idNumber, email].filter(Boolean).join(', ');
                const display = `${name || ''}${headerParts ? ' (' + headerParts + ')' : ''}`.trim();
                $('#client_search').val(display);
                $('#client_search_results').hide().empty();

                setTimeout(() => {
                    $('#contact_zip_code').trigger('focus');
                }, 0);
            });

            $('#client_search_results').on('click', '.client-create', function () {
                $('#create_client').val('1');
                $('#client_id').val('');
                $('#client_address_id').val('');
                $('#use_custom_address').val('0');

                setClientFieldsVisible(true);
                setSnapshotMode(false);

                $('#client_search').val('');
                $('#client_search_results').hide().empty();

                setTimeout(() => {
                    $('#contact_name').trigger('focus');
                }, 0);
            });

            function setClientFieldsVisible(visible) {
                $('.worksheet-client-fields').toggle(!!visible);
                $('#contact_name').prop('required', !!visible);
            }

            function setSnapshotMode(isSnapshot) {
                const disable = !!isSnapshot;

                const $inputs = $('#contact_name, #contact_zip_code, #contact_city, #contact_address_line');
                const $alwaysEditableInputs = $('#contact_phone, #contact_email');
                const $selects = $('#contact_country');

                $inputs.prop('readonly', disable);
                $alwaysEditableInputs.prop('readonly', false);
                $selects.toggleClass('snapshot-locked', disable);

                if (disable) {
                    $inputs.addClass('bg-light');
                    $alwaysEditableInputs.removeClass('bg-light');
                    $selects.addClass('bg-light');
                } else {
                    $inputs.removeClass('bg-light');
                    $alwaysEditableInputs.removeClass('bg-light');
                    $selects.removeClass('bg-light');
                }
            }

            function escapeHtml(str) {
                return String(str)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
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
                $('#worker_report').val(worksheet.worker_report);
                $('#contact_phone').val(worksheet.phone);
                $('#contact_email').val(worksheet.email);

                $('#client_id').val(worksheet.client_id || '');
                if (worksheet.client_id) {
                    const display = `${worksheet.name || ''}${worksheet.email ? ' (' + worksheet.email + ')' : ''}`.trim();
                    $('#client_search').val(display);
                } else {
                    $('#client_search').val('');
                }

                setClientFieldsVisible(true);
                setSnapshotMode(true);
                $('#create_client').val('0');
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

            $('#call_phone_number').on('click', function (e) {
                e.preventDefault(); // ne csináljon mást, pl. ha gomb/link
                let phone_number = $('#contact_phone').val().replace(/\s+/g, ''); // szóközök eltávolítása

                if (phone_number) {
                    window.location.href = 'tel:' + phone_number;
                } else {
                    alert('Nincs megadva telefonszám.');
                }
            });


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

            $('#toggleSelectedProducts').on('click', function () {
                const $body = $('#selectedProductsBody');
                const isVisible = $body.is(':visible');
                $body.toggle(!isVisible);
                $(this).text(isVisible ? 'Mutat' : 'Elrejt');
            });

            function refreshSelectedProductsSummary() {
                const $summary = $('#selectedProductsSummary');
                const $tbody = $('#selectedProductsTable tbody');
                $tbody.empty();

                const selected = [];
                $('#productManagerTable tbody input[type="checkbox"][name$="[selected]"]:checked').each(function () {
                    const checkboxName = $(this).attr('name') || '';
                    const match = checkboxName.match(/^products\[(\d+)\]\[selected\]$/);
                    if (!match) return;
                    const id = match[1];
                    const title = $(`#product_${id}`).closest('tr').find('label').text().trim();
                    const qty = $(`input[name="products[${id}][qty]"]`).val() || 1;
                    selected.push({ title, qty: qty });
                });

                if (!selected.length) {
                    $summary.hide();
                    return;
                }

                selected.forEach(item => {
                    $tbody.append(`<tr><td>${item.title}</td><td>${item.qty}</td></tr>`);
                });

                $summary.show();
                if (!$('#selectedProductsBody').is(':visible')) {
                    $('#selectedProductsBody').show();
                    $('#toggleSelectedProducts').text('Elrejt');
                }
            }

            $('#productManagerTable').on('change input', 'input', function () {
                $('#productManagerTable tbody input[type="checkbox"][name$="[selected]"]').each(function () {
                    const $cb = $(this);
                    const checked = $cb.is(':checked');
                    const $row = $cb.closest('tr');
                    $row.toggleClass('table-success', checked);

                    const checkboxName = $cb.attr('name') || '';
                    const match = checkboxName.match(/^products\[(\d+)\]\[selected\]$/);
                    if (match) {
                        const id = match[1];
                        $(`input[name="products[${id}][qty]"]`).prop('disabled', !checked);
                    }
                });
                refreshSelectedProductsSummary();
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

                const saveWorksheetAjax = () => {
                    return $.ajax({
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
                            $('#saveWorksheet').html(originalSaveButtonHtml).prop('disabled', false);
                        }
                    });
                };

                const clientId = ($('#client_id').val() || '').toString().trim();
                const isCreateClient = ($('#create_client').val() || '').toString() === '1';

                if (clientId && !isCreateClient) {
                    const payload = {
                        id: clientId,
                        name: ($('#contact_name').val() || '').toString().trim() || null,
                        email: ($('#contact_email').val() || '').toString().trim(),
                        phone: ($('#contact_phone').val() || '').toString().trim() || null,
                        _token: csrfToken,
                    };

                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/ugyfelek/${encodeURIComponent(clientId)}`,
                        method: 'PUT',
                        data: payload,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    })
                        .done(() => saveWorksheetAjax())
                        .fail((xhr) => {
                            let msg = 'Hiba az ügyfél adatainak mentésekor!';
                            if (xhr.responseJSON?.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                            } else if (xhr.responseJSON?.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                            $('#saveWorksheet').html(originalSaveButtonHtml).prop('disabled', false);
                        });

                    return;
                }

                saveWorksheetAjax();

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
                            table.ajax.reload(null, false);
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

                const isEditMode = Array.isArray(workers) && workers.length > 0;

                fetch(`${window.appConfig.APP_URL}admin/fetch-felhasznalok`)
                    .then(response => response.json())
                    .then(data => {
                        if (!isEditMode) {
                            data = (Array.isArray(data) ? data : []).filter(u => (u?.status ?? 'active') !== 'inactive');
                        }

                        // Átalakítjuk a meglévő workers tömböt egy gyors lookup objektummá
                        const selectedWorkers = {};
                        workers.forEach(w => {
                            selectedWorkers[w.id] = true; // csak a kiválasztottakat tároljuk
                        });

                        data.forEach(worker => {
                            const isChecked = selectedWorkers.hasOwnProperty(worker.id);
                            const isInactive = (worker?.status ?? 'active') === 'inactive';

                            const selectionCell = isEditMode
                                ? (isInactive
                                    ? (isChecked
                                        ? `<i class="fa-solid fa-check text-success"></i>
                                           <input type="hidden" name="workers[${worker.id}][selected]" value="1">`
                                        : ``)
                                    : `<input
                                            type="checkbox"
                                            name="workers[${worker.id}][selected]"
                                            value="1"
                                            ${isChecked ? 'checked' : ''}
                                       >`)
                                : `<input
                                        type="checkbox"
                                        name="workers[${worker.id}][selected]"
                                        value="1"
                                        ${isChecked ? 'checked' : ''}
                                   >`;

                            const row = `
                                <tr>
                                    <td>${selectionCell}</td>
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
                const isWorker = $('#productManagerTable').attr('isWorker');
                productManagerTable.empty();
                let selected_rows = '';

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

                                if (isChecked) {
                                    selected_rows += `<tr>
                                        <td>${item.title}</td>
                                        <td>${selectedProducts[item.id]}</td>
                                    </tr>`;
                                }

                                const row = `
                                    <tr class="${isChecked ? 'table-success' : ''}">
                                        <td>
                                            <input
                                                type="checkbox"
                                                name="products[${item.id}][selected]"
                                                value="1"
                                                id="product_${item.id}"
                                                ${isWorker == 'readonly' ? 'disabled' : ''}
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
                                                ${(isWorker == 'readonly' || !isChecked) ? 'disabled' : ''}
                                            >
                                        </td>
                                    </tr>
                                `;
                                productManagerTable.append(row);
                            });
                        });

                        if (isWorker == 'readonly') {
                            $('#productTableForWorker tbody').append(selected_rows);
                            $('#productTableForWorker').show();
                        } else {
                            $('#productTableForWorker').hide();
                        }

                        refreshSelectedProductsSummary();
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
                            <a href="${fileUrl}" class="glightbox" data-gallery="worksheet" data-title="${description}">
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

                // Lightbox init friss képekre
                if (window.worksheetLightbox) {
                    window.worksheetLightbox.destroy();
                }

                window.worksheetLightbox = GLightbox({
                    selector: '.glightbox',
                    touchNavigation: true,
                    loop: true,
                    zoomable: true,
                    closeOnOutsideClick: true
                });

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
                    $(form).find('select').val('Folyamatban').trigger('change'); // select2 kompatibilitás
                    $(form).find('textarea').val('');
                    $(form).find('input[type="file"]').val(''); // fájlmezők törlése

                    // Ha szeretnél, eltüntethetsz előzőleg betöltött képeket is, pl.:
                    $('#worksheetLocalPhotos').empty();
                    $('#worksheetDataTablePhotos').empty();
                    $('#worksheetCertificatePhotos').empty();
                    $('#worksheetInstallPhotos').empty();
                    $('#worksheet_id').val(''); // Munkalap ID törlése
                    $('#client_id').val('');
                    $('#create_client').val('0');
                    $('#client_search').val('');
                    $('#client_search_results').hide().empty();
                    $('#zip_suggestions').empty().hide();
                    $('#street_suggestions').empty().hide();

                    setClientFieldsVisible(false);
                }
            }
        });
    </script>
@endsection
