@extends('layouts.admin')

@section('content')

    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyviteli folyamatok / Szerződések</h2>
            @if(auth('admin')->user()->can('create-contract'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új szerződés</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-contracts'))

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
                        <input type="text" placeholder="Irányítószám" class="filter-input form-control" data-column="3">
                    </div>
                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Város" class="filter-input form-control" data-column="4">
                    </div>
                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Cím" class="filter-input form-control" data-column="5">
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Név</th>
                        <th>Ország</th>
                        <th>Irányítószám</th>
                        <th>Város</th>
                        <th>Cím</th>
                        <th>Szerelés dátuma</th>
                        <th>Készítette</th>
                        <th>Létrehozva</th>
                        <th>Látta</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>

            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a szerződések megtekintéséhez.
                </div>
            @endif
        </div>
    </div>

    <!-- Modális ablak -->
    <div class="modal fade admin-modal-soft" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="adminModalForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="contract_id" id="contract_id">
                <input type="hidden" id="client_id" name="client_id">
                <input type="hidden" id="client_address_id" name="client_address_id">
                <input type="hidden" id="create_client" name="create_client" value="0">
                <input type="hidden" id="use_custom_address" name="use_custom_address" value="0">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-custom">
                        <h5 class="modal-title" id="adminModalLabel">Szerződés szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">

                        <ul class="nav nav-tabs admin-modal-tabs" id="productTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#contact" type="button">Kapcsolati adatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#contractDataManager" type="button">Szerződés adatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#productManager" type="button">Termékek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#contract" type="button">Aláírás és generálás</button></li>
                        </ul>

                        <div class="tab-content mt-3">

                            <!-- Kapcsolati adatok tab -->

                            <div class="tab-pane fade show active" id="contact">
                                <div class="contract-contact">
                                    <table class="table admin-modal-form-table">
                                        <tbody>
                                        <tr>
                                            <td class="w-25">Ügyfél keresés</td>
                                            <td class="position-relative">
                                                <input type="text" class="form-control" id="client_search" placeholder="Név / e-mail / telefon..." autocomplete="off">
                                                <div id="client_search_results" class="list-group position-absolute w-100 admin-client-search-results" style="z-index: 1100; display:none; max-height: 260px; overflow-y: auto;"></div>
                                            </td>
                                        </tr>

                                        <tr class="contract-client-fields" style="display:none;">
                                            <td class="w-25">Név / Cégnév*</td>
                                            <td><input type="text" class="form-control" name="contact_name" id="contact_name" required></td>
                                        </tr>
                                        <tr class="contract-client-fields" style="display:none;">
                                            <td>Ország*</td>
                                            <td>
                                                <select name="contact_country" class="form-control w-100" id="contact_country">
                                                    @foreach(config('countries') as $code => $name)
                                                        <option value="{{ $code }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class="contract-client-fields" style="display:none;">
                                            <td>Irányítószám*</td>
                                            <td><input type="text" class="form-control" name="contact_zip_code" id="contact_zip_code" autocomplete="off"></td>
                                        </tr>
                                        <tr class="contract-client-fields" style="display:none;">
                                            <td>Város*</td>
                                            <td class="position-relative">
                                                <input type="text" class="form-control" name="contact_city" id="contact_city">
                                                <div id="zip_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                            </td>
                                        </tr>
                                        <tr class="contract-client-fields" style="display:none;">
                                            <td>Cím*</td>
                                            <td class="position-relative">
                                                <input type="text" class="form-control" name="contact_address_line" id="contact_address_line">
                                                <div id="street_suggestions" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                            </td>
                                        </tr>
                                        <tr class="contract-client-fields" style="display:none;">
                                            <td>Telefonszám</td>
                                            <td><input type="text" class="form-control" name="contact_phone" id="contact_phone"></td>
                                        </tr>
                                        <tr class="contract-client-fields" style="display:none;">
                                            <td>E-mail cím</td>
                                            <td><input type="email" class="form-control" name="contact_email" id="contact_email"></td>
                                        </tr>
                                        <tr class="contract-client-fields" style="display:none;">
                                            <td>Anyja neve</td>
                                            <td><input type="text" class="form-control" name="mothers_name" id="mothers_name"></td>
                                        </tr>
                                        <tr class="contract-client-fields" style="display:none;">
                                            <td>Születési hely</td>
                                            <td><input type="text" class="form-control" name="place_of_birth" id="place_of_birth"></td>
                                        </tr>
                                        <tr class="contract-client-fields" style="display:none;">
                                            <td>Születési idő</td>
                                            <td><input type="date" class="form-control" name="date_of_birth" id="date_of_birth"></td>
                                        </tr>
                                        <tr class="contract-client-fields" style="display:none;">
                                            <td>Személyi igazolványszám</td>
                                            <td><input type="text" class="form-control" name="id_number" id="id_number"></td>
                                        </tr>
                                        <tr class="contract-client-fields" style="display:none;">
                                            <td>Szerelés időpontja</td>
                                            <td><input type="date" class="form-control" name="installation_date" id="installation_date"></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                            <!-- Szerződés adatok tab -->
                            <div class="tab-pane fade" id="contractDataManager">
                                <div class="form-group">
                                    <label for="contract_version">Szerződés verzió kiválasztása</label>
                                    <select id="contract_version" class="form-control" name="contract_version">
                                        @foreach($versions as $version)
                                            <option value="{{ $version }}">{{ $version }}</option>
                                        @endforeach
                                    </select>
                                    <div id="contractDataFieldsArea" class="mt-5">
                                    </div>
                                </div>
                            </div>

                            <!-- Termékek tab -->

                            <div class="tab-pane fade" id="productManager">
                                <input type="text" class="form-control mb-3" id="productSearch" placeholder="Keresés a termékek között...">
                                <div style="max-height: 300px; overflow-y: auto">
                                    <table class="table table-bordered" id="productManagerTable">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Termék</th>
                                            <th>Br. ár</th>
                                            <th>Db</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <!-- Termékek betöltése itt -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Szerződés generálás tab -->

                            <div class="tab-pane fade" id="contract">

                                <div id="fullscreen_signature" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: none; flex-direction: column; align-items: center; justify-content: center; z-index: 1060; background-color: white">

                                    <canvas id="signature-pad" style="flex: 1; width: 100%; height: calc(100% - 80px); border: 1px solid #ccc;"></canvas>
                                    <input type="hidden" name="signature" id="signature-input">

                                    <div style="padding: 1rem; display: flex; gap: 10px;">
                                        <button type="submit" class="btn btn-primary" id="generateContract">
                                            <i class="fas fa-file-pdf"></i> Szerződés generálása
                                        </button>
                                        <button type="button" id="clear-signature-pad" class="btn btn-secondary">
                                            <i class="fas fa-eraser"></i> Újra
                                        </button>
                                        <button id="close-fullscreen-signature" class="btn btn-danger">
                                            Bezárás
                                        </button>
                                    </div>
                                </div>

                                <img id="show_signature" src="" class="d-none" style="max-width: 300px">
                                <a href="#" id="preview_contract" target="_blank" class="btn btn-info d-none">Előnézet</a>
                                <a href="#" id="clear_signature" class="btn btn-secondary">Aláírás</a>
                                <a href="#" id="generateContractWithOutSignature" class="btn btn-primary">Aláírás nélküli szerződés generálás</a>
                                <a href="#" id="regenarate" class="btn btn-secondary">Újragenerálás új aláírás nélkül</a>
                                <a href="" id="contract_pdf_link" target="_blank" class="btn btn-primary d-none">Generált PDF megtekintése</a>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>



@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <style>
        select.snapshot-locked {
            pointer-events: none;
        }
    </style>
    <script type="module">

        const adminModalDOM = document.getElementById('adminModal');
        const adminModal = new bootstrap.Modal(adminModalDOM);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        $(document).ready(async function() {
            const table = $('#adminTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.contracts.data') }}',
                order: [[0, 'desc']],
                columns: [
                    {data: 'id'},
                    {data: 'name'},
                    {data: 'country'},
                    {data: 'zip_code'},
                    {data: 'city'},
                    {data: 'address_line'},
                    {data: 'installation_date'},
                    {data: 'creator_name'},
                    {data: 'created'},
                    {data: 'viewed_by'},
                    {data: 'action', orderable: false, searchable: false}
                ],
            });

            const urlParams = new URLSearchParams(window.location.search);
            const searchId = urlParams.get('id');
            const showModal = urlParams.get('modal');
            const makeContract = urlParams.get('make_contract');
            const installationDate = urlParams.get('installation_date');

            if (searchId) {
                $('.filter-input[data-name="id"]').val(searchId);
                const input = $('.filter-input[data-name="id"]');
                const i = input.attr('data-column');
                const v = input.val();
                table.columns(i).search(v).draw();

                if (showModal) {
                    let hasOpened = false;

                    table.on('draw', function () {
                        if (hasOpened) return; // csak egyszer futtassuk
                        hasOpened = true;

                        const viewBtn = $('#adminTable tbody .view[data-id="' + searchId + '"]');
                        if (viewBtn.length) {
                            viewBtn.trigger('click');
                        }
                    });
                }
            }

            if (makeContract) {
                await showModalToCreate(installationDate);
            }


            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');  // getting column index
                var v =$(this).val();  // getting search input value
                table.columns(i).search(v).draw();
            });

            const signature_canvas = document.getElementById('signature-pad');
            const fullscreenDiv = document.getElementById('fullscreen_signature');


            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const rect = signature_canvas.getBoundingClientRect();

                if(rect.width === 0 || rect.height === 0) return; // még nem látható, ne skálázz

                signature_canvas.width = rect.width * ratio;
                signature_canvas.height = rect.height * ratio;
                signature_canvas.getContext("2d").scale(ratio, ratio);
            }

            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

            const signaturePad = new SignaturePad(signature_canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });


            document.getElementById('clear_signature').addEventListener('click', function() {
                signaturePad.clear();
                document.getElementById('fullscreen_signature').style.display = 'flex';
                resizeCanvas();
            });

            document.getElementById('clear-signature-pad').addEventListener('click', function() {
                signaturePad.clear();
            });


            $("#close-fullscreen-signature").on("click", function (e) {
                e.preventDefault();
                $("#fullscreen_signature").hide(); // bezárás
            });

            // Szerződés megtekintése

            $('#adminTable').on('click', '.view', async function () {

                resetForm('Szerződés megtekintése');
                $('.contract-contact').find('input, select, textarea').prop('disabled', true);

                $('#generateContract').addClass('d-none');
                $('#generateContractWithOutSignature').addClass('d-none');


                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();

                const contract_data = await loadContractProducts(row_data.id);
                const contract = contract_data.contract || {};
                const contract_products = contract.products || [];

                // Kapcsolati adatok

                $('#contact_name').val(contract.name);
                $('#contact_country').val(contract.country);
                $('#contact_zip_code').val(contract.zip_code);
                $('#contact_city').val(contract.city);
                $('#contact_address_line').val(contract.address_line);
                $('#contact_phone').val(contract.phone);
                $('#contact_email').val(contract.email);
                $('#mothers_name').val(contract.mothers_name);
                $('#place_of_birth').val(contract.place_of_birth);
                $('#date_of_birth').val(contract.date_of_birth);
                $('#id_number').val(contract.id_number);
                $('#installation_date').val(contract.installation_date);

                setClientFieldsVisible(true);
                setSnapshotMode(true);
                $('#create_client').val('0');

                const display = `${contract.name || ''}${contract.email ? ' (' + contract.email + ')' : ''}`.trim();
                $('#client_search').val(display);
                $('#client_search_results').hide().empty();


                // Szerződés adatok

                $('#contract_version').val(contract.version);
                $('#contract_version').prop('disabled', true);

                const container = document.getElementById('contractDataFieldsArea');
                container.innerHTML = '';

                if (contract.data === null) {
                    container.innerHTML = "Nincsenek beállított opciók";
                } else {
                    const contract_version_data = await loadVersions(contract.version);

                    contract_version_data.fields.forEach(field => {
                        const value = contract.data[field.key] ?? null;

                        // Opcionálisan boolean értékeket szövegesen is megjeleníthetsz:
                        let displayValue = value;
                        if (field.type === 'boolean') {
                            if (value === true) {
                                displayValue = 'Igen';
                            } else if (value === false) {
                                displayValue = 'Nem';
                            } else if (value === null || value === undefined) {
                                displayValue = 'Nem';
                            } else {
                                displayValue = '';
                            }
                        }


                        // Select esetén ellenőrizhető lenne, hogy valid érték-e
                        if (field.type === 'select' && field.options && !field.options.includes(value)) {
                            displayValue = ''; // vagy: 'Ismeretlen érték'
                        }

                        const fieldElement = document.createElement('div');
                        fieldElement.className = 'contract-display-field mb-2';

                        fieldElement.innerHTML = `
                            <strong>${field.label}:</strong> ${displayValue ?? ''}
                        `;

                        container.appendChild(fieldElement);

                    });
                }

                // Termékek

                const productManagerTable = $('#productManagerTable tbody');
                productManagerTable.empty();
                contract_products.forEach(item => {
                    const product_qty = item.pivot.product_qty;
                    const row = `
                        <tr>
                            <td>${item.id}</td>
                            <td>${item.title}</td>
                            <td>${item.pivot.gross_price}</td>
                            <td>${product_qty}</td>
                        </tr>`;
                    productManagerTable.append(row);
                });

                // Alárás
                if(contract.signature_path !== null) {
                    $('#show_signature').attr("src", "szerzodes/alairas/" + contract.signature_path);
                    $('#show_signature').removeClass('d-none');
                }
                $('#clear_signature').addClass('d-none');
                $('#preview_contract').addClass('d-none');
                $('#regenarate').addClass('d-none');
                $('#signature_area').addClass('d-none');

                // Generált PDF link

                $('#contract_pdf_link').removeClass('d-none').attr('href', `${contract.pdf_path}`);

                sendViewRequest("contract", row_data.id);

                table.ajax.reload(null, false);

                adminModal.show();
            });

            // Szerződés szerkesztése

            $('#adminTable').on('click', '.edit', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const contract_data = await loadContractProducts(row_data.id);
                sendViewRequest("contract", row_data.id);
                showModalToUpdate(contract_data);
            });

            async function loadContractProducts(id) {
                try {
                    const response = await fetch(`{{ url('/admin/szerzodesek/termekek') }}/${id}`, {
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

            // Új szerződés létrehozása modal megjelenítése
            $('#addButton').on('click', async function () {
                await showModalToCreate();
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

            async function showModalToCreate(installationDate = null) {
                try {
                    resetForm('Új szerződés létrehozása');
                    $('#show_signature').addClass('d-none');
                    $('#signature_area').removeClass('d-none');
                    $('#contract_version').prop('disabled', false);
                    $('#contract_version').val('v2');

                    const loaded_version = await loadVersions("v2");
                    renderContractForm(loaded_version.fields);
                    if (installationDate) {
                        $('#installation_date').val(installationDate);
                    }
                    loadProducts();
                    $('.contract-contact').find('input, select, textarea').prop('disabled', false);

                    setClientFieldsVisible(false);
                    setSnapshotMode(true);

                    $('#generateContract').removeClass('d-none');
                    $('#clear_signature').removeClass('d-none');
                    $('#preview_contract').removeClass('d-none');
                    $('#generateContractWithOutSignature').removeClass('d-none');
                    $('#regenarate').addClass('d-none');
                    $('#contract_pdf_link').addClass('d-none').removeAttr('href');
                } catch (error) {
                    showToast(error, 'danger');
                }
                adminModal.show();
            }

            async function showModalToUpdate(existingContractData = null) {
                try {

                    resetForm('Szerződés szerkesztése');

                    const contract = existingContractData.contract || {};
                    const contract_products = contract.products || [];

                    // Kapcsolati adatok

                    $('#contract_id').val(contract.id);
                    $('#contact_name').val(contract.name);
                    $('#contact_country').val(contract.country);
                    $('#contact_zip_code').val(contract.zip_code);
                    $('#contact_city').val(contract.city);
                    $('#contact_address_line').val(contract.address_line);
                    $('#contact_phone').val(contract.phone);
                    $('#contact_email').val(contract.email);
                    $('#mothers_name').val(contract.mothers_name);
                    $('#place_of_birth').val(contract.place_of_birth);
                    $('#date_of_birth').val(contract.date_of_birth);
                    $('#id_number').val(contract.id_number);
                    $('#installation_date').val(contract.installation_date);

                    setClientFieldsVisible(true);
                    setSnapshotMode(true);
                    $('#create_client').val('0');

                    $('#show_signature').addClass('d-none');
                    $('#clear_signature').removeClass('d-none');
                    $('#preview_contract').removeClass('d-none');
                    $('#signature_area').removeClass('d-none');
                    $('#contract_version').prop('disabled', false);
                    $('#contract_version').val(contract.version);

                    const loaded_version = await loadVersions(contract.version);
                    renderContractForm(loaded_version.fields, existingContractData);

                    loadProducts(contract_products);
                    $('.contract-contact').find('input, select, textarea').prop('disabled', false);

                    $('#generateContract').removeClass('d-none');

                    $('#regenarate').removeClass('d-none');
                    $('#generateContractWithOutSignature').addClass('d-none');

                    $('#contract_pdf_link').addClass('d-none').removeAttr('href');
                } catch (error) {
                    showToast(error, 'danger');
                }
                adminModal.show();
            }

            // Szerződés generálása

            $('#generateContract').on('click', function (e) {
                e.preventDefault();

                if (signaturePad.isEmpty()) {
                    alert('Kérlek, írd alá a szerződést!');
                    return;
                }

                document.getElementById('signature-input').value = signaturePad.toDataURL();
                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                submitContractForm(formData);

            });

            // Szerződés generálása aláírás nélkül
            $('#generateContractWithOutSignature').on('click', function (e) {
                e.preventDefault();

                document.getElementById('signature-input').value = '';
                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                submitContractForm(formData);
            });

            function submitContractForm(formData) {
                const doSubmit = () => {
                    $.ajax({
                        url: '{{ route('admin.contracts.store') }}',
                        method: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        beforeSend: () => {
                            showLoader();
                        },
                        success(response) {
                            showToast(response.message || 'Sikeres!', 'success');
                            table.ajax.reload(null, false);
                            adminModal.hide();

                            const worksheet = response?.data?.worksheet;
                            if (worksheet && worksheet.id) {
                                const worksheet_id = worksheet.id;
                                window.location.href = `{{ url('/admin/munkalapok') }}?id=${worksheet_id}`;
                            }
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
                            hideLoader();
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
                        mothers_name: ($('#mothers_name').val() || '').toString().trim() || null,
                        place_of_birth: ($('#place_of_birth').val() || '').toString().trim() || null,
                        date_of_birth: ($('#date_of_birth').val() || '').toString().trim() || null,
                        id_number: ($('#id_number').val() || '').toString().trim() || null,
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
                        .done(() => doSubmit())
                        .fail((xhr) => {
                            let msg = 'Hiba az ügyfél adatainak mentésekor!';
                            if (xhr.responseJSON?.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                            } else if (xhr.responseJSON?.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                            hideLoader();
                        });

                    return;
                }

                doSubmit();
            }

            // Betölti a szerződés verziókat

            async function loadVersions(version) {
                if (!version) {
                    $('#contractDataFieldsArea').empty();
                    return null;
                }

                try {
                    const res = await fetch(`${window.appConfig.APP_URL}admin/szerzodesek/verzio/${version}`);
                    const data = await res.json();

                    if (data.error) {
                        alert(data.error);
                        return null;
                    }

                    return data; // Itt valóban visszaadja a hívónak
                } catch (error) {
                    console.error('Hiba történt a verzió betöltésekor:', error);
                    alert('Hiba történt a verzió betöltésekor.');
                    return null;
                }
            }


            // Szeződés verzió kiválasztása

            $('#contract_version').on('change', async function () {
                const version = $(this).val();
                const loaded_version = await loadVersions(version);
                renderContractForm(loaded_version.fields);
            });

            // Szerződés mezők renderelése

            function renderContractForm(fields, existingData = null) {
                const container = $('#contractDataFieldsArea');
                container.empty();

                const row = $('<div class="row g-3"></div>');

                fields.forEach(field => {
                    let input = '';
                    let wrapperClass = 'col-12 col-md-6 col-lg-3'; // mobil: 1 oszlop, tablet: 2 oszlop, desktop: 4 oszlop

                    const isCommentField = /megjegy|megjegyz|comment|note|remark/i.test(field.key || '')
                        || /megjegy|megjegyz|comment|note|remark/i.test(field.label || '');

                    const inputName = `contract_data[${field.key}]`;
                    const value = existingData?.contract?.data?.[field.key] ?? '';

                    if (isCommentField) {
                        input = `<textarea name="${inputName}" class="form-control" rows="4">${value}</textarea>`;
                        wrapperClass = 'col-12';
                    } else {

                    switch (field.type) {
                        case 'text':
                            input = `<input type="text" name="${inputName}" value="${value}" class="form-control">`;
                            break;

                        case 'number':
                            input = `<input type="number" name="${inputName}" value="${value}" class="form-control">`;
                            break;

                        case 'date':
                            input = `<input type="date" name="${inputName}" value="${value}" class="form-control">`;
                            break;

                        case 'select':
                            const selectOptions = (field.options || []).map(opt => {
                                let optValue = typeof opt === 'object' ? opt.value : opt;
                                let optLabel = typeof opt === 'object' ? opt.label : opt;
                                let selected = (value && value == optValue) ? 'selected' : '';
                                return `<option value="${optValue}" ${selected}>${optLabel}</option>`;
                            }).join('');
                            input = `<select name="${inputName}" class="form-control">${selectOptions}</select>`;
                            break;

                        case 'boolean':
                            const checked = (value && (value === 1 || value === '1' || value === true)) ? 'checked' : '';
                            input = `
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="${inputName}" value="1" class="form-check-input" id="${field.key}" ${checked}>
                                    <label class="form-check-label" for="${field.key}">${field.label}</label>
                                </div>
                            `;
                            break;

                        case 'textarea':
                            input = `<textarea name="${inputName}" class="form-control" rows="4">${value}</textarea>`;
                            wrapperClass = 'col-12';
                            break;

                        case 'model':
                            const modelOptions = (field.options || []).map(opt => {
                                let selected = (value && value == opt.value) ? 'selected' : '';
                                return `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
                            }).join('');
                            input = `<select name="${inputName}" class="form-control">${modelOptions}</select>`;
                            break;

                        default:
                            input = `<input type="text" name="${inputName}" value="${value}" class="form-control">`;
                    }

                    }

                    let fieldHtml;

                    if (field.type !== 'boolean') {
                        fieldHtml = `
                <div class="${wrapperClass}">
                    <label class="form-label" for="${field.key}">${field.label}</label>
                    ${input}
                </div>
            `;
                    } else {
                        fieldHtml = `<div class="${wrapperClass}">${input}</div>`;
                    }

                    row.append(fieldHtml);
                });

                container.append(row);
                loadDefaultData();
            }

            function loadDefaultData() {
                const container = $('#contractDataFieldsArea');

                // Aktuális dátum beállítása contract_datetime mezőbe
                const $dateInput = container.find('[name="contract_data[contract_datetime]"]');
                if ($dateInput.length) {
                    const today = new Date().toISOString().split('T')[0];
                    $dateInput.val(today);
                }

                // Város beállítása contract_location mezőbe
                const $cityInput = $('#contact_city');
                const city = $cityInput.val();

                const $locationInput = container.find('[name="contract_data[contract_location]"]');
                if ($locationInput.length && city) {
                    $locationInput.val(city.trim());
                }
            }


            // Termékek betöltése

            function loadProducts(selectedProducts = []) {

                const productManagerTable = $('#productManagerTable tbody');
                productManagerTable.empty();

                // összegyűjtjük az összes selected product id-t
                const selectedIds = selectedProducts.map(p => p.pivot?.product_id ?? p.id);

                // gyors hozzáférés: selected map product_id → pivot
                const selectedMap = {};
                selectedProducts.forEach(p => {
                    const id = p.pivot?.product_id ?? p.id;
                    selectedMap[id] = {
                        gross_price: p.pivot?.gross_price ?? p.gross_price ?? '',
                        product_qty: p.pivot?.product_qty ?? 1
                    };
                });

                fetch(`${window.appConfig.APP_URL}admin/szerzodesek/szerzodes-termekek`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(category => {
                            const categoryRow = `
                    <tr class="table-secondary">
                        <td colspan="4"><strong>${category.title}</strong></td>
                    </tr>`;
                            productManagerTable.append(categoryRow);

                            category.products.forEach(item => {
                                const isChecked = selectedIds.includes(item.id) ? 'checked' : '';

                                // ha kiválasztott, vegyük a pivot értékeit
                                const grossPrice = selectedMap[item.id]?.gross_price ?? item.gross_price;
                                const productQty = selectedMap[item.id]?.product_qty ?? 1;

                                const row = `
                        <tr>
                            <td>
                                <input
                                    type="checkbox"
                                    name="products[${item.id}][selected]"
                                    value="1"
                                    id="product_${item.id}"
                                    ${isChecked}
                                >
                            </td>
                            <td><label for="product_${item.id}">${item.title}</label></td>
                            <td style="width: 200px">
                                <input
                                    type="number"
                                    class="form-control"
                                    name="products[${item.id}][gross_price]"
                                    value="${grossPrice}"
                                    step="1"
                                >
                            </td>
                            <td style="width: 100px">
                                <input
                                    type="number"
                                    min="1"
                                    name="products[${item.id}][product_qty]"
                                    step="1"
                                    value="${productQty}"
                                    class="form-control">
                            </td>
                        </tr>`;
                                productManagerTable.append(row);
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Hiba a termékek betöltésekor:', error);
                    });
            }



            let debounceTimeout;

            $('#contact_city').on('change', function() {
                loadDefaultData();
            });

            $('#contact_zip_code').on('input', function () {
                clearTimeout(debounceTimeout);

                debounceTimeout = setTimeout(() => {
                    let zip = $(this).val();

                    $.ajax({
                        url: window.appConfig.APP_URL + 'api/postal-codes/search?zip=' + zip,
                        type: 'GET',
                        success: function (data) {
                            const $suggestions = $('#zip_suggestions');
                            $suggestions.empty();

                            if (data.length > 0) {
                                data.forEach(row => {
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
                        }
                    });

                }, 300); // 300 ms debounce
            });

            $('#installation_date').on('change', function () {
                const installationDate = $(this).val();
                const date = new Date(installationDate);
                date.setMonth(date.getMonth() + 1);
                const formattedDate = date.toISOString().split('T')[0];
                $('#contractDataFieldsArea').find('[name="contract_data[completion_due_date]"]').val(formattedDate);
            });

            // Ha rákattintanak egy ajánlásra
            $('#zip_suggestions').on('click', 'button', function () {
                $('#contact_zip_code').val($(this).attr('data_zip'));
                $('#contact_city').val($(this).text().trim());
                $('#zip_suggestions').hide();
                loadDefaultData();
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

            function setClientFieldsVisible(visible) {
                $('.contract-client-fields').toggle(!!visible);
                $('#contact_name').prop('required', !!visible);
            }

            let clientSearchDebounce;

            $('#client_search').on('input', function () {
                const q = ($(this).val() || '').trim();
                clearTimeout(clientSearchDebounce);

                $('#client_search_results').hide().empty();

                if ($('#client_id').val() || $('#create_client').val() === '1') {
                    clearClientSelection();
                }

                if (q.length < 2) return;

                clientSearchDebounce = setTimeout(() => {
                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/ugyfelek/kereses?q=${encodeURIComponent(q)}`,
                        method: 'GET',
                        success: function (response) {
                            const clients = response?.clients || [];
                            const $list = $('#client_search_results');
                            $list.empty();

                            if (clients.length) {
                                clients.forEach(function (c) {
                                    const name = c?.name || '';
                                    const email = c?.email || '';
                                    const idNumber = c?.id_number || '';
                                    const phone = c?.phone || '';
                                    const addresses = Array.isArray(c.addresses) ? c.addresses : [];

                                    const headerParts = [idNumber, email].filter(Boolean).join(', ');
                                    $list.append(`
                                        <div class="list-group-item client-search-header">
                                            <div class="fw-bold">${escapeHtml(name || email || 'N/A')}${headerParts ? ' (' + escapeHtml(headerParts) + ')' : ''}</div>
                                        </div>
                                    `);

                                    addresses.forEach(function (a) {
                                        const addrText = [a?.zip_code, a?.city, a?.address_line].filter(Boolean).join(' ');
                                        $list.append(`
                                            <button type="button" class="list-group-item list-group-item-action client-address-item"
                                                data-id="${c.id}"
                                                data-address-id="${a?.id || ''}"
                                                data-name="${escapeHtml(name)}"
                                                data-email="${escapeHtml(email)}"
                                                data-phone="${escapeHtml(phone)}"
                                                data-country="${escapeHtml(a?.country || 'HU')}"
                                                data-zip="${escapeHtml(a?.zip_code || '')}"
                                                data-city="${escapeHtml(a?.city || '')}"
                                                data-line="${escapeHtml(a?.address_line || '')}"
                                                data-id-number="${escapeHtml(idNumber)}"
                                            >
                                                ${escapeHtml(addrText || 'Cím nélkül')}
                                            </button>
                                        `);
                                    });

                                    $list.append(`
                                        <button type="button" class="list-group-item list-group-item-action client-new-address"
                                            data-id="${c.id}"
                                            data-name="${escapeHtml(name)}"
                                            data-email="${escapeHtml(email)}"
                                            data-phone="${escapeHtml(phone)}"
                                            data-id-number="${escapeHtml(idNumber)}"
                                        >
                                            + Új cím ehhez az ügyfélhez
                                        </button>
                                    `);
                                });
                            } else {
                                $list.append(`
                                    <button type="button" class="list-group-item list-group-item-action client-create client-create-item">
                                        + Új ügyfél létrehozása: <strong>${escapeHtml(q)}</strong>
                                    </button>
                                `);
                            }

                            $list.show();
                        },
                        error: function () {
                            const $list = $('#client_search_results');
                            $list.empty();
                            $list.append(`
                                <button type="button" class="list-group-item list-group-item-action client-create client-create-item">
                                    + Új ügyfél létrehozása
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
                const addressId = $btn.data('address-id');
                const name = ($btn.data('name') || '').toString();
                const email = ($btn.data('email') || '').toString();
                const phone = ($btn.data('phone') || '').toString();
                const country = ($btn.data('country') || 'HU').toString();
                const zip = ($btn.data('zip') || '').toString();
                const city = ($btn.data('city') || '').toString();
                const line = ($btn.data('line') || '').toString();
                const idNumber = ($btn.data('id-number') || '').toString();

                $('#client_id').val(clientId);
                $('#client_address_id').val(addressId);
                $('#create_client').val('0');
                $('#use_custom_address').val('0');

                $('#contact_name').val(name);
                $('#contact_email').val(email);
                $('#contact_phone').val(phone);
                $('#contact_country').val(country);
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
                const name = ($btn.data('name') || '').toString();
                const email = ($btn.data('email') || '').toString();
                const phone = ($btn.data('phone') || '').toString();
                const idNumber = ($btn.data('id-number') || '').toString();

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

                $('#client_search').val('');
                $('#client_search_results').hide().empty();

                setClientFieldsVisible(true);
                setSnapshotMode(false);

                setTimeout(() => {
                    $('#contact_name').trigger('focus');
                }, 0);
            });

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

            function resetForm(title = null) {
                $('#adminModalLabel').text(title);

                $('#adminModalForm')[0].reset();
                $('#contract_id').val('');

                clearClientSelection();

                $('#client_search').val('');
                $('#client_search_results').hide().empty();
            }

            // Szerződés törlése
            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const contract_id = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd a szerződést?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/szerzodesek') }}/${contract_id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Szerződése sikeresen törölve!', 'success');
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a szerződés törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt a szerződés törlésekor', 'danger');
                }
            });

            document.getElementById('preview_contract').addEventListener('click', function (e) {
                e.preventDefault();

                document.getElementById('signature-input').value = signaturePad.toDataURL();

                const form = document.getElementById('adminModalForm');
                form.action = "{{ route('admin.contract.preview') }}";
                form.method = "POST";
                form.target = "_blank";
                form.submit();
            });



        });

    </script>
@endsection
