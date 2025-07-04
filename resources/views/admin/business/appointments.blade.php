@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Ügyfél folyamatok / Időpontfoglalások</h1>
            <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új időpontfoglalás</button>
        </div>

        <table class="table table-bordered" id="appointmentsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ügyfélnév</th>
                    <th style="display:none">E-mail</th>
                    <th style="display:none">Telefonszám</th>
                    <th style="display:none">Irányítószám</th>
                    <th>Város</th>
                    <th>Cím</th>
                    <th>Dátum</th>
                    <th>Típus</th>
                    <th style="display:none">Megjegyzés</th>
                    <th>Látta</th>
                    <th>Állapot</th>
                    <th>Létrehozva</th>
                    <th>Műveletek</th>
                </tr>
                </thead>
        </table>
    </div>


    <!-- Modális ablak -->
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="appointmentForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="appointmentModalLabel">Időpontfoglalás szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="appointment_id" name="id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Ügyfélnév*</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">E-mail cím</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Telefonszám*</label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="zip_code" class="form-label">Irányítószám*</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">Város*</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address_line" class="form-label">Cím*</label>
                                <input type="text" class="form-control" id="address_line" name="address_line" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="appointment_date" class="form-label">Dátum*</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="appointment_type" class="form-label">Típus*</label>
                                <select id="appointment_type" name="appointment_type" class="form-control">
                                    <option value="Karbantartás">Karbantartás</option>
                                    <option value="Felmérés">Felmérés</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Állapot*</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="Függőben">Függőben</option>
                                    <option value="Folyamatban">Folyamatban</option>
                                    <option value="Kész">Kész</option>
                                    <option value="Törölve">Törölve</option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="message" class="form-label">Üzenet</label>
                                <textarea name="message" id="message" rows="3" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-btn">Mentés</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">

        document.addEventListener('DOMContentLoaded', () => {
            initCrud({
                tableId: 'appointmentsTable',
                modalId: 'appointmentModal',
                formId: 'appointmentForm',
                addButtonId: 'addButton',
                dataUrl: '{{ route('admin.appointments.data') }}',
                storeUrl: '{{ route('admin.appointments.store') }}',
                destroyUrl: '{{ url('/admin/idopontfoglalasok/') }}',
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'email', visible: false },
                    { data: 'phone', visible: false },
                    { data: 'zip_code', visible: false },
                    { data: 'city' },
                    { data: 'address_line' },
                    { data: 'appointment_date' },
                    { data: 'appointment_type' },
                    { data: 'message', visible: false },
                    { data: 'viewed_by' },
                    { data: 'status' },
                    { data: 'created_at' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                model: 'appointments',
                fillFormFn: (row) => {
                    document.getElementById('tax_id').value = row.id ?? '';
                    document.getElementById('name').value = row.name ?? '';
                    document.getElementById('email').value = row.email ?? '';
                    document.getElementById('phone').value = row.phone ?? '';
                    document.getElementById('zip_code').value = row.zip_code ?? '';
                    document.getElementById('city').value = row.city ?? '';
                    document.getElementById('address_line').value = row.address_line ?? '';
                    document.getElementById('appointment_date').value = row.appointment_date ?? '';
                    document.getElementById('appointment_type').value = row.appointment_type ?? '';
                    document.getElementById('status').value = row.status ?? '';
                    document.getElementById('message').value = row.message ?? '';

                }
            });
        });

        /*$(document).ready(function() {
            $('#appointmentsTable').on('click', '.edit', async function () {
                const row_data = $('#appointmentsTable').DataTable().row($(this).parents('tr')).data();
                console.log(row_data.id, row_data.viewed_by);
            });
        });*/
    </script>
@endsection
