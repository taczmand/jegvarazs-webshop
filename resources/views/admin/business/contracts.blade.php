@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Ügyviteli folyamatok / Szerződések</h1>
            <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új szerződés</button>
        </div>

        <table class="table table-bordered" id="adminTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Név</th>
                <th>Ország</th>
                <th>Irányítószám</th>
                <th>Város</th>
                <th>Cím</th>
                <th>Készítette</th>
                <th>Létrehozva</th>
                <th>Műveletek</th>
            </tr>
            </thead>
        </table>
    </div>

    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="adminModalForm" action="" method="POST">
                <input type="hidden" id="customer_id" name="id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Vevő/partner szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">

                        <ul class="nav nav-tabs" id="productTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#contact" type="button">Kapcsolati adatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#contractDataManager" type="button">Szerződés adatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#productManager" type="button">Termékek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#contract" type="button">Szerződés generálás</button></li>
                        </ul>

                        <div class="tab-content mt-3">

                            <!-- Kapcsolati adatok tab -->

                            <div class="tab-pane fade show active" id="contact">
                                <div class="container contract-contact">
                                    <h5 class="mt-4">Kapcsolattartó adatok</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Név / Cégnév</label>
                                            <input type="text" class="form-control" name="contact_name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Ország</label>
                                            <select name="contact_country" class="form-control w-100" id="contact_country">
                                                @foreach(config('countries') as $code => $name)
                                                    <option value="{{ $code }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Irányítószám</label>
                                            <input type="text" class="form-control" name="contact_zip_code">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Város</label>
                                            <input type="text" class="form-control" name="contact_city">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Cím</label>
                                            <input type="text" class="form-control" name="contact_address_line">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Telefonszám</label>
                                            <input type="text" class="form-control" name="contact_phone">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">E-mail cím</label>
                                            <input type="email" class="form-control" name="contact_email">
                                        </div>
                                    </div>

                                    <h5 class="mt-4">Személyes adatok</h5>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Anyja neve</label>
                                            <input type="text" class="form-control" name="mothers_name">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Születési hely</label>
                                            <input type="text" class="form-control" name="place_of_birth">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Születési idő</label>
                                            <input type="date" class="form-control" name="date_of_birth">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Személyi szám</label>
                                            <input type="text" class="form-control" name="id_number">
                                        </div>
                                    </div>
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
                                <div style="max-height: 300px; overflow-y: auto">
                                    <table class="table table-bordered" id="productManagerTable">
                                        <thead>
                                        <tr>
                                            <th>Kiválasztás</th>
                                            <th>Termék</th>
                                            <th>Bruttó ár</th>
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
                                <button type="submit" class="btn btn-primary d-none" id="generateContract">
                                    <i class="fas fa-file-pdf"></i> Szerződés generálása
                                </button>
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
    <script type="module">

        const adminModalDOM = document.getElementById('adminModal');
        const adminModal = new bootstrap.Modal(adminModalDOM);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        $(document).ready(function() {
            const table = $('#adminTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.contracts.data') }}',
                columns: [
                    {data: 'id'},
                    {data: 'name'},
                    {data: 'country'},
                    {data: 'zip_code'},
                    {data: 'city'},
                    {data: 'address_line'},
                    {data: 'creator_name'},
                    {data: 'created'},
                    {data: 'action', orderable: false, searchable: false}
                ],
            });


            // Új szerződés létrehozása modal megjelenítése
            $('#addButton').on('click', async function () {
                try {
                    resetForm('Új szerződés létrehozása');
                    loadVersions("v1");
                    loadProducts();
                    $('.contract-contact').find('input, select, textarea').prop('disabled', false);

                    $('#generateContract').removeClass('d-none');
                    $('#contract_pdf_link').addClass('d-none').removeAttr('href');
                } catch (error) {
                    showToast(error, 'danger');
                }
                adminModal.show();
            });

            // Szerződés generálása

            $('#generateContract').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                $.ajax({
                    url: '{{ route('admin.contracts.store') }}',
                    method: 'POST',
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

                    }
                });

            });

            // Betölti a szerződés verziókat

            function loadVersions(version) {
                if (!version) {
                    $('#contractDataFieldsArea').empty();
                    return;
                }

                fetch(`/admin/szerzodesek/verzio/${version}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        renderContractForm(data.fields);
                    });
            }

            // Szeződés verzió kiválasztása

            $('#contract_version').on('change', function () {
                const version = $(this).val();
                loadVersions(version)
            });

            // Szerződés mezők renderelése

            function renderContractForm(fields) {
                const container = $('#contractDataFieldsArea');
                container.empty();

                const row = $('<div class="row g-3"></div>');

                fields.forEach(field => {
                    let input = '';
                    let wrapperClass = 'col-md-6'; // Két oszlopos elrendezés

                    const inputName = `contract_data[${field.key}]`;

                    switch (field.type) {
                        case 'text':
                            input = `<input type="text" name="${inputName}" class="form-control">`;
                            break;

                        case 'number':
                            input = `<input type="number" name="${inputName}" class="form-control">`;
                            break;

                        case 'date':
                            input = `<input type="date" name="${inputName}" class="form-control">`;
                            break;

                        case 'select':
                            const selectOptions = (field.options || []).map(opt => {
                                if (typeof opt === 'object') {
                                    return `<option value="${opt.value}">${opt.label}</option>`;
                                }
                                return `<option value="${opt}">${opt}</option>`;
                            }).join('');
                            input = `<select name="${inputName}" class="form-control">${selectOptions}</select>`;
                            break;

                        case 'boolean':
                            input = `
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="${inputName}" value="1" class="form-check-input" id="${field.key}">
                                    <label class="form-check-label" for="${field.key}">${field.label}</label>
                                </div>
                            `;
                            break;

                        case 'textarea':
                            input = `<textarea name="${inputName}" class="form-control" rows="4"></textarea>`;
                            wrapperClass = 'col-12';
                            break;

                        case 'model':
                            const modelOptions = (field.options || []).map(opt => {
                                return `<option value="${opt.value}">${opt.label}</option>`;
                            }).join('');
                            input = `<select name="${inputName}" class="form-control">${modelOptions}</select>`;
                            break;

                        default:
                            input = `<input type="text" name="${inputName}" class="form-control">`;
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
            }


            // Termékek betöltése

            function loadProducts() {
                const productManagerTable = $('#productManagerTable tbody');
                productManagerTable.empty();

                fetch(`/admin/termekek/kategoriakkal`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(category => {
                            const categoryRow = `
                        <tr class="table-secondary">
                            <td colspan="3"><strong>${category.title}</strong></td>
                        </tr>`;
                            productManagerTable.append(categoryRow);

                            category.products.forEach(item => {
                                const row = `
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        name="products[${item.id}][selected]"
                                        value="1"
                                    >
                                </td>
                                <td>${item.title}</td>
                                <td>
                                    <input
                                        type="number"
                                        class="form-control"
                                        name="products[${item.id}][gross_price]"
                                        value="${item.gross_price}"
                                        step="0.01"
                                    >
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

            // Form kiörítése és modal cím beállítása

            function resetForm(title = null) {
                $('#adminModalLabel').text(title);
            }

        });

    </script>
@endsection
