@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyviteli folyamatok / Érdeklődők</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-leads'))

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
                        <input type="text" placeholder="Email" class="filter-input form-control" data-column="2">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Város" class="filter-input form-control" data-column="4">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Form" class="filter-input form-control" data-column="5">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Kampány" class="filter-input form-control" data-column="6">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="7">
                            <option value="">Állapot (összes)</option>
                            <option value="Új">Új</option>
                            <option value="Nem vette fel">Nem vette fel</option>
                            <option value="Csak érdeklődött">Csak érdeklődött</option>
                            <option value="Felmérés">Felmérés</option>
                            <option value="Átgondolja">Átgondolja</option>
                            <option value="Nem érdekli">Nem érdekli</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Név</th>
                        <th data-priority="4">Email</th>
                        <th data-priority="5">Telefon</th>
                        <th data-priority="6">Város</th>
                        <th>Form</th>
                        <th>Kampány</th>
                        <th>Állapot</th>
                        <th data-priority="3">Létrehozva</th>
                        <th>Látta</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod az érdeklődők megtekintéséhez.
                </div>
            @endif
        </div>
    </div>


    <!-- Modális ablak -->
    <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="adminModalForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="lead_id" name="id">
                        <div class="mb-3">
                            <label for="leader_name" class="form-label"><strong>Név</strong></label>
                            <p id="leader_name"></p>
                        </div>
                        <div class="mb-3">
                            <label for="leader_email" class="form-label"><strong>Email</strong></label>
                            <p id="leader_email"></p>
                        </div>
                        <div class="mb-3">
                            <label for="leader_phone" class="form-label"><strong>Telefon</strong></label> <span class="ml-3 btn btn-primary btn-sm" id="call_phone_number"><i class="fa fa-phone"></i> Hívás</span>
                            <p id="leader_phone"></p>
                        </div>
                        <div class="mb-3">
                            <label for="leader_city" class="form-label"><strong>Város</strong></label>
                            <p id="leader_city"></p>
                        </div>
                        <div class="mb-3">
                            <label for="leader_form" class="form-label"><strong>Form neve</strong></label>
                            <p id="leader_form"></p>
                        </div>
                        <div class="mb-3">
                            <label for="leader_campaign" class="form-label"><strong>Kampány</strong></label>
                            <p id="leader_campaign"></p>
                        </div>
                        <div class="mb-3">
                            <label for="leader_campaign" class="form-label"><strong>Státusz</strong></label>
                            <select id="lead_status" name="lead_status" class="form-select">
                                <option value="Új">Új</option>
                                <option value="Nem vette fel">Nem vette fel</option>
                                <option value="Csak érdeklődött">Csak érdeklődött</option>
                                <option value="Felmérés">Felmérés</option>
                                <option value="Átgondolja">Átgondolja</option>
                                <option value="Nem érdekli">Nem érdekli</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="lead_comment" class="form-label"><strong>Megjegyzés</strong></label>
                            <textarea id="lead_comment" name="lead_comment" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="" class="form-label"><strong>Egyéb adatok</strong></label>
                            <p id="leader_data"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary save-btn" id="saveLead">Mentés</button>
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
                ajax: '{{ route('admin.leads.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'full_name' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'city' },
                    { data: 'form_name' },
                    { data: 'campaign_name' },
                    { data: 'status' },
                    { data: 'created_at' },
                    { data: 'viewed_by' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            // Szűrők beállítása

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            // Érdeklődő szerkesztése

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Érdeklődő szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                $('#lead_id').val(row_data.id);

                const lead_data = await loadLead(row_data.id);

                $('#leader_name').text(lead_data.full_name || 'Nem adott meg nevet');
                $('#leader_email').text(lead_data.email);
                $('#leader_phone').text(lead_data.phone || 'Nem adott meg telefon');
                $('#leader_city').text(lead_data.city || 'Nem adott meg várost');
                $('#leader_form').text(lead_data.form_name || 'Nincs form név');
                $('#leader_campaign').val(lead_data.campaign_name || 'Nem kampány része');

                $('#lead_status').val(lead_data.status);
                $('#lead_comment').val(lead_data.comment || '');

                let lead;
                try {
                    lead = JSON.parse(lead_data.data);
                } catch (e) {
                    console.error("Hibás JSON:", e);
                    lead = { field_data: [] }; // fallback
                }

                if (lead.field_data) {
                    let html = `<ul>`;
                    lead.field_data.forEach(field => {
                        html += `<li><strong>${field.name}:</strong> ${field.values.join(', ')}</li>`;
                    });
                    html += `</ul>`;

                    $('#leader_data').html(html);
                }

                sendViewRequest("lead", row_data.id);

                table.ajax.reload(null, false);

                adminModal.show();
            });

            async function loadLead(id) {
                try {
                    const response = await fetch(`{{ url('/admin/leads') }}/${id}`, {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    if (!response.ok) {
                        throw new Error('Hiba az érdeklődő lekérdezésekor');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Lekérdezési hiba:', error);
                    return [];
                }
            }

            // Érdeklődő mentése

            $('#saveLead').on('click', function (e) {
                e.preventDefault();
                const form = document.getElementById('adminModalForm');
                const formData = new FormData(form);
                formData.append('_token', csrfToken);

                const originalSaveButtonHtml = $(this).html();
                $(this).html('Mentés...').prop('disabled', true);

                const leadId = $('#lead_id').val();

                let method = 'POST';  // Alapértelmezett metódus

                let url = `${window.appConfig.APP_URL}admin/leads/${leadId}`;

                $.ajax({
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
                        $(this).html(originalSaveButtonHtml).prop('disabled', false);
                    }
                });

            });

            // Érdeklődő törlése
            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const leadId = row_data.id;

                if (!confirm('Biztosan törölni szeretnéd az érdeklődőt?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/leads') }}/${leadId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Érdeklődő sikeresen törölve!', 'success');
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt az érdeklődő törlésekor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt az érdeklődő törlésekor', 'danger');
                }
            });

            $('#call_phone_number').on('click', function (e) {
                e.preventDefault(); // ne csináljon mást, pl. ha gomb/link
                let phone_number = $('#leader_phone').val().replace(/\s+/g, ''); // szóközök eltávolítása

                if (phone_number) {
                    window.location.href = 'tel:' + phone_number;
                } else {
                    alert('Nincs megadva telefonszám.');
                }
            });

            $('#adminTable').on('click', '.reset-viewed', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const leadId = row_data.id;

                if (!confirm('Biztosan visszavonodni szeretnéd az érdeklődő látását?')) return;

                try {
                    $.ajax({
                        url: `{{ url('/admin/leads') }}/${leadId}/reset-viewed`,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            showToast('Érdeklődő megtekintése sikeresen visszavonva!', 'success');
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            let msg = 'Hiba történt az érdeklődő visszavonásakor';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            showToast(msg, 'danger');
                        }
                    });
                } catch (error) {
                    showToast(error.message || 'Hiba történt az érdeklődő megtekintésének visszavonásakor', 'danger');
                }

            });

            function resetForm(title = null) {
                $('#adminModalForm')[0].reset();
                $('#lead_id').val('');
                $('#adminModalLabel').text(title);
            }
        });
    </script>
@endsection
