@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Értékesítés / Rendelések</h1>
        </div>

        <table class="table table-bordered" id="adminTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Dátum</th>
                <th>Vásárló</th>
                <th>Teljes összeg</th>
                <th>Állapot</th>
                <th>Termékek száma</th>
                <th>Műveletek</th>
            </tr>
            </thead>
        </table>
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
                            <div class="tab-pane fade show active" id="basic">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="contact_last_name" class="form-label">Vezetéknév</label>
                                            <input type="text" class="form-control" id="contact_last_name" name="contact_last_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="contact_first_name" class="form-label">Keresztnév</label>
                                            <input type="text" class="form-control" id="contact_first_name" name="contact_first_name" required>
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
                                                <td><span id="customer_name_display"></span></td>
                                            </tr>
                                            <tr>
                                                <td>Teljes összeg</td>
                                                <td><span id="total_amount_display"></span></td>
                                            </tr>
                                        </table>
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Termék</th>
                                                <th>Mennyiség</th>
                                                <th>Ár</th>
                                            </tr>
                                            </thead>
                                            <tbody id="order_items_body">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="shipping">
                                Szállítási adatok itt jelennek meg.
                            </div>
                            <div class="tab-pane fade" id="billing">
                                Számlázási adatok itt jelennek meg.
                            </div>
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
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.orders.data') }}',
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

            // Rendelés szerkesztése

            $('#adminTable').on('click', '.edit', async function () {

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const order_data = await getOrderData(row_data.id);
                //const order_items = await loadOrderItems(row_data.id);
                const order_history = await loadOrderHistory(row_data.id);
                console.log(order_history);
                $('#order_id').val(row_data.id);
                $('#order_id_display').text(row_data.id);
                $('#order_date_display').text(row_data.created_at);
                $('#customer_name_display').text(row_data.customer_name);
                $('#total_amount_display').text(row_data.total_amount);

                $('#contact_last_name').val(order_data.contact_last_name);
                $('#contact_first_name').val(order_data.contact_first_name);

                renderHistory(order_history);
                /*$('#download_id').val(row_data.id);
                $('#file_name').val(row_data.file_name);
                $('#file_description').val(row_data.file_description);

                const statusCheckbox = $('#download_status');
                const statusLabel = $('label[for="download_status"]');

                if (row_data.status === 'active') {
                    statusCheckbox.prop('checked', true);
                    statusLabel.text('Állapot (Aktív)');
                } else {
                    statusCheckbox.prop('checked', false);
                    statusLabel.text('Állapot (Inaktív)');
                }

                $('#exist_file_area').removeClass('d-none');
                $('#exist_file_url').attr("href", row_data.file_path);*/
                adminModal.show();
            });

            async function getOrderData(order_id) {
                try {
                    const response = await fetch(`{{ url('/admin/ertekesites/rendeles') }}/${order_id}`);
                    if (!response.ok) {
                        throw new Error('Hiba a metaadatok lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Metaadat hiba:', error);
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

        });

    </script>
@endsection
