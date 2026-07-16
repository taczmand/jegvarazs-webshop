@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyvitel / Bizonylatok / Kimenő számlák</h2>
            @if(auth('admin')->user()->can('create-sales-invoice'))
                <button class="btn btn-success" id="addButton"><i class="fas fa-plus me-1"></i> Új számla</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-sales-invoices'))

                <div class="filters d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div class="filter-group">
                        <i class="fa-solid fa-filter text-gray-500"></i>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="ID" class="filter-input form-control" data-column="0">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Számlaszám" class="filter-input form-control" data-column="1">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Partner" class="filter-input form-control" data-column="2">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="8">
                            <option value="">Állapot (összes)</option>
                            <option value="draft">draft</option>
                            <option value="issued">issued</option>
                            <option value="sent">sent</option>
                            <option value="cancelled">cancelled</option>
                        </select>
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <select class="form-select filter-input" data-column="9">
                            <option value="">Fizetés (összes)</option>
                            <option value="unpaid">unpaid</option>
                            <option value="partially_paid">partially_paid</option>
                            <option value="paid">paid</option>
                            <option value="overdue">overdue</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Számlaszám</th>
                        <th>Partner</th>
                        <th>Kelt</th>
                        <th>Határidő</th>
                        <th>Pénznem</th>
                        <th>Bruttó</th>
                        <th>Létrehozva</th>
                        <th>Állapot</th>
                        <th>Fizetés</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                    Nincs jogosultságod a kimenő számlák megtekintéséhez.
                </div>
            @endif
        </div>
    </div>


    <x-admin.document-modal id="salesInvoiceModal" title="Kimenő számla" form-id="salesInvoiceForm" save-button-id="saveSalesInvoice">
        <x-slot:left>
            <input type="hidden" id="invoice_id" name="id">

            <fieldset class="admin-fieldset mb-3">
                <legend class="admin-fieldset__legend">Számla kiállító adatai</legend>
                <div class="mb-3">
                    <select class="form-select" id="company_id" name="company_id" required>
                        @foreach(($companies ?? []) as $c)
                            <option value="{{ $c->id }}" @selected(isset($defaultCompanyId) && (int) $c->id === (int) $defaultCompanyId)>
                                {{ $c->name }}@if($c->is_default) (alapértelmezett)@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="p-3 bg-light border rounded mb-3" style="line-height: 1.25;">
                    <div class="fw-semibold" id="company_display_name">-</div>
                    <div class="small" id="company_display_address">-</div>
                    <div class="small text-muted" id="company_display_tax">-</div>
                    <div class="small text-muted" id="company_display_contact">-</div>
                    <div class="small text-muted" id="company_display_bank">-</div>
                </div>
            </fieldset>

            <fieldset class="admin-fieldset mt-3">
                <legend class="admin-fieldset__legend">Vevő</legend>

                <div class="row g-2">
                    <div class="col-12 col-md-7">
                        <label for="partner_name" class="form-label">Név*</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="partner_name" name="partner_name" required autocomplete="off">
                            <div id="partner_client_search_results" class="list-group w-100 admin-client-search-results" style="z-index: 1100; display:none; max-height: 260px; overflow-y: auto;"></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-5">
                        <label for="partner_tax_number" class="form-label">Adószám</label>
                        <input type="text" class="form-control" id="partner_tax_number" name="partner_tax_number">
                    </div>
                </div>

                <div class="row g-2 mt-1">
                    <div class="col-12">
                        <label for="partner_country" class="form-label">Ország</label>
                        <select class="form-select" id="partner_country" name="partner_country" required>
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

                <div class="row g-2 mt-1">
                    <div class="col-12 col-md-6">
                        <label for="partner_email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="partner_email" name="partner_email">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="partner_phone" class="form-label">Telefon</label>
                        <input type="text" class="form-control" id="partner_phone" name="partner_phone">
                    </div>
                </div>
            </fieldset>

            <fieldset class="admin-fieldset mb-3 mt-3">
                <legend class="admin-fieldset__legend">Fizetés</legend>

                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <label for="payment_method" class="form-label">Fizetési mód*</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="bank_transfer">Átutalás</option>
                                <option value="cash">Készpénz</option>
                                <option value="cod">Utánvét</option>
                                <option value="credit_card">Bankkártya</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="currency" class="form-label">Pénznem</label>
                            <select class="form-select" id="currency" name="currency" required>
                                <option value="HUF">HUF</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="invoice_type" class="form-label">Számla típusa</label>
                            <select class="form-select" id="invoice_type" name="invoice_type" required>
                                <option>Papír</option>
                                <option>Elektronikus számla</option>
                            </select>
                        </div>
                    </div>


                    <div class="row g-2 mt-1">
                        <div class="col-12 col-md-4">
                            <label for="issued_at" class="form-label">Számla kelte</label>
                            <input type="date" class="form-control" id="issued_at" name="issued_at" readonly style="pointer-events:none; background-color: #e9ecef;">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="fulfilled_at" class="form-label">Teljesítés dátuma</label>
                            <input type="date" class="form-control" id="fulfilled_at" name="fulfilled_at">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="due_at" class="form-label">Fizetési határidő</label>
                            <input type="date" class="form-control" id="due_at" name="due_at">
                        </div>
                    </div>
            </fieldset>

            <fieldset class="admin-fieldset mb-3 mt-3">
                <legend class="admin-fieldset__legend">Megjegyzés</legend>

                <div class="mb-0">
                    <label for="note" class="form-label">Belső megjegyzés</label>
                    <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                </div>

                <div class="mb-3 mt-3">
                    <label for="note_before_items" class="form-label">Megjegyzés tételek fölé</label>
                    <textarea class="form-control" id="note_before_items" name="note_before_items" rows="3"></textarea>
                </div>

                <div class="mb-3 mt-3">
                    <label for="note_after_items" class="form-label">Megjegyzés tételek alá</label>
                    <textarea class="form-control" id="note_after_items" name="note_after_items" rows="3"></textarea>
                </div>

            </fieldset>
        </x-slot:left>

        <x-slot:middle>
            <fieldset class="admin-fieldset mb-3 mb-3">
                <legend class="admin-fieldset__legend">Tételek</legend>

                <div class="mb-2">
                    <label for="product_search" class="form-label">Termék keresés</label>
                    <input type="text" class="form-control form-control-sm" id="product_search" placeholder="Kezdj el gépelni..." style="font-size: 0.85rem;">
                </div>

                <div class="list-group mb-3" id="product_search_results" style="max-height: 240px; overflow:auto; font-size: 0.85rem;"></div>

                <div class="table-responsive" style="overflow-x:auto;">
                    <table class="table table-sm table-bordered align-middle" id="invoice_items_table" style="font-size: 0.85rem; min-width: 1100px;">
                        <thead>
                        <tr>
                            <th>Megnevezés</th>
                            <th class="text-end">Mennyiség</th>
                            <th class="text-center">Mee.</th>
                            <th class="text-end">Kedvezmény</th>
                            <th class="text-end">Egységár</th>
                            <th class="text-end">ÁFA%</th>
                            <th class="text-end">Nettó</th>
                            <th class="text-end">ÁFA</th>
                            <th class="text-end">Bruttó</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <input type="hidden" name="items_json" id="items_json" value="[]">
            </fieldset>
        </x-slot:middle>

        <x-slot:right>
            <div class="d-flex flex-column" style="height: 100%; min-height: 0;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-semibold">Előnézet</div>
                    <div class="small text-muted" id="sales_invoice_preview_zoom_label" style="display:none;">100%</div>
                </div>

                <div id="sales_invoice_preview_placeholder" class="d-flex align-items-center justify-content-center flex-grow-1" style="min-height: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="220" height="220" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" style="color: #cfd4da;">
                        <path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"/>
                        <path d="M14 2v5h5"/>
                        <path d="M8 13h8"/>
                        <path d="M8 17h8"/>
                        <path d="M8 9h4"/>
                    </svg>
                </div>

                <div id="sales_invoice_preview_wrap" class="border rounded bg-white flex-grow-1" style="display:none; overflow:auto; min-height: 0;">
                    <div id="sales_invoice_preview_inner" style="transform-origin: 0 0; height: 100%; display:flex;">
                        <iframe id="sales_invoice_preview_iframe" title="PDF előnézet" style="width: 100%; height: 100%; border: 0; display:block; flex: 1;"></iframe>
                    </div>
                </div>
            </div>
        </x-slot:right>

        <x-slot:footer>
            <button type="button" class="btn btn-outline-primary" id="previewSalesInvoice">Előnézet</button>
        </x-slot:footer>
    </x-admin.document-modal>

@endsection

@section('scripts')
    <script type="module">

        const companies = @json($companies ?? []);
        const defaultCompanyId = @json($defaultCompanyId ?? null);

        const modalDOM = document.getElementById('salesInvoiceModal');
        const modal = new bootstrap.Modal(modalDOM);
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
                    $('#company_display_bank').text('-');
                    return;
                }

                const tax = c.tax_number ? `Adószám: ${escapeHtml(c.tax_number)}` : '';
                const addressParts = [c.country, c.zip_code, c.city, c.address_line].filter(Boolean).map(escapeHtml);
                const address = addressParts.length ? addressParts.join(' ') : '';
                const contactParts = [];
                if (c.email) contactParts.push(escapeHtml(c.email));
                if (c.phone) contactParts.push(escapeHtml(c.phone));
                const contact = contactParts.join(' | ');
                const bank = c.bank_account ? `Bank: ${escapeHtml(c.bank_account)}` : '';

                $('#company_display_name').text(c.name || '-');
                $('#company_display_tax').text(tax || '-');
                $('#company_display_address').text(address || '-');
                $('#company_display_contact').text(contact || '-');
                $('#company_display_bank').text(bank || '-');
            }

            let previewScale = 1;

            function resetPreview() {
                previewScale = 1;
                $('#sales_invoice_preview_iframe').attr('src', 'about:blank');
                $('#sales_invoice_preview_wrap').hide();
                $('#sales_invoice_preview_placeholder').removeClass('d-none');
                $('#sales_invoice_preview_zoom_label').hide().text('100%');
                $('#sales_invoice_preview_inner').css('transform', 'scale(1)');
            }

            function setPreviewScale(scale) {
                const clamped = Math.max(0.5, Math.min(3, Number(scale) || 1));
                previewScale = clamped;
                $('#sales_invoice_preview_inner').css('transform', `scale(${previewScale})`);
                $('#sales_invoice_preview_zoom_label').show().text(`${Math.round(previewScale * 100)}%`);
            }

            function loadTestPdfPreview() {
                const testPdfUrl = 'https://mozilla.github.io/pdf.js/web/compressed.tracemonkey-pldi-09.pdf';
                $('#sales_invoice_preview_placeholder').addClass('d-none');
                $('#sales_invoice_preview_wrap').show();
                $('#sales_invoice_preview_iframe').attr('src', testPdfUrl);
                setPreviewScale(1);
            }

            let partnerClientSearchDebounce = null;

            function clearPartnerClientResults() {
                $('#partner_client_search_results').hide().empty();
            }

            function fillPartnerFromClient({ name = '', email = '', phone = '', country = 'HU', zip = '', city = '', line = '' } = {}) {
                $('#partner_name').val(name || '');
                $('#partner_email').val(email || '');
                $('#partner_phone').val(phone || '');

                $('#partner_country').val(country || 'HU');
                $('#partner_zip_code').val(zip || '');
                $('#partner_city').val(city || '');
                $('#partner_address_line').val(line || '');
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
                        url: `${window.appConfig.APP_URL}admin/ugyfelek/kereses?q=${encodeURIComponent(q)}`,
                        method: 'GET',
                        success: function (response) {
                            const clients = response?.clients || [];
                            const $list = $('#partner_client_search_results');
                            $list.empty();

                            if (clients.length) {
                                clients.forEach(c => {
                                    const name = c?.name || '';
                                    const idNumber = c?.id_number || '';
                                    const email = c?.email || '';
                                    const phone = c?.phone || '';
                                    const addresses = Array.isArray(c?.addresses) ? c.addresses : [];

                                    const headerParts = [idNumber, email].filter(Boolean).join(', ');
                                    $list.append(`
                                        <div class="list-group-item client-search-header">
                                            <div class="fw-bold">${escapeHtml(name || email || 'N/A')}${headerParts ? ' (' + escapeHtml(headerParts) + ')' : ''}</div>
                                        </div>
                                    `);

                                    if (!addresses.length) {
                                        $list.append(`
                                            <button type="button" class="list-group-item list-group-item-action client-no-address"
                                                data-name="${escapeHtml(name)}"
                                                data-email="${escapeHtml(email)}"
                                                data-phone="${escapeHtml(phone)}">
                                                <div class="fw-bold">Kiválasztás</div>
                                                <div class="small text-muted">Nincs rögzített cím</div>
                                            </button>
                                        `);
                                        return;
                                    }

                                    addresses.forEach(a => {
                                        const addrText = `${a?.zip_code || ''} ${a?.city || ''}, ${a?.address_line || ''}`.trim();
                                        $list.append(`
                                            <button type="button" class="list-group-item list-group-item-action client-address-item"
                                                data-name="${escapeHtml(name)}"
                                                data-email="${escapeHtml(email)}"
                                                data-phone="${escapeHtml(phone)}"
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
                    email: $btn.data('email') || '',
                    phone: $btn.data('phone') || '',
                    country: $btn.data('country') || 'HU',
                    zip: $btn.data('zip') || '',
                    city: $btn.data('city') || '',
                    line: $btn.data('line') || '',
                });
                clearPartnerClientResults();
            });

            $('#partner_client_search_results').on('click', '.client-no-address', function () {
                const $btn = $(this);
                fillPartnerFromClient({
                    name: $btn.data('name') || '',
                    email: $btn.data('email') || '',
                    phone: $btn.data('phone') || '',
                    country: 'HU',
                    zip: '',
                    city: '',
                    line: '',
                });
                clearPartnerClientResults();
                setTimeout(() => {
                    $('#partner_zip_code').trigger('focus');
                }, 0);
            });
            const table = $('#adminTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.documents.sales-invoices.data') }}',
                order: [[0, 'desc']],
                columns: [
                    { data: 'id' },
                    { data: 'invoice_number' },
                    { data: 'partner_name' },
                    { data: 'issued_at' },
                    { data: 'due_at' },
                    { data: 'currency' },
                    { data: 'gross_total' },
                    { data: 'created' },
                    { data: 'status' },
                    { data: 'payment_status' },
                    { data: 'action', orderable: false, searchable: false }
                ],
            });

            $('.filter-input').on('change keyup', function () {
                var i =$(this).attr('data-column');
                var v =$(this).val();
                table.columns(i).search(v).draw();
            });

            $('#addButton').on('click', async function () {
                resetForm('Új kimenő számla');
                const companyId = defaultCompanyId || $('#company_id').val();
                if (companyId) {
                    $('#company_id').val(companyId);
                }
                renderCompanyBlock($('#company_id').val());
                modal.show();
            });

            $('#previewSalesInvoice').on('click', function () {
                loadTestPdfPreview();
            });

            $('#sales_invoice_preview_wrap').on('wheel', function (e) {
                if (!e.ctrlKey) return;
                e.preventDefault();
                const delta = e.originalEvent.deltaY;
                const next = previewScale + (delta > 0 ? -0.1 : 0.1);
                setPreviewScale(next);
            });

            $('#company_id').on('change', function () {
                renderCompanyBlock($(this).val());
            });

            $('#adminTable').on('click', '.edit', async function () {

                resetForm('Kimenő számla szerkesztése');

                const row_data = $('#adminTable').DataTable().row($(this).parents('tr')).data();
                $('#invoice_id').val(row_data.id);
                $('#company_id').val(row_data.company_id || defaultCompanyId);
                renderCompanyBlock($('#company_id').val());
                $('#invoice_number').val(row_data.invoice_number);
                $('#partner_name').val(row_data.partner_name);
                $('#issued_at').val(todayDate());
                $('#fulfilled_at').val(todayDate());
                $('#due_at').val(addDays(todayDate(), 8));
                $('#currency').val(row_data.currency || 'HUF');
                $('#gross_total').val(row_data.gross_total);
                $('#status').val(row_data.status || 'draft');
                $('#payment_status').val(row_data.payment_status || 'unpaid');

                const pdfPath = row_data.pdf_path;
                if (pdfPath) {
                    const src = String(pdfPath).startsWith('http')
                        ? String(pdfPath)
                        : (String(pdfPath).startsWith('/') ? String(pdfPath) : `${window.appConfig.APP_URL}${String(pdfPath)}`);

                    $('#sales_invoice_preview_placeholder').hide();
                    $('#sales_invoice_preview_wrap').show();
                    $('#sales_invoice_preview_iframe').attr('src', src);
                    setPreviewScale(1);
                } else {
                    resetPreview();
                }

                items.splice(0, items.length);
                renderItems();
                syncItemsJson();
                $('#product_search').val('');
                $('#product_search_results').empty();

                modal.show();
            });

            modalDOM.addEventListener('hidden.bs.modal', function () {
                $('#product_search_results').empty();
                resetPreview();
                clearPartnerClientResults();
            });

            $('#salesInvoiceForm').on('submit', function (e) {
                e.preventDefault();

                syncItemsJson();

                const formData = new FormData(this);
                formData.append('_token', csrfToken);

                const saveBtn = $('#saveSalesInvoice');
                const originalSaveButtonHtml = saveBtn.html();
                saveBtn.html('Mentés...').prop('disabled', true);

                const invoiceId = $('#invoice_id').val();

                let url = '{{ route('admin.documents.sales-invoices.store') }}';
                let method = 'POST';

                if (invoiceId) {
                    url = `${window.appConfig.APP_URL}admin/bizonylatok/kimeno-szamlak/${invoiceId}`;
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success(response) {
                        showToast(response.message || 'Sikeres!', 'success');
                        table.ajax.reload(null, false);
                        modal.hide();
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
                        saveBtn.html(originalSaveButtonHtml).prop('disabled', false);
                    }
                });
            });

            const items = [];

            function formatMoney(value) {
                const n = Number(value);
                if (!Number.isFinite(n)) return '0';
                return String(Math.round(n));
            }

            function formatPercent(value) {
                const n = Number(value);
                if (!Number.isFinite(n)) return '0';
                return String(Math.round(n * 100) / 100);
            }

            function recalcRow(item) {
                const qty = Number(item.quantity);
                const unitGross = Number(item.unit_gross_price);
                const discountPercent = Number(item.discount_percent);
                const vatPercent = Number(item.vat_percent);

                const q = Number.isFinite(qty) ? qty : 0;
                const ug = Number.isFinite(unitGross) ? unitGross : 0;
                const disc = Number.isFinite(discountPercent) ? discountPercent : 0;
                const vat = Number.isFinite(vatPercent) ? vatPercent : 0;

                const discountedUnitGross = ug * (1 - (disc / 100));
                const unitNet = vat > 0 ? (discountedUnitGross / (1 + vat / 100)) : discountedUnitGross;
                const unitVat = discountedUnitGross - unitNet;

                item.unit_net_price = Math.round(unitNet);
                item.net_total = Math.round(unitNet * q);
                item.vat_total = Math.round(unitVat * q);
                item.gross_total = Math.round(discountedUnitGross * q);
            }

            function renderItems() {
                const tbody = $('#invoice_items_table tbody');
                tbody.empty();

                items.forEach((item, idx) => {
                    const row = $(
                        `<tr data-idx="${idx}">
                            <td>
                                <div class="fw-semibold">${item.name}</div>
                            </td>
                            <td style="width: 120px;">
                                <input type="number" min="${item.qty_step ?? 1}" step="${item.qty_step ?? 1}" class="form-control form-control-sm text-end item-qty" value="${item.quantity}">
                            </td>
                            <td class="text-center" style="width: 70px;">
                                <span class="text-muted">${item.unit_abbreviation ?? ''}</span>
                            </td>
                            <td style="width: 120px;">
                                <input type="number" step="0.01" class="form-control form-control-sm text-end item-discount" value="${formatPercent(item.discount_percent)}">
                            </td>
                            <td style="width: 140px;">
                                <input type="number" step="1" class="form-control form-control-sm text-end item-unit" value="${formatMoney(item.unit_gross_price)}">
                            </td>
                            <td style="width: 90px;">
                                <input type="number" step="0.01" class="form-control form-control-sm text-end item-vat" value="${formatPercent(item.vat_percent)}">
                            </td>
                            <td class="text-end" style="width: 120px;">
                                <span class="item-net">${formatMoney(item.net_total)}</span>
                            </td>
                            <td class="text-end" style="width: 120px;">
                                <span class="item-vat">${formatMoney(item.vat_total)}</span>
                            </td>
                            <td class="text-end" style="width: 120px;">
                                <span class="item-gross">${formatMoney(item.gross_total)}</span>
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
                const existing = items.find(i => i.product_id === product.id);
                if (existing) {
                    const step = Number(existing.qty_step);
                    const add = Number.isFinite(step) && step > 0 ? step : 1;
                    existing.quantity = Number(existing.quantity) + add;
                    recalcRow(existing);
                    renderItems();
                    syncItemsJson();
                    return;
                }

                const unitPrice = product.effective_gross_price ?? product.gross_price ?? 0;
                const vatPercent = product.tax_value ?? 0;
                const unitAbbrev = product.unit_abbreviation ?? '';
                const qtyStep = (() => {
                    const q = Number(product.unit_qty);
                    return Number.isFinite(q) && q > 1 ? q : 1;
                })();

                const item = {
                    product_id: product.id,
                    name: product.title,
                    quantity: qtyStep,
                    qty_step: qtyStep,
                    unit_abbreviation: unitAbbrev,
                    discount_percent: 0,
                    vat_percent: vatPercent,
                    unit_net_price: 0,
                    unit_gross_price: Math.round(Number(unitPrice) || 0),
                    net_total: 0,
                    vat_total: 0,
                    gross_total: 0,
                };

                recalcRow(item);
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

                searchTimeout = setTimeout(() => {
                    $.ajax({
                        url: `${window.appConfig.APP_URL}admin/termekek/search?q=${encodeURIComponent(q)}`,
                        method: 'GET',
                        success: function (resp) {
                            const results = resp?.products ?? [];
                            const container = $('#product_search_results');
                            container.empty();
                            results.forEach(p => {
                                const unitQty = Number(p.unit_qty);
                                const unitText = (p.unit_abbreviation || p.unit_name) ? `${escapeHtml(p.unit_abbreviation || p.unit_name)}` : '';
                                const packagingLabel = Number.isFinite(unitQty) && unitQty > 1
                                    ? ` (kiszerelés: ${escapeHtml(unitQty)}${unitText ? ' ' + unitText : ''})`
                                    : '';
                                const btn = $(
                                    `<button type="button" class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div><strong>${escapeHtml(p.title)}</strong>${packagingLabel}</div>
                                            <div class="text-muted">${formatMoney(p.effective_gross_price ?? p.gross_price ?? 0)} HUF</div>
                                        </div>
                                     </button>`
                                );
                                btn.on('click', function () {
                                    addProductAsItem(p);
                                    $('#product_search').val('');
                                    $('#product_search_results').empty();
                                    $('#product_search').trigger('focus');
                                });
                                container.append(btn);
                            });
                        },
                        error: function () {
                            $('#product_search_results').empty();
                        }
                    });
                }, 250);
            });

            $('#invoice_items_table').on('input', '.item-qty, .item-unit, .item-discount, .item-vat', function () {
                const tr = $(this).closest('tr');
                const idx = Number(tr.data('idx'));
                const item = items[idx];
                if (!item) return;

                const qtyInput = tr.find('.item-qty');
                const qtyRaw = Number(qtyInput.val());
                const unit = Number(tr.find('.item-unit').val());
                const discount = Number(tr.find('.item-discount').val());
                const vat = Number(tr.find('.item-vat').val());

                const minQty = Number(item.qty_step);
                const min = Number.isFinite(minQty) && minQty > 1 ? minQty : 1;
                const qty = Number.isFinite(qtyRaw) ? qtyRaw : min;
                const clampedQty = qty < min ? min : qty;
                if (clampedQty !== qtyRaw) {
                    qtyInput.val(clampedQty);
                }

                item.quantity = clampedQty;
                item.unit_gross_price = Number.isFinite(unit) ? unit : 0;
                item.discount_percent = Number.isFinite(discount) ? discount : 0;
                item.vat_percent = Number.isFinite(vat) ? vat : 0;
                recalcRow(item);
                tr.find('.item-net').text(formatMoney(item.net_total));
                tr.find('.item-vat').text(formatMoney(item.vat_total));
                tr.find('.item-gross').text(formatMoney(item.gross_total));
                syncItemsJson();
            });

            $('#invoice_items_table').on('click', '.item-remove', function () {
                const tr = $(this).closest('tr');
                const idx = Number(tr.data('idx'));
                if (!Number.isFinite(idx)) return;
                items.splice(idx, 1);
                renderItems();
                syncItemsJson();
            });

            (function initSalesInvoiceSplitResize() {
                const split = document.getElementById('salesInvoiceModal_split');
                if (!split) return;

                const leftHandle = split.querySelector('[data-resizer="left"]');
                const rightHandle = split.querySelector('[data-resizer="right"]');
                if (!leftHandle || !rightHandle) return;

                function pxToPercent(px) {
                    const w = split.getBoundingClientRect().width || 1;
                    return (px / w) * 100;
                }

                function setPanes(leftPercent, midPercent, rightPercent) {
                    split.style.setProperty('--pane-left', `${leftPercent}%`);
                    split.style.setProperty('--pane-mid', `${midPercent}%`);
                    split.style.setProperty('--pane-right', `${rightPercent}%`);
                }

                function currentPanes() {
                    const style = getComputedStyle(split);
                    const l = parseFloat(style.getPropertyValue('--pane-left')) || 25;
                    const m = parseFloat(style.getPropertyValue('--pane-mid')) || 50;
                    const r = parseFloat(style.getPropertyValue('--pane-right')) || 25;
                    return { l, m, r };
                }

                function startDrag(which, startEvent) {
                    if (window.innerWidth < 992) return; // lg breakpoint
                    startEvent.preventDefault();

                    const startX = startEvent.clientX;
                    const { l, m, r } = currentPanes();
                    const min = 15; // percent

                    document.body.style.userSelect = 'none';

                    function onMove(e) {
                        const dx = e.clientX - startX;
                        const delta = pxToPercent(dx);

                        if (which === 'left') {
                            let nl = l + delta;
                            let nm = m - delta;
                            if (nl < min) { nm -= (min - nl); nl = min; }
                            if (nm < min) { nl -= (min - nm); nm = min; }
                            if (nl < min) nl = min;
                            if (nm < min) nm = min;
                            setPanes(nl, nm, r);
                            return;
                        }

                        if (which === 'right') {
                            let nm = m + delta;
                            let nr = r - delta;
                            if (nr < min) { nm -= (min - nr); nr = min; }
                            if (nm < min) { nr -= (min - nm); nm = min; }
                            if (nr < min) nr = min;
                            if (nm < min) nm = min;
                            setPanes(l, nm, nr);
                        }
                    }

                    function onUp() {
                        document.body.style.userSelect = '';
                        window.removeEventListener('mousemove', onMove);
                        window.removeEventListener('mouseup', onUp);
                    }

                    window.addEventListener('mousemove', onMove);
                    window.addEventListener('mouseup', onUp);
                }

                leftHandle.addEventListener('mousedown', (e) => startDrag('left', e));
                rightHandle.addEventListener('mousedown', (e) => startDrag('right', e));
            })();

            function todayDate() {
                const d = new Date();
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            }

            function addDays(yyyyMmDd, days) {
                const [y, m, d] = String(yyyyMmDd).split('-').map(Number);
                const date = new Date(y, (m || 1) - 1, d || 1);
                date.setDate(date.getDate() + Number(days || 0));
                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const dd = String(date.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            }

            function resetForm(title = null) {
                $('#salesInvoiceForm')[0].reset();
                $('#salesInvoiceModalLabel').text(title);
                $('#invoice_id').val('');

                if (defaultCompanyId) {
                    $('#company_id').val(defaultCompanyId);
                }
                renderCompanyBlock($('#company_id').val());

                $('#currency').val('HUF');
                $('#partner_country').val('HU');
                $('#issued_at').val(todayDate());
                $('#fulfilled_at').val(todayDate());
                $('#due_at').val(addDays(todayDate(), 8));
                $('#status').val('draft');
                $('#payment_status').val('unpaid');
                $('#prices_include_vat').prop('checked', true);

                resetPreview();

                items.splice(0, items.length);
                renderItems();
                syncItemsJson();
                $('#product_search').val('');
                $('#product_search_results').empty();
            }
        });
    </script>
@endsection
