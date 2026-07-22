@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyvitel / Bizonylatok / Szállítólevelek</h2>
            @if(auth('admin')->user()->can('create-delivery-note'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új szállítólevél</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-delivery-notes'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Bizonylatszám" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Partner" class="filter-input form-control" data-column="2">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="5">
                            <option value="">Állapot (összes)</option>
                            <option value="draft">draft</option>
                            <option value="issued">issued</option>
                            <option value="delivered">delivered</option>
                            <option value="cancelled">cancelled</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Bizonylatszám</th>
                        <th>Partner</th>
                        <th>Kelt</th>
                        <th>Átadás</th>
                        <th>Állapot</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Nincs jogosultságod a szállítólevelek megtekintéséhez.
                </div>
            @endif
        </div>
    </div>


    <x-admin.document-modal id="deliveryNoteModal" title="Szállítólevél" form-id="deliveryNoteForm" save-button-id="saveDeliveryNote">
        <x-slot:left>
            <input type="hidden" id="delivery_note_id" name="id">

            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Kiállító adatai</legend>

                <div class="mb-2">
                    <label for="company_id" class="form-label">Cég*</label>
                    <select class="form-select" id="company_id" name="company_id" required>
                        @foreach(($companies ?? []) as $c)
                            <option value="{{ $c->id }}" @selected(isset($defaultCompanyId) && (int) $c->id === (int) $defaultCompanyId)>
                                {{ $c->name }}@if($c->is_default) (alapértelmezett)@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label for="company_site_id" class="form-label">Telephely*</label>
                    <select class="form-select" id="company_site_id" name="company_site_id" required>
                        @foreach(($companySites ?? []) as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="p-3 bg-light border rounded mb-0" style="line-height: 1.25;">
                    <div class="fw-semibold" id="company_display_name">-</div>
                    <div class="small" id="company_display_address">-</div>
                    <div class="small text-muted" id="company_display_tax">-</div>
                    <div class="small text-muted" id="company_display_contact">-</div>
                </div>
            </fieldset>

            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Partner</legend>

                <div class="row g-2">
                    <div class="col-12">
                        <label for="partner_name" class="form-label">Név*</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="partner_name" name="partner_name" required autocomplete="off">
                            <div id="partner_client_search_results" class="list-group w-100 admin-client-search-results" style="z-index: 1100; display:none; max-height: 260px; overflow-y: auto;"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label for="partner_tax_number" class="form-label">Adószám</label>
                        <input type="text" class="form-control" id="partner_tax_number" name="partner_tax_number">
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12">
                        <label for="partner_country" class="form-label">Ország</label>
                        <select class="form-select" id="partner_country" name="partner_country">
                            @foreach(config('countries') as $code => $name)
                                <option value="{{ $code }}" {{ $code === 'HU' ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12 col-md-3">
                        <label for="partner_zip_code" class="form-label">Irányítószám</label>
                        <input type="text" class="form-control" id="partner_zip_code" name="partner_zip_code">
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="partner_city" class="form-label">Város</label>
                        <input type="text" class="form-control" id="partner_city" name="partner_city">
                    </div>
                    <div class="col-12 col-md-5">
                        <label for="partner_address_line" class="form-label">Cím</label>
                        <input type="text" class="form-control" id="partner_address_line" name="partner_address_line">
                    </div>
                </div>
            </fieldset>

            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Szállítási cím (opcionális)</legend>

                <div class="row g-2">
                    <div class="col-12">
                        <label for="shipping_country" class="form-label">Ország</label>
                        <select class="form-select" id="shipping_country" name="shipping_country">
                            @foreach(config('countries') as $code => $name)
                                <option value="{{ $code }}" {{ $code === 'HU' ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12 col-md-3">
                        <label for="shipping_zip_code" class="form-label">Irányítószám</label>
                        <input type="text" class="form-control" id="shipping_zip_code" name="shipping_zip_code">
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="shipping_city" class="form-label">Város</label>
                        <input type="text" class="form-control" id="shipping_city" name="shipping_city">
                    </div>
                    <div class="col-12 col-md-5">
                        <label for="shipping_address_line" class="form-label">Cím</label>
                        <input type="text" class="form-control" id="shipping_address_line" name="shipping_address_line">
                    </div>
                </div>
            </fieldset>

            <fieldset class="admin-fieldset mb-0">
                <legend class="admin-fieldset__legend">Dátumok</legend>

                <div class="row g-2">
                    <div class="col-12 col-md-6">
                        <label for="issued_at" class="form-label">Kelt</label>
                        <input type="date" class="form-control" id="issued_at" name="issued_at">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="delivered_at" class="form-label">Átadás / kiszállítás</label>
                        <input type="date" class="form-control" id="delivered_at" name="delivered_at">
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12">
                        <label for="status" class="form-label">Állapot</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft">draft</option>
                            <option value="issued">issued</option>
                            <option value="delivered">delivered</option>
                            <option value="cancelled">cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12">
                        <label for="note" class="form-label">Belső megjegyzés</label>
                        <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12">
                        <label for="note_before_items" class="form-label">Megjegyzés tételek fölé</label>
                        <textarea class="form-control" id="note_before_items" name="note_before_items" rows="3"></textarea>
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12">
                        <label for="note_after_items" class="form-label">Megjegyzés tételek alá</label>
                        <textarea class="form-control" id="note_after_items" name="note_after_items" rows="3"></textarea>
                    </div>
                </div>
            </fieldset>
        </x-slot:left>

        <x-slot:middle>
            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Tételek</legend>

                <div class="mb-2">
                    <label for="product_search" class="form-label">Termék keresés</label>
                    <input type="text" class="form-control form-control-sm" id="product_search" placeholder="Kezdj el gépelni..." style="font-size: 0.85rem;">
                </div>

                <div class="list-group mb-3" id="product_search_results" style="max-height: 240px; overflow:auto; font-size: 0.85rem;"></div>

                <div class="table-responsive" style="overflow-x:auto;">
                    <table class="table table-sm table-bordered align-middle" id="delivery_note_items_table" style="font-size: 0.85rem; min-width: 900px;">
                        <thead>
                        <tr>
                            <th>Megnevezés</th>
                            <th class="text-end">Mennyiség</th>
                            <th class="text-center">Mee.</th>
                            <th class="text-center">SKU</th>
                            <th>Megjegyzés</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <input type="hidden" name="items_json" id="items_json" value="[]">
            </fieldset>
        </x-slot:middle>

        <x-slot:footer>
            <button type="button" class="btn btn-outline-primary" id="previewDeliveryNote">Előnézet</button>
        </x-slot:footer>
    </x-admin.document-modal>

    <div class="modal fade" id="deliveryNotePreviewModal" tabindex="-1" aria-labelledby="deliveryNotePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1100px;">
            <div class="modal-content" style="height: 80vh;">
                <div class="modal-header">
                    <h5 class="modal-title" id="deliveryNotePreviewModalLabel">Szállítólevél előnézet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <div class="modal-body p-0" style="height: 100%;">
                    <iframe id="delivery_note_preview_iframe" title="PDF előnézet" style="width: 100%; height: 100%; border: 0; display:block;"></iframe>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script type="module">

        const companies = @json($companies ?? []);
        const companySites = @json($companySites ?? []);
        const defaultCompanyId = @json($defaultCompanyId ?? null);

        const modalDOM = document.getElementById('deliveryNoteModal');
        const modal = new bootstrap.Modal(modalDOM);
        const previewModalDOM = document.getElementById('deliveryNotePreviewModal');
        const previewModal = previewModalDOM ? new bootstrap.Modal(previewModalDOM) : null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        $(document).ready(function() {

            function escapeHtml(value) {
                if (value === null || value === undefined) return '';
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderCompanyBlock(companyId) {
                const id = companyId ? parseInt(companyId, 10) : null;
                const c = companies.find(x => parseInt(x.id, 10) === id);

                if (!c) {
                    $('#company_display_name').text('-');
                    $('#company_display_tax').text('-');
                    $('#company_display_address').text('-');
                    $('#company_display_contact').text('-');
                    return;
                }

                const tax = c.tax_number ? `Adószám: ${escapeHtml(c.tax_number)}` : '';
                const addressParts = [c.country, c.zip_code, c.city, c.address_line].filter(Boolean).map(escapeHtml);
                const address = addressParts.length ? addressParts.join(' ') : '';
                const contactParts = [];
                if (c.email) contactParts.push(escapeHtml(c.email));
                if (c.phone) contactParts.push(escapeHtml(c.phone));
                const contact = contactParts.join(' | ');

                $('#company_display_name').text(c.name || '-');
                $('#company_display_tax').text(tax || '-');
                $('#company_display_address').text(address || '-');
                $('#company_display_contact').text(contact || '-');
            }

            function todayDate() {
                const d = new Date();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${d.getFullYear()}-${m}-${day}`;
            }

            function resetPreview() {
                $('#delivery_note_preview_iframe').attr('src', 'about:blank');
            }

            async function loadDeliveryNotePdfPreview() {
                const previewBtn = document.getElementById('previewDeliveryNote');
                if (!previewBtn) return;
                const originalText = previewBtn ? previewBtn.innerHTML : null;
                const saveBtn = document.getElementById('saveDeliveryNote');
                const saveBtnWasDisabled = saveBtn ? saveBtn.disabled : false;
                if (previewBtn) {
                    previewBtn.disabled = true;
                    previewBtn.innerHTML = 'Betöltés...';
                }
                if (saveBtn) {
                    saveBtn.disabled = true;
                }

                try {
                    syncItemsJson();
                    const form = document.getElementById('deliveryNoteForm');
                    const formData = new FormData(form);

                    const resp = await fetch('{{ route('admin.documents.delivery-notes.preview-pdf') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    if (!resp.ok) {
                        let msg = 'Hiba történt a PDF előnézet generálásakor.';
                        try {
                            const json = await resp.json();
                            if (json?.message) msg = json.message;
                        } catch (e) {}
                        throw new Error(msg);
                    }

                    const blob = await resp.blob();
                    const blobUrl = URL.createObjectURL(blob);
                    $('#delivery_note_preview_iframe').attr('src', blobUrl);
                    if (previewModal) previewModal.show();
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                } finally {
                    if (previewBtn) {
                        previewBtn.disabled = false;
                        if (originalText !== null) previewBtn.innerHTML = originalText;
                    }
                    if (saveBtn) {
                        saveBtn.disabled = saveBtnWasDisabled;
                    }
                }
            }

            let partnerClientSearchDebounce = null;

            function clearPartnerClientResults() {
                $('#partner_client_search_results').hide().empty();
            }

            function fillPartnerFromClient({ name = '', tax = '', country = 'HU', zip = '', city = '', line = '' } = {}) {
                $('#partner_name').val(name || '');
                $('#partner_tax_number').val(tax || '');
                $('#partner_country').val(country || 'HU');
                $('#partner_zip_code').val(zip || '');
                $('#partner_city').val(city || '');
                $('#partner_address_line').val(line || '');

                $('#shipping_country').val(country || 'HU');
                $('#shipping_zip_code').val(zip || '');
                $('#shipping_city').val(city || '');
                $('#shipping_address_line').val(line || '');
            }

            $('#partner_name').on('input', function () {
                const q = ($(this).val() || '').trim();
                clearTimeout(partnerClientSearchDebounce);
                clearPartnerClientResults();

                if (q.length < 2) {
                    return;
                }

                partnerClientSearchDebounce = setTimeout(() => {
                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/bizonylatok/partner-kereses?q=${encodeURIComponent(q)}`,
                        method: 'GET',
                        success: function (response) {
                            const clients = response?.partners || [];
                            const $list = $('#partner_client_search_results');
                            $list.empty();

                            if (clients.length) {
                                clients.forEach(c => {
                                    const name = c?.name || '';
                                    const email = c?.email || '';
                                    const tax = c?.id_number || '';
                                    const addresses = Array.isArray(c?.addresses) ? c.addresses : [];

                                    const headerParts = [email].filter(Boolean).join(', ');
                                    $list.append(`
                                        <div class="list-group-item client-search-header">
                                            <div class="fw-bold">${escapeHtml(name || email || 'N/A')}${headerParts ? ' (' + escapeHtml(headerParts) + ')' : ''}</div>
                                        </div>
                                    `);

                                    addresses.forEach(a => {
                                        const addrText = `${a?.zip_code || ''} ${a?.city || ''}, ${a?.address_line || ''}`.trim();
                                        $list.append(`
                                            <button type="button" class="list-group-item list-group-item-action client-address-item"
                                                data-name="${escapeHtml(name)}"
                                                data-tax="${escapeHtml(tax)}"
                                                data-country="${escapeHtml(a?.country || 'HU')}"
                                                data-zip="${escapeHtml(a?.zip_code || '')}"
                                                data-city="${escapeHtml(a?.city || '')}"
                                                data-line="${escapeHtml(a?.address_line || '')}">
                                                <div class="fw-bold">${escapeHtml(addrText || 'Cím nélkül')}${a?.is_default ? ' (alapértelmezett)' : ''}</div>
                                            </button>
                                        `);
                                    });
                                });
                            } else {
                                $list.append(`
                                    <div class="list-group-item">
                                        <div class="small text-muted">Nincs találat.</div>
                                    </div>
                                `);
                            }

                            $list.show();
                        },
                        error: function () {
                            const $list = $('#partner_client_search_results');
                            $list.empty();
                            $list.append(`
                                <div class="list-group-item">
                                    <div class="small text-muted">A keresés sikertelen volt.</div>
                                </div>
                            `);
                            $list.show();
                        }
                    });
                }, 300);
            });

            $('#partner_client_search_results').on('click', '.client-address-item', function () {
                const $btn = $(this);
                fillPartnerFromClient({
                    name: $btn.data('name') || '',
                    tax: $btn.data('tax') || '',
                    country: $btn.data('country') || 'HU',
                    zip: $btn.data('zip') || '',
                    city: $btn.data('city') || '',
                    line: $btn.data('line') || '',
                });
                clearPartnerClientResults();
            });

            const table = $('#adminTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.documents.delivery-notes.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'document_number' },
                    { data: 'partner_name' },
                    { data: 'issued_at' },
                    { data: 'delivered_at' },
                    { data: 'status' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            function resetForm(title) {
                $('#delivery_note_id').val('');
                $('#status').val('draft');
                $('#issued_at').val(todayDate());
                $('#delivered_at').val('');
                $('#partner_name').val('');
                $('#partner_tax_number').val('');
                $('#partner_country').val('HU');
                $('#partner_zip_code').val('');
                $('#partner_city').val('');
                $('#partner_address_line').val('');
                $('#shipping_country').val('HU');
                $('#shipping_zip_code').val('');
                $('#shipping_city').val('');
                $('#shipping_address_line').val('');
                $('#note').val('');
                $('#note_before_items').val('');
                $('#note_after_items').val('');

                const companyId = defaultCompanyId || $('#company_id').val();
                if (companyId) {
                    $('#company_id').val(companyId);
                }

                if (companySites.length) {
                    $('#company_site_id').val(companySites[0].id);
                }

                renderCompanyBlock($('#company_id').val());

                items.splice(0, items.length);
                renderItems();
                syncItemsJson();
                $('#product_search').val('');
                $('#product_search_results').empty();
                resetPreview();
                clearPartnerClientResults();

                const label = document.getElementById('deliveryNoteModalLabel');
                if (label && title) {
                    label.textContent = title;
                }
            }

            $('#addButton').on('click', async function () {
                resetForm('Új szállítólevél');
                modal.show();
            });

            $('#previewDeliveryNote').on('click', function () {
                loadDeliveryNotePdfPreview();
            });

            $('#company_id').on('change', function () {
                renderCompanyBlock($(this).val());
            });

            $('#adminTable').on('click', '.edit', async function () {
                resetForm('Szállítólevél szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const id = row_data.id;

                const resp = await fetch(`{{ url('/admin/bizonylatok/szallitolevelek') }}/${id}`);
                const json = await resp.json();

                const note = json?.delivery_note;
                const noteItems = json?.items || [];

                $('#delivery_note_id').val(note.id);
                $('#company_id').val(note.company_id || defaultCompanyId);
                $('#company_site_id').val($('#company_site_id').val() || (companySites[0]?.id ?? ''));
                renderCompanyBlock($('#company_id').val());

                $('#partner_name').val(note.partner_name || '');
                $('#partner_tax_number').val(note.partner_tax_number || '');
                $('#partner_country').val(note.partner_country || 'HU');
                $('#partner_zip_code').val(note.partner_zip_code || '');
                $('#partner_city').val(note.partner_city || '');
                $('#partner_address_line').val(note.partner_address_line || '');

                $('#shipping_country').val(note.shipping_country || 'HU');
                $('#shipping_zip_code').val(note.shipping_zip_code || '');
                $('#shipping_city').val(note.shipping_city || '');
                $('#shipping_address_line').val(note.shipping_address_line || '');

                $('#issued_at').val(note.issued_at || todayDate());
                $('#delivered_at').val(note.delivered_at || '');
                $('#status').val(note.status || 'draft');

                $('#note').val(note.note || '');
                $('#note_before_items').val(note.note_before_items || '');
                $('#note_after_items').val(note.note_after_items || '');

                const pdfPath = row_data.pdf_path;
                if (pdfPath) {
                    const src = `{{ route('admin.documents.delivery-notes.pdf', ['id' => '__ID__']) }}`.replace('__ID__', String(note.id));
                    $('#delivery_note_preview_iframe').attr('src', src);
                } else {
                    resetPreview();
                }

                items.splice(0, items.length);
                noteItems.forEach(it => {
                    items.push({
                        product_id: it.product_id,
                        name: it.name,
                        quantity: it.quantity,
                        unit: it.unit || 'db',
                        sku: it.sku || '',
                        note: it.note || '',
                    });
                });
                renderItems();
                syncItemsJson();

                modal.show();
            });

            $('#adminTable').on('click', '.delete', async function () {
                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                const id = row_data?.id;
                if (!id) {
                    showToast('Hiányzó azonosító.', 'danger');
                    return;
                }

                const ok = confirm('Biztosan törlöd a szállítólevelet?');
                if (!ok) {
                    return;
                }

                try {
                    const url = `{{ route('admin.documents.delivery-notes.destroy', ['id' => '__ID__']) }}`.replace('__ID__', String(id));
                    const resp = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!resp.ok) {
                        let msg = 'Hiba történt a törlés során.';
                        try {
                            const json = await resp.json();
                            if (json?.message) msg = json.message;
                        } catch (e) {}
                        throw new Error(msg);
                    }

                    showToast('Sikeres törlés!', 'success');
                    table.ajax.reload(null, false);
                } catch (e) {
                    showToast(e?.message || 'Hiba!', 'danger');
                }
            });

            modalDOM.addEventListener('hidden.bs.modal', function () {
                $('#product_search_results').empty();
                resetPreview();
                clearPartnerClientResults();
            });

            $('#deliveryNoteForm').on('submit', function (e) {
                e.preventDefault();

                syncItemsJson();

                const formData = new FormData(this);
                formData.append('_token', csrfToken);

                const saveBtn = $('#saveDeliveryNote');
                const originalSaveButtonHtml = saveBtn.html();
                saveBtn.html('Mentés...').prop('disabled', true);

                const previewBtn = $('#previewDeliveryNote');
                const originalPreviewButtonHtml = previewBtn.length ? previewBtn.html() : null;
                if (previewBtn.length) previewBtn.prop('disabled', true);

                const id = $('#delivery_note_id').val();

                let url = '{{ route('admin.documents.delivery-notes.store') }}';
                let method = 'POST';

                if (id) {
                    url = `${window.appConfig.APP_URL}admin/bizonylatok/szallitolevelek/${id}`;
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success(response) {
                        const savedId = response?.delivery_note?.id;
                        if (!savedId) {
                            showToast('Sikeres mentés, de hiányzik a bizonylat azonosítója.', 'warning');
                            table.ajax.reload(null, false);
                            saveBtn.html(originalSaveButtonHtml).prop('disabled', false);
                            if (previewBtn.length) previewBtn.html(originalPreviewButtonHtml).prop('disabled', false);
                            return;
                        }

                        const src = `{{ route('admin.documents.delivery-notes.pdf', ['id' => '__ID__']) }}`.replace('__ID__', String(savedId));
                        $('#delivery_note_preview_iframe').attr('src', src);

                        showToast('Sikeres mentés!', 'success');
                        table.ajax.reload(null, false);

                        saveBtn.html(originalSaveButtonHtml).prop('disabled', false);
                        if (previewBtn.length) previewBtn.html(originalPreviewButtonHtml).prop('disabled', false);
                    },
                    error(xhr) {
                        let msg = 'Hiba!';
                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, 'danger');

                        saveBtn.html(originalSaveButtonHtml).prop('disabled', false);
                        if (previewBtn.length) previewBtn.html(originalPreviewButtonHtml).prop('disabled', false);
                    },
                    complete: () => {}
                });
            });

            const items = [];

            function renderItems() {
                const tbody = $('#delivery_note_items_table tbody');
                tbody.empty();

                items.forEach((item, idx) => {
                    const row = $(
                        `<tr data-idx="${idx}">
                            <td>
                                <div class="fw-semibold">${escapeHtml(item.name)}</div>
                            </td>
                            <td style="width: 130px;">
                                <input type="number" min="0.001" step="0.001" class="form-control form-control-sm text-end item-qty" value="${escapeHtml(item.quantity)}">
                            </td>
                            <td class="text-center" style="width: 70px;">
                                <input type="text" class="form-control form-control-sm text-center item-unit" value="${escapeHtml(item.unit || 'db')}">
                            </td>
                            <td class="text-center" style="width: 110px;">
                                <input type="text" class="form-control form-control-sm text-center item-sku" value="${escapeHtml(item.sku || '')}">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm item-note" value="${escapeHtml(item.note || '')}">
                            </td>
                            <td class="text-center" style="width: 44px;">
                                <button type="button" class="btn btn-sm btn-outline-danger item-remove" title="Tétel törlése">&times;</button>
                            </td>
                        </tr>`
                    );

                    tbody.append(row);
                });
            }

            function syncItemsJson() {
                $('#items_json').val(JSON.stringify(items));
            }

            function addProductAsItem(product) {
                const unitAbbrev = product.unit_abbreviation ?? '';

                const item = {
                    product_id: product.id,
                    name: product.title,
                    quantity: 1,
                    unit: unitAbbrev || 'db',
                    sku: '',
                    note: '',
                };

                items.push(item);
                renderItems();
                syncItemsJson();
            }

            let searchTimeout = null;
            $('#product_search').on('input', function () {
                const q = $(this).val().trim();
                clearTimeout(searchTimeout);
                if (q.length < 2) {
                    $('#product_search_results').empty();
                    return;
                }

                const siteId = String($('#company_site_id').val() || '').trim();
                if (!siteId) {
                    $('#product_search_results').empty();
                    return;
                }

                searchTimeout = setTimeout(() => {
                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/termekek/search?q=${encodeURIComponent(q)}&company_site_id=${encodeURIComponent(siteId)}`,
                        method: 'GET',
                        success: function (resp) {
                            const results = resp?.products ?? [];
                            const container = $('#product_search_results');
                            container.empty();
                            results.forEach(p => {
                                const unitText = (p.unit_abbreviation || p.unit_name) ? `${escapeHtml(p.unit_abbreviation || p.unit_name)}` : '';
                                const qty = Number(p.available_quantity ?? 0);
                                const qtyText = Number.isFinite(qty) ? String(qty) : '0';
                                const isOut = !Number.isFinite(qty) || qty <= 0;
                                const btn = $(
                                    `<button type="button" class="list-group-item list-group-item-action" ${isOut ? 'disabled aria-disabled="true"' : ''}>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div><strong>${escapeHtml(p.title)}</strong></div>
                                            <div class="text-muted">${unitText}</div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <div class="small text-muted">Készlet: ${escapeHtml(qtyText)}</div>
                                            ${isOut ? '<span class="badge text-bg-secondary">nincs készleten</span>' : ''}
                                        </div>
                                     </button>`
                                );
                                if (!isOut) {
                                    btn.on('click', function () {
                                        addProductAsItem(p);
                                        $('#product_search').val('');
                                        $('#product_search_results').empty();
                                        $('#product_search').trigger('focus');
                                    });
                                }
                                container.append(btn);
                            });
                        },
                        error: function () {
                            $('#product_search_results').empty();
                        }
                    });
                }, 250);
            });

            $('#delivery_note_items_table').on('input', '.item-qty, .item-unit, .item-sku, .item-note', function () {
                const tr = $(this).closest('tr');
                const idx = Number(tr.data('idx'));
                const item = items[idx];
                if (!item) return;

                item.quantity = Number(tr.find('.item-qty').val()) || 0;
                item.unit = String(tr.find('.item-unit').val() || '').trim();
                item.sku = String(tr.find('.item-sku').val() || '').trim();
                item.note = String(tr.find('.item-note').val() || '').trim();

                syncItemsJson();
            });

            $('#delivery_note_items_table').on('click', '.item-remove', function () {
                const tr = $(this).closest('tr');
                const idx = Number(tr.data('idx'));
                if (!Number.isFinite(idx)) return;
                items.splice(idx, 1);
                renderItems();
                syncItemsJson();
            });

            resetPreview();
        });

    </script>
@endsection
