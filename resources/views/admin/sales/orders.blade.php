@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Értékesítés / Rendelések</h2>
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
                        <th>Teljes összeg</th>
                        <th>Állapot</th>
                        <th>Termékek száma</th>
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

                        <ul class="nav nav-tabs" id="productTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#basic" type="button">Alapadatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#products" type="button">Termékek</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#shipping" type="button">Szállítási adatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#billing" type="button">Számlázási adatok</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#history" type="button">Rendelés történet</button></li>
                        </ul>

                        <div class="tab-content mt-3">

                            <!-- Alapadatok -->

                            <div class="tab-pane fade show active" id="basic">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Kapcsolattartó adatok</h5>
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
                                            <tr>
                                                <td>Rendelés ID</td>
                                                <td><span id="order_id_display"></span></td>
                                            </tr>
                                            <tr>
                                                <td>Dátum</td>
                                                <td><span id="order_date_display"></span></td>
                                            </tr>
                                            <tr>
                                                <td>Vásárló</td>
                                                <td><a href=""><span id="customer_name_display"></span></a></td>
                                            </tr>
                                            <tr>
                                                <td>Teljes összeg</td>
                                                <td><span id="total_amount_display"></span></td>
                                            </tr>
                                            <tr>
                                                <td>Fizetés módja</td>
                                                <td><span id="payment_method"></span></td>
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
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Termék</th>
                                            <th>Bruttó ár</th>
                                            <th>Mennyiség</th>
                                            <th>AFA</th>
                                            <th>Összeg</th>
                                        </tr>
                                    </thead>
                                    <tbody id="order_items_body">
                                        <!-- Rendelés termékek dinamikusan töltődik be -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Szállítási adatok -->

                            <div class="tab-pane fade" id="shipping">
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
    <script type="module">

        const adminModalDOM = document.getElementById('adminModal');
        const adminModal = new bootstrap.Modal(adminModalDOM);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                    {data: 'total_amount'},
                    {data: 'status'},
                    {data: 'items_count'},
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
                const order_data = await getOrderData(row_data.id);
                const order_items = await loadOrderItems(row_data.id);
                const order_history = await loadOrderHistory(row_data.id);

                $('#order_id').val(row_data.id);
                $('#order_id_display').text(row_data.id);
                $('#order_date_display').text(row_data.created_at);
                $('#customer_name_display').text(row_data.customer_name);
                $('#total_amount_display').text(row_data.total_amount);

                renderBasicData(order_data);
                renderOrderItems(order_items);
                renderHistory(order_history);
                renderShippingData(order_data);
                renderBillingData(order_data);

                sendViewRequest("orders", row_data.id);

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
                $('#contact_last_name').val(order_data.contact_last_name || '');
                $('#contact_first_name').val(order_data.contact_first_name || '');
                $('#contact_email').val(order_data.contact_email || '');
                $('#contact_phone').val(order_data.contact_phone || '');
                $('#payment_method').text(order_data.payment_method);
                $('#order_comment').val(order_data.comment || '');
                $('#order_status').val(order_data.status || 'pending');
            }

            function renderOrderItems(items) {
                const itemsBody = $('#order_items_body');
                itemsBody.empty();

                items.forEach(item => {
                    const row = `<tr>
                        <td>${item.product_name}</td>
                        <td>${item.gross_price} Ft</td>
                        <td>${item.quantity}</td>
                        <td>${item.tax_value}%</td>
                        <td>${item.gross_price * item.quantity} Ft</td>
                    </tr>`;
                    itemsBody.append(row);
                });
            }

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

                const orderId = $('#order_id').val();

                const url = `${window.appConfig.APP_URL}admin/ertekesites/rendelesek/${orderId}`;
                formData.append('_method', 'PUT');

                $.ajax({
                    url: url,
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
                            table.ajax.reload();
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
