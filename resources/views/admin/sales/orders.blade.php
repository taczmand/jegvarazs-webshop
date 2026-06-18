@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Értékesítés / Rendelések</h2>
            @if(auth('admin')->user()->can('create-order'))
                <button type="button" class="btn btn-success" id="addOrder">
                    <i class="fas fa-plus me-1"></i> Új rendelés
                </button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-orders'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Vásárló" class="filter-input form-control" data-column="2">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="4">
                            <option value="">Állapot (összes)</option>
                            <option value="pending">Függőben</option>
                            <option value="processing">Feldolgozás alatt</option>
                            <option value="completed">Befejezve</option>
                            <option value="cancelled">Törölve</option>
                        </select>
                    </div>
                </div>


                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th data-priority="1">ID</th>
                        <th>Dátum</th>
                        <th data-priority="2">Vásárló</th>
                        <th>Partner?</th>
                        <th>Teljes összeg</th>
                        <th>Állapot</th>
                        <th>Termékek száma</th>
                        <th>Látta</th>
                        <th data-priority="3">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a rendelések megtekintésére!
                </div>
            @endif
        </div>
    </div>

    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">

            <form id="adminModalForm" enctype="multipart/form-data">

                <input type="hidden" id="order_id" name="id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">Rendelés szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">

                        <ul class="nav nav-tabs admin-modal-tabs" id="productTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#basic" type="button">Alapadatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#products" type="button">Termékek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#shipping" type="button">Szállítási adatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#billing" type="button">Számlázási adatok</button></li>
                            <li class="nav-item" id="order_history_tab"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#history" type="button">Rendelés történet</button></li>
                        </ul>

                        <div class="tab-content mt-3">

                            <!-- Alapadatok -->

                            <div class="tab-pane fade show active" id="basic">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Kapcsolattartó adatok</h5>
                                        <div id="customer_picker" class="d-none mb-3">
                                            <label class="form-label">Vásárló</label>
                                            <input type="hidden" id="customer_id" name="customer_id">
                                            <input type="text" class="form-control" id="customer_search" placeholder="Keresés (név/e-mail/telefon)..." autocomplete="off">
                                            <div id="customer_search_results" class="list-group mt-2" style="max-height: 160px; overflow: auto;"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="contact_last_name" class="form-label">Vezetéknév*</label>
                                            <input type="text" class="form-control" id="contact_last_name" name="contact_last_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="contact_first_name" class="form-label">Keresztnév*</label>
                                            <input type="text" class="form-control" id="contact_first_name" name="contact_first_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="contact_email" class="form-label">E-mail cím*</label>
                                            <input type="mail" class="form-control" id="contact_email" name="contact_email" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="contact_phone" class="form-label">Telefonszám*</label>
                                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr id="order_id_row">
                                                <td>Rendelés ID</td>
                                                <td><span id="order_id_display"></span></td>
                                            </tr>
                                            <tr>
                                                <td>Dátum</td>
                                                <td>
                                                    <span id="order_date_display"></span>
                                                    <input type="datetime-local" class="form-control d-none" id="order_date_input" name="order_date">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Vásárló</td>
                                                <td>
                                                    <span id="customer_name_display"></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Teljes összeg</td>
                                                <td><span id="total_amount_display"></span></td>
                                            </tr>
                                            <tr>
                                                <td>Fizetés módja</td>
                                                <td>
                                                    <span id="payment_method"></span>
                                                    <select name="payment_method" class="form-select d-none" id="payment_method_select">
                                                        <option value="cash">Készpénz</option>
                                                        <option value="bank_transfer">Banki átutalás</option>
                                                        <option value="cod">Utánvét</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Megjegyzés</td>
                                                <td><textarea rows="3" id="order_comment" name="order_comment" class="form-control"></textarea></td>
                                            </tr>
                                            <tr>
                                                <td>Állapot</td>
                                                <td>
                                                    <select name="status" class="form-select" id="order_status">
                                                        <option value="pending">Függőben</option>
                                                        <option value="processing">Folyamatban</option>
                                                        <option value="completed">Befejezve</option>
                                                        <option value="cancelled">Törölve</option>
                                                    </select>
                                            </tr>
                                        </table>

                                    </div>
                                </div>
                            </div>

                            <!-- Termékek lista -->

                            <div class="tab-pane fade" id="products">
                                <div class="d-flex flex-wrap gap-2 align-items-end mb-3 d-none" id="order_items_editor" style="display:none;">
                                    <div style="min-width: 260px; flex: 1;">
                                        <label class="form-label">Termék</label>
                                        <input type="text" class="form-control" id="product_search" placeholder="Termék keresés (név vagy ID)..." autocomplete="off">
                                        <div id="product_search_results" class="list-group mt-2" style="max-height: 200px; overflow: auto;"></div>
                                    </div>
                                    <div style="min-width: 220px;">
                                        <label class="form-label d-block">&nbsp;</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="show_partner_prices" name="show_partner_prices">
                                            <label class="form-check-label" for="show_partner_prices">
                                                Partneri árak mutatása
                                            </label>
                                        </div>
                                    </div>
                                    <div style="width: 140px;">
                                        <label class="form-label">Mennyiség</label>
                                        <input type="number" min="1" step="1" value="1" class="form-control" id="new_item_quantity">
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary" id="addOrderItem">Hozzáadás</button>
                                    </div>
                                </div>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Termék</th>
                                            <th>Bruttó ár</th>
                                            <th>Mennyiség</th>
                                            <th>Mértékegység</th>
                                            <th>AFA</th>
                                            <th>Összeg</th>
                                            <th id="order_items_actions_head" class="d-none" style="display:none;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="order_items_body">
                                        <!-- Rendelés termékek dinamikusan töltődik be -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Szállítási adatok -->

                            <div class="tab-pane fade" id="shipping">
                                <div class="row mb-2 d-none" id="shipping_address_picker_row">
                                    <div class="col-lg-12">
                                        <label class="form-label">Mentett szállítási cím</label>
                                        <select class="form-select" id="shipping_address_select">
                                            <option value="" selected>Új megadása</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="checkout__input">
                                            <p>Név<span>*</span></p>
                                            <input type="text" name="shipping_name" class="form-control" value="" id="shipping_name">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Ország<span>*</span></p>
                                            <select name="shipping_country" class="form-control w-100" id="shipping_country">
                                                @foreach(config('countries') as $code => $name)
                                                    <option value="{{ $code }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Város<span>*</span></p>
                                            <input type="text" name="shipping_city" class="form-control" value="" id="shipping_city">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Irányítószám<span>*</span></p>
                                            <input type="text" name="shipping_postal_code" class="form-control" value="" id="shipping_postal_code">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Cím<span>*</span></p>
                                            <input type="text" name="shipping_address_line" class="form-control" value="" id="shipping_address_line">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Számlázási adatok -->

                            <div class="tab-pane fade" id="billing">
                                <div class="row mb-2 d-none" id="billing_address_picker_row">
                                    <div class="col-lg-12">
                                        <label class="form-label">Mentett számlázási cím</label>
                                        <select class="form-select" id="billing_address_select">
                                            <option value="" selected>Új megadása</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Név<span>*</span></p>
                                            <input type="text" class="form-control" name="billing_name" value="" id="billing_name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Cég esetén adószám</p>
                                            <input type="text" name="billing_tax_number" class="form-control" value="" id="billing_tax_number">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Ország<span>*</span></p>
                                            <select name="billing_country" class="form-control w-100" id="billing_country">
                                                @foreach(config('countries') as $code => $name)
                                                    <option value="{{ $code }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Irányítószám<span>*</span></p>
                                            <input type="text" name="billing_postal_code" value="" id="billing_postal_code" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Város<span>*</span></p>
                                            <input type="text" name="billing_city" value="" id="billing_city" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="checkout__input">
                                            <p>Cím<span>*</span></p>
                                            <input type="text" name="billing_address" value="" id="billing_address" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rendelés történet -->

                            <div class="tab-pane fade" id="history" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Időpont</th>
                                            <th>Felhasználó</th>
                                            <th>Vásárló</th>
                                            <th>Művelet</th>
                                            <th>Adat</th>
                                        </tr>
                                    </thead>
                                    <tbody id="order_history_body">
                                        <!-- Rendelés történet dinamikusan töltődik be -->
                                    </tbody>
                                </table>
                            </div>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-btn" id="saveOrder">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <style>
        .order-item-thumb {
            width: 34px;
            height: 34px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid rgba(0,0,0,.15);
            background: #fff;
        }
    </style>
    <script type="module">

        const adminModalDOM = document.getElementById('adminModal');
        const adminModal = new bootstrap.Modal(adminModalDOM);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const build_app_url = (path) => {
            const base = (window.appConfig?.APP_URL || '').toString().replace(/\/+$/, '');
            const clean_path = (path || '').toString().replace(/^\/+/, '');
            return `${base}/${clean_path}`;
        };

        $(document).ready(function() {
            const table = $('#adminTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.orders.data') }}',
                order: [[0, 'desc']],
                columns: [
                    {data: 'id'},
                    {data: 'created_at'},
                    {data: 'customer_name'},
                    {data: 'is_partner'},
                    {data: 'total_amount'},
                    {data: 'status'},
                    {data: 'items_count'},
                    {data: 'viewed_by'},
                    {data: 'action', orderable: false, searchable: false}
                ],
            });

            // Szűrők beállítása

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            // Rendelés szerkesztése

            $('#adminTable').on('click', '.edit', async function () {

                setCreateMode(false);

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const order_data = await getOrderData(row_data.id);
                const order_items = await loadOrderItems(row_data.id);
                const order_history = await loadOrderHistory(row_data.id);

                $('#order_id').val(row_data.id);
                $('#order_id_display').text(row_data.id);
                $('#order_date_display').text(row_data.created_at);
                let displayName = row_data.customer_name;

                if (row_data.is_partner && row_data.is_partner === 'Igen') {
                    displayName += ' (Partner)';
                }

                $('#customer_name_display').text(displayName);
                $('#total_amount_display').text(row_data.total_amount);

                renderBasicData(order_data);
                renderOrderItems(order_items);
                renderHistory(order_history);
                renderShippingData(order_data);
                renderBillingData(order_data);

                sendViewRequest("order", row_data.id);
                table.ajax.reload(null, false);

                adminModal.show();
            });

            async function getOrderData(order_id) {
                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/rendeles') }}/${order_id}`);
                    if (!response.ok) {
                        throw new Error('Hiba az adatok lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Adat hiba:', error);
                    return [];
                }
            }

            async function loadOrderItems(order_id) {
                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/rendeles') }}/${order_id}/items`);
                    if (!response.ok) {
                        throw new Error('Hiba a termékek lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Termékek hiba:', error);
                    return [];
                }
            }

            async function loadOrderHistory(order_id) {
                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/rendeles') }}/${order_id}/history`);
                    if (!response.ok) {
                        throw new Error('Hiba a rendelés történet lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Rendelés történet hiba:', error);
                    return [];
                }
            }

            function renderBasicData(order_data) {
                const cid = (order_data?.customer_id || '').toString().trim();
                $('#customer_id').val(cid);
                if (cid) {
                    $('#show_partner_prices').prop('checked', false).prop('disabled', true);
                } else {
                    $('#show_partner_prices').prop('disabled', false);
                }
                $('#contact_last_name').val(order_data.contact_last_name || '');
                $('#contact_first_name').val(order_data.contact_first_name || '');
                $('#contact_email').val(order_data.contact_email || '');
                $('#contact_phone').val(order_data.contact_phone || '');
                $('#payment_method').text(order_data.payment_method_label);
                $('#order_comment').val(order_data.comment || '');
                $('#order_status').val(order_data.status || 'pending');
            }

            let createMode = false;
            let productsIndex = new Map();
            let selectedProductId = null;
            let createItems = [];
            let customerShippingAddresses = [];
            let customerBillingAddresses = [];

            function resetCreateState() {
                createItems = [];
                $('#order_items_body').empty();
                $('#customer_id').val('');
                $('#customer_search').val('');
                $('#customer_search_results').empty();
                $('#show_partner_prices').prop('checked', false).prop('disabled', false);
                $('#contact_last_name').val('');
                $('#contact_first_name').val('');
                $('#contact_email').val('');
                $('#contact_phone').val('');
                clearShippingInputs();
                clearBillingInputs();
                $('#product_search').val('');
                $('#product_search_results').empty();
                selectedProductId = null;
                $('#order_id').val('');
                $('#order_id_display').text('');
                $('#order_date_display').text('');
                $('#order_date_input').val(getLocalDateTimeValue());
                $('#customer_name_display').text('');
                $('#total_amount_display').text('');
                $('#payment_method').text('');
                $('#order_comment').val('');
                $('#order_status').val('pending');
                $('#payment_method_select').val('cash');

                customerShippingAddresses = [];
                customerBillingAddresses = [];
                renderCustomerAddressPickers();
            }

            function getLocalDateTimeValue(date = new Date()) {
                const pad = (n) => String(n).padStart(2, '0');
                const yyyy = date.getFullYear();
                const mm = pad(date.getMonth() + 1);
                const dd = pad(date.getDate());
                const hh = pad(date.getHours());
                const mi = pad(date.getMinutes());
                return `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
            }

            function setCreateMode(enabled) {
                createMode = !!enabled;

                $('#customer_picker').toggleClass('d-none', !createMode);
                $('#customer_name_display').toggleClass('d-none', false);

                $('#order_id_row').toggle(!createMode);
                $('#order_date_input').toggleClass('d-none', !createMode);
                $('#order_date_display').toggleClass('d-none', createMode);

                $('#adminModalLabel').text(createMode ? 'Rendelés létrehozása' : 'Rendelés szerkesztése');
                $('#order_history_tab').toggle(!createMode);
                $('#history').toggle(!createMode);

                $('#payment_method_select').toggleClass('d-none', !createMode);
                $('#payment_method').toggleClass('d-none', createMode);

                $('#shipping_address_picker_row').toggleClass('d-none', !createMode);
                $('#billing_address_picker_row').toggleClass('d-none', !createMode);

                $('#order_items_editor').toggleClass('d-none', false);
                $('#order_items_actions_head').toggleClass('d-none', false);

                $('#product_search').prop('disabled', false);
                $('#new_item_quantity').prop('disabled', false);
                $('#addOrderItem').prop('disabled', false);
                $('#show_partner_prices').prop('disabled', false);

                if (createMode) {
                    $('#show_partner_prices').prop('checked', true);
                }

                if (!createMode) {
                    createItems = [];
                    selectedProductId = null;
                    $('#product_search').val('');
                    $('#product_search_results').empty();
                    $('#new_item_quantity').val('1');
                    $('#show_partner_prices').prop('checked', true);
                }

                $('#saveOrder').text(createMode ? 'Létrehozás' : 'Mentés');
            }

            function renderCustomerAddressPickers() {
                const shipSel = document.getElementById('shipping_address_select');
                const billSel = document.getElementById('billing_address_select');
                if (!shipSel || !billSel) return;

                shipSel.innerHTML = '<option value="" selected>Új megadása</option>';
                customerShippingAddresses.forEach((a) => {
                    const id = a?.id;
                    if (!id) return;
                    const name = (a?.name || '').toString();
                    const city = (a?.city || '').toString();
                    const zip = (a?.zip_code || '').toString();
                    const line = (a?.address_line || '').toString();
                    const label = `${name}${zip || city || line ? ' - ' : ''}${[zip, city, line].filter(Boolean).join(' ')}`.trim();
                    const opt = document.createElement('option');
                    opt.value = String(id);
                    opt.textContent = label || `#${id}`;
                    shipSel.appendChild(opt);
                });

                billSel.innerHTML = '<option value="" selected>Új megadása</option>';
                customerBillingAddresses.forEach((a) => {
                    const id = a?.id;
                    if (!id) return;
                    const name = (a?.name || '').toString();
                    const city = (a?.city || '').toString();
                    const zip = (a?.zip_code || '').toString();
                    const line = (a?.address_line || '').toString();
                    const tax = (a?.tax_number || '').toString();
                    const parts = [zip, city, line].filter(Boolean).join(' ');
                    const label = `${name}${parts ? ' - ' + parts : ''}${tax ? ' (Adószám: ' + tax + ')' : ''}`.trim();
                    const opt = document.createElement('option');
                    opt.value = String(id);
                    opt.textContent = label || `#${id}`;
                    billSel.appendChild(opt);
                });

                $('#shipping_address_select').val('');
                $('#billing_address_select').val('');
            }

            async function loadCustomerAddresses(customerId) {
                customerShippingAddresses = [];
                customerBillingAddresses = [];
                renderCustomerAddressPickers();

                const id = (customerId || '').toString().trim();
                if (!id) return;

                try {
                    const res = await fetch(`{{ url('/admin/ertekesites/vevo') }}/${id}/cimek`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!res.ok) return;
                    const payload = await res.json().catch(() => ({}));
                    customerShippingAddresses = Array.isArray(payload?.shipping_addresses) ? payload.shipping_addresses : [];
                    customerBillingAddresses = Array.isArray(payload?.billing_addresses) ? payload.billing_addresses : [];
                    renderCustomerAddressPickers();
                } catch (e) {
                    console.error('Címek lekérése hiba:', e);
                }
            }

            function clearShippingInputs() {
                $('#shipping_name').val('');
                $('#shipping_country').val('HU');
                $('#shipping_postal_code').val('');
                $('#shipping_city').val('');
                $('#shipping_address_line').val('');
            }

            function clearBillingInputs() {
                $('#billing_name').val('');
                $('#billing_tax_number').val('');
                $('#billing_country').val('HU');
                $('#billing_postal_code').val('');
                $('#billing_city').val('');
                $('#billing_address').val('');
            }

            $('#shipping_address_select').on('change', function () {
                const id = ($(this).val() || '').toString();
                if (!id) {
                    clearShippingInputs();
                    return;
                }
                const addr = customerShippingAddresses.find(a => String(a?.id) === id);
                if (!addr) return;

                $('#shipping_name').val(addr.name || '');
                $('#shipping_country').val(addr.country || 'HU');
                $('#shipping_postal_code').val(addr.zip_code || '');
                $('#shipping_city').val(addr.city || '');
                $('#shipping_address_line').val(addr.address_line || '');
            });

            $('#billing_address_select').on('change', function () {
                const id = ($(this).val() || '').toString();
                if (!id) {
                    clearBillingInputs();
                    return;
                }
                const addr = customerBillingAddresses.find(a => String(a?.id) === id);
                if (!addr) return;

                $('#billing_name').val(addr.name || '');
                $('#billing_tax_number').val(addr.tax_number || '');
                $('#billing_country').val(addr.country || 'HU');
                $('#billing_postal_code').val(addr.zip_code || '');
                $('#billing_city').val(addr.city || '');
                $('#billing_address').val(addr.address_line || '');
            });

            adminModalDOM.addEventListener('hidden.bs.modal', function () {
                resetCreateState();
                setCreateMode(false);
            });

            function renderCreateItems() {
                const itemsBody = $('#order_items_body');
                itemsBody.empty();

                let total = 0;

                const cid = ($('#customer_id').val() || '').toString().trim();
                const showPartner = !cid && !!document.getElementById('show_partner_prices')?.checked;

                createItems.forEach((row, idx) => {
                    const p = productsIndex.get(String(row.product_id));
                    const title = (p?.title || '').toString();
                    const gross = Number((showPartner ? (p?.partner_gross_price ?? p?.effective_gross_price ?? p?.gross_price) : (p?.effective_gross_price ?? p?.gross_price)) || 0);
                    const tax = (p?.taxCategory?.tax_value ?? p?.tax_value ?? '');
                    const qty = Number(row.quantity || 0);
                    const line = gross * qty;
                    total += line;

                    const photoPath = (p?.main_photo_path || '').toString().replace(/^\/+/, '');
                    const img_src = photoPath ? build_app_url(`storage/${photoPath}`) : '';
                    const productCell = img_src
                        ? `<div class="d-flex align-items-center gap-2"><img src="${img_src}" data-full-src="${img_src}" class="order-item-thumb" alt="" /><span>${title}</span></div>`
                        : `${title}`;

                    const tr = `
                        <tr data-idx="${idx}">
                            <td>${productCell}</td>
                            <td>${gross} Ft</td>
                            <td>${qty}</td>
                            <td></td>
                            <td>${tax}%</td>
                            <td>${line} Ft</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-create-item">Törlés</button>
                            </td>
                        </tr>
                    `;
                    itemsBody.append(tr);
                });

                $('#total_amount_display').text(total ? `${total} Ft` : '');
            }

            $('#order_items_body').on('click', '.remove-create-item', function () {
                const idx = Number($(this).closest('tr').attr('data-idx'));
                if (Number.isNaN(idx)) return;
                createItems.splice(idx, 1);
                renderCreateItems();
            });

            $('#addOrderItem').on('click', function () {
                const productId = (selectedProductId || '').toString();
                const qty = Number($('#new_item_quantity').val() || 0);
                if (!productId || qty <= 0) return;

                if (createMode) {
                    const existing = createItems.find(i => String(i?.product_id) === String(productId));
                    if (existing) {
                        existing.quantity = Number(existing.quantity || 0) + qty;
                    } else {
                        createItems.push({ product_id: productId, quantity: qty });
                    }
                } else {
                    const p = productsIndex.get(String(productId));
                    if (!p) return;
                    const existing = editItems.find(i => !i?._delete && Number(i?.product_id) === Number(productId));
                    if (existing) {
                        existing.quantity = Number(existing.quantity || 0) + qty;
                        renderOrderItems(editItems);
                    } else {
                        editItems.push({
                            id: null,
                            product_id: Number(productId),
                            product_name: p.title || '',
                            quantity: qty,
                            gross_price: Number((p?.effective_gross_price ?? p?.gross_price ?? 0) || 0),
                            tax_value: (p?.taxCategory?.tax_value ?? p?.tax_value ?? ''),
                            product: p,
                            _delete: false,
                        });
                        renderOrderItems(editItems);
                    }
                }
                $('#new_item_quantity').val('1');
                $('#product_search').val('');
                $('#product_search_results').empty();
                selectedProductId = null;
                if (createMode) {
                    renderCreateItems();
                }
            });

            const debounce = (fn, wait) => {
                let t;
                return (...args) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...args), wait);
                };
            };

            const onCustomerSearch = debounce(async () => {
                const q = (document.getElementById('customer_search').value || '').trim();
                const wrap = document.getElementById('customer_search_results');
                wrap.innerHTML = '';
                if (q.length === 0) {
                    $('#customer_id').val('');
                    $('#customer_name_display').text('');
                    $('#show_partner_prices').prop('disabled', false);
                    renderCreateItems();
                    return;
                }
                if (q.length < 2) return;

                const url = new URL(`{{ route('admin.customers.search') }}`);
                url.searchParams.set('q', q);
                const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const payload = await res.json().catch(() => ({}));
                const customers = Array.isArray(payload?.customers) ? payload.customers : [];

                customers.forEach(c => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action';
                    const name = (c?.name || '').toString();
                    const email = (c?.email || '').toString();
                    const isPartner = !!c?.is_partner;
                    const partnerLabel = isPartner ? 'Partner' : 'Nem partner';
                    btn.textContent = `${name}${email ? ' - ' + email : ''} (${partnerLabel})`;
                    btn.addEventListener('click', () => {
                        $('#customer_id').val(c.id);
                        $('#customer_search').val(name);
                        $('#customer_name_display').text(name);
                        $('#show_partner_prices').prop('checked', false).prop('disabled', true);
                        $('#contact_last_name').val((c?.name || '').toString().split(' ')[0] || (c?.last_name || ''));
                        $('#contact_first_name').val((c?.name || '').toString().split(' ').slice(1).join(' ') || (c?.first_name || ''));
                        $('#contact_email').val((c?.email || '').toString());
                        $('#contact_phone').val((c?.phone || '').toString());
                        wrap.innerHTML = '';
                        loadCustomerAddresses(c.id);
                        renderCreateItems();
                    });
                    wrap.appendChild(btn);
                });
            }, 250);

            document.getElementById('customer_search').addEventListener('input', onCustomerSearch);

            const onProductSearch = debounce(async () => {
                const q = (document.getElementById('product_search').value || '').trim();
                const wrap = document.getElementById('product_search_results');
                wrap.innerHTML = '';
                selectedProductId = null;

                if (q.length < 2) return;

                const url = new URL(`{{ route('admin.products.search') }}`);
                url.searchParams.set('q', q);
                const cid = ($('#customer_id').val() || '').toString().trim();
                if (cid) {
                    url.searchParams.set('customer_id', cid);
                } else if (document.getElementById('show_partner_prices')?.checked) {
                    url.searchParams.set('show_partner_prices', '1');
                }
                const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const payload = await res.json().catch(() => ({}));
                const products = Array.isArray(payload?.products) ? payload.products : [];

                products.forEach(p => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action';
                    const price = Number((p?.effective_gross_price ?? p?.gross_price) || 0);

                    const photoPath = (p?.main_photo_path || '').toString().replace(/^\/+/, '');
                    const img_src = photoPath ? build_app_url(`storage/${photoPath}`) : '';
                    const label = `#${p.id} - ${p.title || ''}${price ? ' - ' + price + ' Ft' : ''}`;
                    btn.innerHTML = img_src
                        ? `<div class="d-flex align-items-center gap-2"><img src="${img_src}" class="order-item-thumb" alt="" /><span>${label}</span></div>`
                        : label;
                    btn.addEventListener('click', () => {
                        selectedProductId = String(p.id);
                        productsIndex.set(String(p.id), p);
                        $('#product_search').val(`#${p.id} - ${p.title || ''}`);
                        wrap.innerHTML = '';
                    });
                    wrap.appendChild(btn);
                });
            }, 250);

            document.getElementById('product_search').addEventListener('input', onProductSearch);

            document.getElementById('show_partner_prices').addEventListener('change', () => {
                const cid = ($('#customer_id').val() || '').toString().trim();
                if (cid) {
                    $('#show_partner_prices').prop('checked', false).prop('disabled', true);
                    return;
                }
                renderCreateItems();
            });

            $('#addOrder').on('click', async function () {
                resetCreateState();
                setCreateMode(true);
                renderCreateItems();
                adminModal.show();
            });

            let editItems = [];

            function renderOrderItems(items) {
                const itemsBody = $('#order_items_body');
                itemsBody.empty();

                editItems = Array.isArray(items) ? items.map(i => ({
                    id: i.id,
                    product_id: i.product_id,
                    product_name: i.product_name,
                    quantity: i.quantity,
                    gross_price: i.gross_price,
                    tax_value: i.tax_value,
                    product: i.product,
                    _delete: false,
                })) : [];

                editItems.forEach(item => {
                    const photos = Array.isArray(item?.product?.photos) ? item.product.photos : [];
                    const main_photo = photos.find(p => p && (p.is_main === true || p.is_main === 1 || p.is_main === '1'));
                    const photo = main_photo || photos[0] || null;

                    const photoPathFallback = (item?.product?.main_photo_path || '').toString().replace(/^\/+/, '');
                    const img_src = photo?.path
                        ? build_app_url(`storage/${photo.path}`)
                        : (photoPathFallback ? build_app_url(`storage/${photoPathFallback}`) : build_app_url('static_media/no-image.jpg'));

                    const unitLabel = item?.product?.unit
                        ? (item.product.unit.abbreviation || item.product.unit.name || '')
                        : '';
                    const row = `<tr data-item-id="${item.id}">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img
                                    src="${img_src}"
                                    data-full-src="${img_src}"
                                    class="order-item-thumb"
                                    alt=""
                                />
                                <span>${item.product_name}</span>
                            </div>
                        </td>
                        <td>${item.gross_price} Ft</td>
                        <td>
                            <input
                                type="number"
                                min="0"
                                step="1"
                                class="form-control form-control-sm edit-item-qty"
                                value="${item.quantity}"
                                style="width: 110px;"
                            />
                        </td>
                        <td>${unitLabel}</td>
                        <td>${item.tax_value}%</td>
                        <td>${item.gross_price * item.quantity} Ft</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-edit-item">Törlés</button>
                        </td>
                    </tr>`;
                    itemsBody.append(row);
                });
            }

            $('#order_items_body').on('input', '.edit-item-qty', function () {
                const id = String($(this).closest('tr').attr('data-item-id') || '');
                const qty = Number($(this).val() || 0);
                const row = editItems.find(i => String(i.id) === id);
                if (!row) return;
                row.quantity = Number.isFinite(qty) ? qty : row.quantity;
            });

            $('#order_items_body').on('click', '.delete-edit-item', function () {
                const id = String($(this).closest('tr').attr('data-item-id') || '');
                const row = editItems.find(i => String(i.id) === id);
                if (!row) return;
                row._delete = true;
                $(this).closest('tr').addClass('table-danger').hide();
            });

            const $imgPreview = $('<div id="order_item_image_preview" style="display:none; position: fixed; z-index: 2000; pointer-events:none; padding: 6px; background: #fff; border: 1px solid rgba(0,0,0,.2); border-radius: 6px; box-shadow: 0 8px 20px rgba(0,0,0,.2);"></div>');
            $('body').append($imgPreview);

            const update_preview_position = (e) => {
                const offset = 16;
                const max_w = window.innerWidth;
                const max_h = window.innerHeight;
                const w = $imgPreview.outerWidth() || 0;
                const h = $imgPreview.outerHeight() || 0;

                let left = (e.clientX || 0) + offset;
                let top = (e.clientY || 0) + offset;

                if (left + w > max_w - 8) left = (e.clientX || 0) - w - offset;
                if (top + h > max_h - 8) top = (e.clientY || 0) - h - offset;

                $imgPreview.css({ left: `${left}px`, top: `${top}px` });
            };

            $('#order_items_body')
                .on('mouseenter', '.order-item-thumb', function (e) {
                    const src = ($(this).attr('data-full-src') || $(this).attr('src') || '').toString();
                    if (!src) return;

                    $imgPreview.html(`<img src="${src}" style="display:block; width: 360px; height: 360px; object-fit: contain;" alt="" />`);
                    $imgPreview.show();
                    update_preview_position(e);
                })
                .on('mousemove', '.order-item-thumb', function (e) {
                    if (!$imgPreview.is(':visible')) return;
                    update_preview_position(e);
                })
                .on('mouseleave', '.order-item-thumb', function () {
                    $imgPreview.hide().empty();
                });

            function renderHistory(history) {
                const historyBody = $('#order_history_body');
                historyBody.empty();

                history.forEach(item => {
                    const createdAt = item.created_at ?? '';
                    const userName = item.user?.name ?? '';
                    const customerName = item.customer
                        ? `${item.customer.last_name ?? ''} ${item.customer.first_name ?? ''}`.trim()
                        : '';
                    const action = item.action ?? '';

                    let dataContent = '';
                    if (item.data) {
                        try {
                            const dataObj = typeof item.data === 'string' ? JSON.parse(item.data) : item.data;
                            dataContent = `<pre>${JSON.stringify(dataObj, null, 2)}</pre>`;
                        } catch (e) {
                            dataContent = item.data; // fallback: raw output if JSON.parse fails
                        }
                    }

                    const row = `<tr>
                        <td>${createdAt}</td>
                        <td>${userName}</td>
                        <td>${customerName}</td>
                        <td>${action}</td>
                        <td>${dataContent}</td>
                    </tr>`;

                    historyBody.append(row);
                });
            }

            function renderShippingData(order_data) {
                $('#shipping_name').val(order_data.shipping_name || '');
                $('#shipping_country').val(order_data.shipping_country || '');
                $('#shipping_city').val(order_data.shipping_city || '');
                $('#shipping_postal_code').val(order_data.shipping_postal_code || '');
                $('#shipping_address_line').val(order_data.shipping_address_line || '');

            }

            function renderBillingData(order_data) {
                $('#billing_name').val(order_data.billing_name || '');
                $('#billing_tax_number').val(order_data.billing_tax_number || '');
                $('#billing_country').val(order_data.billing_country || '');
                $('#billing_postal_code').val(order_data.billing_postal_code || '');
                $('#billing_city').val(order_data.billing_city || '');
                $('#billing_address').val(order_data.billing_address_line || '');
            }

            // Rendelés mentése

            $('#saveOrder').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const orderId = ($('#order_id').val() || '').toString().trim();

                let url;
                if (!orderId) {
                    url = `{{ route('admin.orders.store') }}`;
                    formData.set('comment', ($('#order_comment').val() || '').toString());
                    formData.set('payment_method', ($('#payment_method_select').val() || 'cash').toString());
                    const od = ($('#order_date_input').val() || '').toString().trim();
                    if (od) {
                        formData.set('order_date', od);
                    } else {
                        formData.delete('order_date');
                    }
                    const cid = ($('#customer_id').val() || '').toString().trim();
                    if (cid) {
                        formData.set('customer_id', cid);
                        formData.delete('show_partner_prices');
                    } else {
                        formData.delete('customer_id');
                        if (document.getElementById('show_partner_prices')?.checked) {
                            formData.set('show_partner_prices', '1');
                        } else {
                            formData.delete('show_partner_prices');
                        }
                    }

                    formData.set('billing_address_line', ($('#billing_address').val() || '').toString());

                    createItems.forEach((row, idx) => {
                        formData.append(`items[${idx}][product_id]`, row.product_id);
                        formData.append(`items[${idx}][quantity]`, row.quantity);
                    });
                } else {
                    url = `${window.appConfig.APP_URL}admin/ertekesites/rendelesek/${orderId}`;
                    formData.append('_method', 'PUT');

                    editItems.forEach((row, idx) => {
                        if (row.id) {
                            formData.append(`items[${idx}][id]`, row.id);
                        }
                        if (row.product_id) {
                            formData.append(`items[${idx}][product_id]`, row.product_id);
                        }
                        formData.append(`items[${idx}][quantity]`, row.quantity ?? 0);
                        if (row._delete) {
                            formData.append(`items[${idx}][delete]`, '1');
                        }
                    });
                }

                $.ajax({
                    url: url,
                    method: 'POST',
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

            // Rendelés törlése

            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const orderId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd ezt a rendelést? Figyelem! Minden adat törlődni fog a rendeléssel kapcsolatban!')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/ertekesites/rendelesek') }}/${orderId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Rendelés sikeresen törölve!', 'success');
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a rendelés törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt a rendelés törlésekor', 'danger');
                }
            });

        });

    </script>
@endsection
