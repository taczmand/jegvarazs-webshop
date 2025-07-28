@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Értékesítés / Vevők és partnerek</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-customers'))

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
                        <input type="text" placeholder="Email" class="filter-input form-control" data-column="3">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="4">
                            <option value="">Partner (összes)</option>
                            <option value="1">Igen</option>
                            <option value="0">Nem</option>
                        </select>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="5">
                            <option value="">Állapot (összes)</option>
                            <option value="active">Aktív</option>
                            <option value="inactive">Inaktív</option>
                        </select>
                    </div>
                </div>


                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Név</th>
                        <th>Telefonszám</th>
                        <th>Email</th>
                        <th>Partner?</th>
                        <th>Állapot</th>
                        <th>Létrehozva</th>
                        <th>Módosítva</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a vevők és partnerek megtekintésére!
                </div>
            @endif
        </div>
    </div>

    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">


            <input type="hidden" id="customer_id" name="id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminModalLabel">Vevő/partner szerkesztése</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <div class="modal-body">

                    <ul class="nav nav-tabs" id="productTab" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#basic" type="button">Alapadatok</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cart" type="button">Kosár tartalma</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#orders" type="button">Rendelések</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#shipping" type="button">Szállítási címek</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#billing" type="button">Számlázási címek</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pricemanager" type="button">Árazó</button></li>
                    </ul>

                    <div class="tab-content mt-3">

                        <!-- Alapadatok -->

                        <div class="tab-pane fade show active" id="basic">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th>ID</th>
                                    <td id="customer_id_display"></td>
                                </tr>
                                <tr>
                                    <th>Vezetéknév</th>
                                    <td>
                                        <input type="text" class="form-control" id="customer_last_name" name="customer_last_name" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Keresztnév</th>
                                    <td>
                                        <input type="text" class="form-control" id="customer_first_name" name="customer_first_name" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Telefonszám</th>
                                    <td>
                                        <input type="text" class="form-control" id="customer_phone" name="customer_phone" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Email cím</th>
                                    <td>
                                        <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Partner?</th>
                                    <td>
                                        <select class="form-select" id="customer_is_partner" name="customer_is_partner">
                                            <option value="0">Nem</option>
                                            <option value="1">Igen</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Állapot</th>
                                    <td>
                                        <select class="form-select" id="customer_status" name="customer_status">
                                            <option value="inactive">Inaktív</option>
                                            <option value="active">Aktív</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Új jelszó</th>
                                    <td>
                                        <input type="password" class="form-control" id="customer_password" name="customer_password">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Létrehozva</th>
                                    <td id="customer_created_at"></td>
                                </tr>
                                <tr>
                                    <th>Módosítva</th>
                                    <td id="customer_updated_at"></td>
                                </tr>
                                <tr>
                                    <th>Műveletek</th>
                                    <td>
                                        <button class="btn btn-success" id="saveCustomer">Mentés</button>
                                    </td>
                                </tbody>
                            </table>
                        </div>

                        <!-- Kosár tartalma -->

                        <div class="tab-pane fade" id="cart">
                            <table class="table table-bordered" id="cartTable">
                                <thead>
                                <tr>
                                    <th>Termék</th>
                                    <th>Bruttó egységár</th>
                                    <th>Mennyiség</th>
                                    <th>Létrehozva</th>
                                    <th>Módosítva</th>
                                    <th>Műveletek</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Kosár elemek itt jelennek meg -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Rendelések -->

                        <div class="tab-pane fade" id="orders">

                            <!-- TODO : Rendelés tételek megjelenítése? -->

                            <div class="d-none" id="order_items">
                                <h3>Rendelés tételek</h3>
                                <table class="table table-bordered" id="orderItemsTable">
                                    <thead>
                                    <tr>
                                        <th>Termék</th>
                                        <th>Bruttó egységár</th>
                                        <th>Mennyiség</th>
                                        <th>Összeg</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <!-- Rendelés tételek itt jelennek meg -->
                                    </tbody>
                                </table>
                            </div>
                            <table class="table table-bordered" id="ordersTable">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Állapot</th>
                                    <th>Rendelés dátuma</th>
                                    <th>Módosítva</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Rendelések itt jelennek meg -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Szállítási címek -->

                        <div class="tab-pane fade" id="shipping">
                            <table class="table table-bordered" id="shippingTable">
                                <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>E-mail</th>
                                    <th>Telefon</th>
                                    <th>Ország</th>
                                    <th>Irányítószám</th>
                                    <th>Város</th>
                                    <th>Cím</th>
                                    <th>Műveletek</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Szállítási címek itt jelennek meg -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Számlázási címek -->

                        <div class="tab-pane fade" id="billing">
                            <table class="table table-bordered" id="billingTable">
                                <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>Adószám</th>
                                    <th>Ország</th>
                                    <th>Irányítószám</th>
                                    <th>Város</th>
                                    <th>Cím</th>
                                    <th>Műveletek</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Számlázási címek itt jelennek meg -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Árazó -->
                        <div class="tab-pane fade" id="pricemanager">
                            <i id="only_partner_msg">Csak partnerek számára adható egyedi ár!</i>
                            <div id="partner_prices_section">
                                <label for="discount_percentage" class="form-label">Kedvezményes százalékos ár beállítása az összes termékre az alap bruttó árból számolva:</label>
                                <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" min="0" max="100" step="0.01" value="0">
                                <button class="btn btn-success mt-2" id="applyDiscount">Százalékos ár beállítása az összes termékre</button>
                                <button class="btn btn-danger mt-2" id="resetPartnerPrices">Egyedi árak törlése az összes terméknél</button>
                                <input type="text" class="form-control mt-2" id="searchProduct" placeholder="Termék keresése...">
                                <div style="max-height: 400px; overflow-y: auto;" class="mt-2">
                                    <table class="table table-bordered" id="priceManagerTable">
                                        <thead>
                                        <tr>
                                            <th>Termék</th>
                                            <th>Bruttó ár</th>
                                            <th>Alapértelmezett partner ár (bruttó)</th>
                                            <th>Egyedi partner ár (bruttó)</th>
                                            <th>Műveletek</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <!-- Árazó tételek itt jelennek meg -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">

        const adminModalDOM = document.getElementById('adminModal');
        const adminModal = new bootstrap.Modal(adminModalDOM);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        $(document).ready(function() {

            var customer_data = null;

            const table = $('#adminTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.customers.data') }}',
                order: [[0, 'desc']],
                columns: [
                    {data: 'id'},
                    {data: 'customer_name'},
                    {data: 'phone'},
                    {data: 'email'},
                    {data: 'is_partner', render: function(data, type, row) {
                        return data ? 'Igen' : 'Nem';
                    }},
                    {data: 'status', render: function(data, type, row) {
                        return data === 'active' ? 'Aktív' : 'Inaktív';
                    }},
                    {data: 'created'},
                    {data: 'updated'},
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

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const customer_all_data = await getCustomerData(row_data.id);
                customer_data = customer_all_data.customer;

                renderCartItems(customer_data.cart?.items);
                renderOrders(customer_data.orders);
                renderShippingData(customer_all_data);
                renderBillingData(customer_all_data);
                loadProductsWithPartnerPrices(row_data.id, customer_data.is_partner);

                $('#customer_id').val(row_data.id);
                $('#customer_id_display').text(row_data.id);
                $('#customer_last_name').val(customer_data.last_name);
                $('#customer_first_name').val(customer_data.first_name);
                $('#customer_phone').val(customer_data.phone);
                $('#customer_email').val(customer_data.email);
                $('#customer_is_partner').val(customer_data.is_partner ? 1 : 0);
                $('#customer_status').val(customer_data.status === 'active' ? 'active' : 'inactive');
                $('#customer_created_at').text(customer_data.created_at);
                $('#customer_updated_at').text(customer_data.updated_at);

                sendViewRequest("customers", row_data.id);

                adminModal.show();
            });

            $(document).on('input', '#searchProduct', function() {
                loadProductsWithPartnerPrices($('#customer_id_display').text(), $('#customer_is_partner').val());
            });

            async function getCustomerData(customer_id) {
                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/vevo') }}/${customer_id}`);
                    if (!response.ok) {
                        throw new Error('Hiba az adatok lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Adat hiba:', error);
                    return [];
                }
            }

            function renderCartItems(cart_items) {
                const tbody = $('#cartTable tbody');
                tbody.empty();
                if (!cart_items || cart_items.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center">A kosár üres</td></tr>');
                    return;
                }
                cart_items.forEach(item => {

                    const product = item.product;

                    const row = `<tr id="cart_item_${item.id}">
                        <td>${product.title}</td>
                        <td>${product.gross_price}</td>
                        <td>${item.quantity}</td>
                        <td>${item.created_at}</td>
                        <td>${item.updated_at}</td>
                        <td>
                            <button class="btn btn-danger delete" data-id="${item.id}">Törlés</button>
                        </td>
                    </tr>`;
                    tbody.append(row);
                });
            }

            function renderOrders(orders) {
                const tbody = $('#ordersTable tbody');
                tbody.empty();
                orders.forEach(order => {
                    const row = `<tr>
                        <td>${order.id}</td>
                        <td>${order.status}</td>
                        <td>${order.created_at}</td>
                        <td>${order.updated_at}</td>
                    </tr>`;
                    tbody.append(row);
                });
            }

            function renderShippingData(customer_all_data) {

                const shipping_addresses = customer_all_data.customer.shipping_addresses || [];
                const countries = customer_all_data.countries || [];

                const tbody = $('#shippingTable tbody');
                tbody.empty();
                shipping_addresses.forEach(address => {

                    let countryOptions = '';

                    Object.entries(countries).forEach(([code, name]) => {
                        const selected = code === address.country ? 'selected' : '';
                        countryOptions += `<option value="${code}" ${selected}>${name}</option>`;
                    });

                    const row = `<tr>
                        <td><input type="text" class="form-control" value="${address.name}"></td>
                        <td><input type="email" class="form-control" value="${address.email}"></td>
                        <td><input type="text" class="form-control" value="${address.phone}"></td>
                        <td>
                            <select class="form-select">
                                ${countryOptions}
                            </select>
                        </td>
                        <td><input type="text" class="form-control" value="${address.zip_code}"></td>
                        <td><input type="text" class="form-control" value="${address.city}"></td>
                        <td><input type="text" class="form-control" value="${address.address_line}"></td>
                        <td>
                            <button class="btn btn-primary edit-shipping" data-id="${address.id}"><i class="fas fa-save"></i></button>
                            <button class="btn btn-danger delete-shipping" data-id="${address.id}"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
                    tbody.append(row);
                });
            }

            function renderBillingData(customer_all_data) {

                const billing_addresses = customer_all_data.customer.billing_addresses || [];
                const countries = customer_all_data.countries || [];

                const tbody = $('#billingTable tbody');
                tbody.empty();
                billing_addresses.forEach(address => {

                    let countryOptions = '';

                    Object.entries(countries).forEach(([code, name]) => {
                        const selected = code === address.country ? 'selected' : '';
                        countryOptions += `<option value="${code}" ${selected}>${name}</option>`;
                    });

                    const row = `<tr>
                        <td><input type="text" class="form-control" value="${address.name}"></td>
                        <td><input type="text" class="form-control" value="${address.tax_number}"></td>
                        <td>
                            <select class="form-select">
                                ${countryOptions}
                            </select>
                        </td>
                        <td><input type="text" class="form-control" value="${address.zip_code}"></td>
                        <td><input type="text" class="form-control" value="${address.city}"></td>
                        <td><input type="text" class="form-control" value="${address.address_line}"></td>
                        <td>
                            <button class="btn btn-primary edit-billing" data-id="${address.id}"><i class="fas fa-save"></i></button>
                            <button class="btn btn-danger delete-billing" data-id="${address.id}"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`;
                    tbody.append(row);
                });
            }

            function loadProductsWithPartnerPrices(customer_id, is_partner) {

                if (1 == is_partner) {

                    $('#only_partner_msg').hide();
                    $('#partner_prices_section').show();

                    const priceManagerTable = $('#priceManagerTable tbody');
                    priceManagerTable.empty();

                    fetch(`{{ url('/admin/ertekesites/partner/arazo') }}/${customer_id}/?product_search=${$('#searchProduct').val()}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(item => {
                                const partner_products = item.partner_products || [];
                                const discount_gross_price_value = partner_products[0]?.discount_gross_price ?? '';

                                const row = `<tr>
                                <td>${item.title}</td>
                                <td>${item.gross_price}</td>
                                <td>${item.partner_gross_price}</td>
                                <td><input type="number" class="form-control" value="${discount_gross_price_value}"></td>
                                <td>
                                    <button class="btn btn-success edit-price" data-product_id="${item.id}" title="Egyedi ár mentése"><i class="fas fa-save"></i></button>
                                    <button class="btn btn-danger delete-price" data-product_id="${item.id}" title="Egyedi ár törlése"><i class="fa-solid fa-eraser"></i></button>
                                </td>
                            </tr>`;
                                priceManagerTable.append(row);
                            });
                        })
                        .catch(error => {
                            console.error('Hiba a termékek betöltésekor:', error);
                        });
                } else {
                    $('#only_partner_msg').show();
                    $('#partner_prices_section').hide();
                }



            }

            $('#saveCustomer').on('click', async function () {
                const customer_id = $('#customer_id').val();
                const last_name = $('#customer_last_name').val();
                const first_name = $('#customer_first_name').val();
                const phone = $('#customer_phone').val();
                const email = $('#customer_email').val();
                const is_partner = $('#customer_is_partner').val();
                const customer_status = $('#customer_status').val();
                const password = $('#customer_password').val();

                if (!last_name || !first_name || !phone || !email) {
                    showToast('Kérjük, töltsön ki minden kötelező mezőt!', 'danger');
                    return;
                }

                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/vevo') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: customer_id,
                            last_name: last_name,
                            first_name: first_name,
                            phone: phone,
                            email: email,
                            is_partner: is_partner,
                            status: customer_status,
                            password: password
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Hiba a vevő mentésekor');
                    }

                    showToast('Vevő sikeresen mentve!', 'success');
                    loadProductsWithPartnerPrices(customer_id, is_partner);
                    table.ajax.reload();
                } catch (error) {
                    showToast(error.message || 'Hiba történt a vevő mentésekor', 'danger');
                }
            });

            $('#cartTable').on('click', '.delete', async function () {
                const itemId = $(this).data('id');
                if (!confirm('Biztosan törölni szeretnéd ezt a kosár elemet?')) return;

                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/vevo/kosar/torol') }}/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Hiba a kosár elem törlésekor');
                    }

                    showToast('Kosár elem sikeresen törölve!', 'success');
                    $('#cart_item_' + itemId).remove();
                } catch (error) {
                    showToast(error.message || 'Hiba történt a kosár elem törlésekor', 'danger');
                }
            });

            $('#shippingTable').on('click', '.edit-shipping', async function () {
                const row = $(this).closest('tr');
                const shipping_id = $(this).data('id');
                const name = row.find('input[type="text"]').eq(0).val();
                const email = row.find('input[type="email"]').val();
                const phone = row.find('input[type="text"]').eq(1).val();
                const country = row.find('select').val();
                const zip_code = row.find('input[type="text"]').eq(2).val();
                const city = row.find('input[type="text"]').eq(3).val();
                const address_line = row.find('input[type="text"]').eq(4).val();

                if (!name || !email || !phone || !zip_code || !city || !address_line) {
                    showToast('Kérjük, töltsön ki minden mezőt!', 'danger');
                    return;
                }

                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/vevo/szallitasi-cim') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: shipping_id,
                            customer_id: customer_data.id,
                            name: name,
                            email: email,
                            phone: phone,
                            country: country,
                            zip_code: zip_code,
                            city: city,
                            address_line: address_line
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Hiba a szállítási cím frissítésekor');
                    }

                    showToast('Szállítási cím sikeresen frissítve!', 'success');
                } catch (error) {
                    showToast(error.message || 'Hiba történt a szállítási cím frissítésekor', 'danger');
                }
            });

            $('#shippingTable').on('click', '.delete-shipping', async function () {
                const shipping_id = $(this).data('id');

                if (!confirm('Biztosan törölni szeretnéd ezt a szállítási címet?')) return;

                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/vevo/szallitasi-cim/torol') }}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: shipping_id,
                            customer_id: customer_data.id
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Hiba a szállítási cím törlésekor');
                    }

                    showToast('Szállítási cím sikeresen törölve!', 'success');
                    $(this).closest('tr').remove();
                } catch (error) {
                    showToast(error.message || 'Hiba történt a szállítási cím törlésekor', 'danger');
                }
            });

            $('#billingTable').on('click', '.edit-billing', async function () {
                const row = $(this).closest('tr');
                const billing_id = $(this).data('id');
                const name = row.find('input[type="text"]').eq(0).val();
                const tax_number = row.find('input[type="text"]').eq(1).val();
                const country = row.find('select').val();
                const zip_code = row.find('input[type="text"]').eq(2).val();
                const city = row.find('input[type="text"]').eq(3).val();
                const address_line = row.find('input[type="text"]').eq(4).val();

                if (!name || !tax_number || !zip_code || !city || !address_line) {
                    showToast('Kérjük, töltsön ki minden mezőt!', 'danger');
                    return;
                }

                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/vevo/szamlazasi-cim') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: billing_id,
                            customer_id: customer_data.id,
                            name: name,
                            tax_number: tax_number,
                            country: country,
                            zip_code: zip_code,
                            city: city,
                            address_line: address_line
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Hiba a számlázási cím frissítésekor');
                    }

                    showToast('Számlázási cím sikeresen frissítve!', 'success');
                } catch (error) {
                    showToast(error.message || 'Hiba történt a számlázási cím frissítésekor', 'danger');
                }
            });

            $('#billingTable').on('click', '.delete-billing', async function () {
                const billing_id = $(this).data('id');

                if (!confirm('Biztosan törölni szeretnéd ezt a számlázási címet?')) return;

                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/vevo/szamlazasi-cim/torol') }}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: billing_id,
                            customer_id: customer_data.id
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Hiba a számlázási cím törlésekor');
                    }

                    showToast('Számlázási cím sikeresen törölve!', 'success');
                    $(this).closest('tr').remove();
                } catch (error) {
                    showToast(error.message || 'Hiba történt a számlázási cím törlésekor', 'danger');
                }
            });

            $('#priceManagerTable').on('click', '.edit-price', async function () {
                const row = $(this).closest('tr');
                const product_id = $(this).data('product_id');
                const discount_gross_price = row.find('input[type="number"]').val();

                if (!discount_gross_price) {
                    showToast('Kérjük, adjon meg egy érvényes ár értéket!', 'danger');
                    return;
                }

                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/partner/arazo') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            customer_id: customer_data.id,
                            product_id: product_id,
                            discount_gross_price: discount_gross_price
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Hiba az ár frissítésekor');
                    }

                    showToast('Ár sikeresen frissítve!', 'success');
                } catch (error) {
                    showToast(error.message || 'Hiba történt az ár frissítésekor', 'danger');
                }
            });

            $('#priceManagerTable').on('click', '.delete-price', async function () {
                const product_id = $(this).data('product_id');

                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/partner/arazo') }}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            customer_id: customer_data.id,
                            product_id: product_id
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Hiba az ár törlésekor');
                    }
                    const row = $(this).closest('tr');
                    row.find('input[type="number"]').val("");

                    showToast('Egyedi ár sikeresen törölve!', 'success');
                } catch (error) {
                    showToast(error.message || 'Hiba történt az egyedi ár törlésekor', 'danger');
                }
            });

            $('#applyDiscount').on('click', async function () {
                if (!confirm('Biztosan beállítod az összes terméknél a százalékos árat?')) return;
                const discount_percentage = $('#discount_percentage').val();
                if (discount_percentage < 0 || discount_percentage > 100) {
                    showToast('Kedvezmény százalék 0 és 100 között kell legyen!', 'danger');
                    return;
                }

                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/partner/szazalek') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            customer_id: customer_data.id,
                            discount_percentage: discount_percentage
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Hiba a kedvezmény alkalmazásakor');
                    }

                    showToast('Kedvezmény sikeresen alkalmazva!', 'success');
                    loadProductsWithPartnerPrices(customer_data.id, customer_data.is_partner);
                } catch (error) {
                    showToast(error.message || 'Hiba történt a kedvezmény alkalmazásakor', 'danger');
                }
            });

            $('#resetPartnerPrices').on('click', async function () {
                if (!confirm('Biztosan törölni szeretnéd az összes egyedi árat?')) return;

                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/partner/arazo/torol') }}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            customer_id: customer_data.id
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Hiba az egyedi árak törlésekor');
                    }

                    showToast('Egyedi árak sikeresen törölve!', 'success');
                    loadProductsWithPartnerPrices(customer_data.id, customer_data.is_partner);
                } catch (error) {
                    showToast(error.message || 'Hiba történt az egyedi árak törlésekor', 'danger');
                }
            });

            // Vásárló törlése

            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const customerId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd a vevőt? Figyelem! Minden adat törlődni fog a vevővel kapcsolatban!')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/ertekesites/vevok') }}/${customerId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Vevő vagy partner sikeresen törölve!', 'success');
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt a vevő vagy partner törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt a termék törlésekor', 'danger');
                }
            });


        });
    </script>
@endsection
