@extends('layouts.admin')

@section('content')
    <div class="container p-0">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyvitel / Tömeges e-mail</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-bold">Címzettek</label>

                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="recipient_group" id="recipient_group_all" value="all" checked>
                            <label class="form-check-label" for="recipient_group_all">Mindenki (ügyfelek + vásárlók)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="recipient_group" id="recipient_group_clients" value="clients">
                            <label class="form-check-label" for="recipient_group_clients">Csak ügyfelek</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="recipient_group" id="recipient_group_customers" value="customers">
                            <label class="form-check-label" for="recipient_group_customers">Csak vásárlók</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="recipient_group" id="recipient_group_partners" value="partners">
                            <label class="form-check-label" for="recipient_group_partners">Klímaszerelő partnerek</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="recipient_group" id="recipient_group_custom" value="custom">
                            <label class="form-check-label" for="recipient_group_custom">Egyedi lista</label>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6" id="clientPickerWrap">
                    <label for="client_search" class="form-label">Ügyfelek hozzáadása</label>
                    <input type="text" class="form-control" id="client_search" placeholder="Keresés (név/e-mail/telefon)..." autocomplete="off">
                    <div id="client_search_results" class="list-group mt-2" style="max-height: 220px; overflow: auto;"></div>
                </div>

                <div class="col-12 col-lg-6" id="customerPickerWrap">
                    <label for="customer_search" class="form-label">Vásárlók hozzáadása</label>
                    <input type="text" class="form-control" id="customer_search" placeholder="Keresés (név/e-mail/telefon)..." autocomplete="off">
                    <div id="customer_search_results" class="list-group mt-2" style="max-height: 220px; overflow: auto;"></div>
                </div>

                <div class="col-12">
                    <label for="manual_emails" class="form-label">Plusz e-mail címek (kézzel)</label>
                    <textarea class="form-control" id="manual_emails" rows="3" placeholder="pl.: valaki@pelda.hu; masik@pelda.hu"></textarea>
                    <div class="form-text">Elválasztó: pontosvessző, vessző vagy szóköz.</div>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold">Címzettek (akiknek az e-mail menni fog)</div>
                        <div class="small text-muted" id="recipientCount"></div>
                    </div>

                    <div id="recipientEmails" class="border rounded p-2" style="max-height: 260px; overflow: auto;"></div>

                    <div class="small text-muted mt-2" id="recipientHint"></div>
                </div>

                <div class="col-12">
                    <label for="subject" class="form-label">Tárgy</label>
                    <input type="text" class="form-control" id="subject" placeholder="Tárgy...">
                </div>

                <div class="col-12">
                    <label for="bulk_email_body" class="form-label">Tartalom</label>
                    <textarea class="form-control" id="bulk_email_body" rows="12"></textarea>
                </div>

                <div class="col-12 d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary" id="sendBulkEmail">
                        Küldés
                    </button>
                    <div id="sendStatus" class="small"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.tiny.cloud/1/k486ypuedp01hfc64g7mn3t9rc5lp8h53a5korymr6qvuvb9/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script type="module">
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const selected = {
            clients: new Map(),
            customers: new Map(),
        };

        const excludedEmails = new Set();

        let lastPreviewTotal = 0;
        let lastPreviewShown = 0;
        let lastPreviewTruncated = false;

        function setStatus(text, type = 'muted') {
            const el = document.getElementById('sendStatus');
            el.classList.remove('text-muted', 'text-danger', 'text-success');
            if (type === 'success') el.classList.add('text-success');
            else if (type === 'danger') el.classList.add('text-danger');
            else el.classList.add('text-muted');
            el.textContent = text || '';
        }

        function getRecipientGroup() {
            const checked = document.querySelector('input[name="recipient_group"]:checked');
            return checked ? checked.value : 'all';
        }

        function parseManualEmails() {
            const raw = (document.getElementById('manual_emails').value || '').trim();
            if (!raw) return [];
            return raw
                .split(/[;\s,]+/g)
                .map(x => (x || '').trim())
                .filter(Boolean);
        }

        function renderRecipientCount() {
            const el = document.getElementById('recipientCount');
            const excludedCount = excludedEmails.size;
            const excludedText = excludedCount ? ` (kizárva: ${excludedCount})` : '';

            if (lastPreviewTotal === 0) {
                el.textContent = `0 címzett${excludedText}`;
                return;
            }

            if (lastPreviewTruncated) {
                el.textContent = `${lastPreviewTotal} címzett (megjelenítve: ${lastPreviewShown})${excludedText}`;
                return;
            }

            el.textContent = `${lastPreviewTotal} címzett${excludedText}`;
        }

        function renderRecipientEmails(emails) {
            const wrap = document.getElementById('recipientEmails');
            wrap.innerHTML = '';

            if (!Array.isArray(emails) || emails.length === 0) {
                wrap.innerHTML = '<div class="text-muted small">Nincs címzett.</div>';
                return;
            }

            emails.forEach(email => {
                const row = document.createElement('div');
                row.className = 'd-flex justify-content-between align-items-center border-bottom py-1';

                const left = document.createElement('div');
                left.className = 'fw-semibold';
                left.textContent = email;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-outline-danger';
                btn.textContent = 'X';
                btn.addEventListener('click', () => {
                    excludedEmails.add(String(email).toLowerCase());
                    updateRecipientsPreview();
                });

                row.appendChild(left);
                row.appendChild(btn);
                wrap.appendChild(row);
            });
        }

        async function updateRecipientsPreview() {
            const recipient_group = getRecipientGroup();
            const client_ids = Array.from(selected.clients.keys());
            const customer_ids = Array.from(selected.customers.keys());
            const manual_emails = parseManualEmails();
            const excluded_emails = Array.from(excludedEmails.values());

            document.getElementById('recipientHint').textContent = 'Betöltés...';

            try {
                const response = await fetch(`{{ route('admin.bulk-emails.recipients') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        recipient_group,
                        client_ids,
                        customer_ids,
                        manual_emails,
                        excluded_emails,
                    })
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    document.getElementById('recipientHint').textContent = payload?.message || 'Hiba történt a címzettek betöltésekor.';
                    lastPreviewTotal = 0;
                    lastPreviewShown = 0;
                    lastPreviewTruncated = false;
                    renderRecipientCount();
                    renderRecipientEmails([]);
                    return;
                }

                const emails = Array.isArray(payload?.emails) ? payload.emails : [];
                lastPreviewTotal = Number(payload?.total || 0);
                lastPreviewShown = Number(payload?.shown || emails.length || 0);
                lastPreviewTruncated = Boolean(payload?.truncated);

                renderRecipientCount();
                renderRecipientEmails(emails);

                if (lastPreviewTruncated) {
                    document.getElementById('recipientHint').textContent = 'A lista túl nagy, csak az első címzettek vannak megjelenítve.';
                } else {
                    document.getElementById('recipientHint').textContent = '';
                }
            } catch (e) {
                document.getElementById('recipientHint').textContent = 'Hálózati hiba történt a címzettek betöltésekor.';
                lastPreviewTotal = 0;
                lastPreviewShown = 0;
                lastPreviewTruncated = false;
                renderRecipientCount();
                renderRecipientEmails([]);
            }
        }

        function createResultItem(item, type) {
            const a = document.createElement('button');
            a.type = 'button';
            a.className = 'list-group-item list-group-item-action';
            const title = (item.name || '').trim();
            const email = (item.email || '').trim();
            const phone = (item.phone || '').trim();
            a.textContent = `${title}${email ? ' – ' + email : ''}${phone ? ' – ' + phone : ''}`;

            a.addEventListener('click', function () {
                if (!email) return;
                if (type === 'client') selected.clients.set(item.id, item);
                if (type === 'customer') selected.customers.set(item.id, item);
                updateRecipientsPreview();
            });

            return a;
        }

        async function searchClients(q) {
            const url = new URL(`{{ route('admin.clients.search') }}`);
            url.searchParams.set('q', q);
            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return [];
            const payload = await res.json().catch(() => ({}));
            return Array.isArray(payload?.clients) ? payload.clients : [];
        }

        async function searchCustomers(q) {
            const url = new URL(`{{ route('admin.customers.search') }}`);
            url.searchParams.set('q', q);
            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return [];
            const payload = await res.json().catch(() => ({}));
            return Array.isArray(payload?.customers) ? payload.customers : [];
        }

        function debounce(fn, wait) {
            let t = null;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...args), wait);
            };
        }

        const onClientSearch = debounce(async () => {
            const q = (document.getElementById('client_search').value || '').trim();
            const wrap = document.getElementById('client_search_results');
            wrap.innerHTML = '';
            if (q.length < 2) return;

            const items = await searchClients(q);
            items.forEach(item => wrap.appendChild(createResultItem(item, 'client')));
        }, 250);

        const onCustomerSearch = debounce(async () => {
            const q = (document.getElementById('customer_search').value || '').trim();
            const wrap = document.getElementById('customer_search_results');
            wrap.innerHTML = '';
            if (q.length < 2) return;

            const items = await searchCustomers(q);
            items.forEach(item => wrap.appendChild(createResultItem(item, 'customer')));
        }, 250);

        document.getElementById('client_search').addEventListener('input', onClientSearch);
        document.getElementById('customer_search').addEventListener('input', onCustomerSearch);
        document.getElementById('manual_emails').addEventListener('input', debounce(updateRecipientsPreview, 250));

        document.querySelectorAll('input[name="recipient_group"]').forEach(r => {
            r.addEventListener('change', () => {
                const group = getRecipientGroup();
                const clientWrap = document.getElementById('clientPickerWrap');
                const customerWrap = document.getElementById('customerPickerWrap');

                const showPickers = (group === 'clients' || group === 'customers' || group === 'partners' || group === 'custom');
                clientWrap.style.display = showPickers ? '' : 'none';
                customerWrap.style.display = showPickers ? '' : 'none';
                updateRecipientsPreview();
            });
        });

        tinymce.init({
            selector: 'textarea#bulk_email_body',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table'
        });

        updateRecipientsPreview();

        document.getElementById('sendBulkEmail').addEventListener('click', async function () {
            const recipient_group = getRecipientGroup();
            const subject = (document.getElementById('subject').value || '').toString().trim();
            const editor = tinymce.get('bulk_email_body');
            const html = editor ? (editor.getContent() || '').toString() : (document.getElementById('bulk_email_body').value || '').toString();

            const client_ids = Array.from(selected.clients.keys());
            const customer_ids = Array.from(selected.customers.keys());
            const manual_emails = parseManualEmails();
            const excluded_emails = Array.from(excludedEmails.values());

            if (!subject) {
                setStatus('A tárgy mező kötelező.', 'danger');
                return;
            }
            if (!html || html.trim() === '') {
                setStatus('A tartalom üres.', 'danger');
                return;
            }

            setStatus('Küldés folyamatban...', 'muted');
            this.disabled = true;

            try {
                const response = await fetch(`{{ route('admin.bulk-emails.send') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        recipient_group,
                        client_ids,
                        customer_ids,
                        manual_emails,
                        excluded_emails,
                        subject,
                        html,
                    })
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    setStatus(payload?.message || 'Hiba történt a küldés során.', 'danger');
                    return;
                }

                setStatus(`${payload?.message || 'E-mail kiküldve.'} (${payload?.count || 0} címzett)`, 'success');
            } catch (e) {
                setStatus('Hálózati hiba történt a küldés során.', 'danger');
            } finally {
                this.disabled = false;
            }
        });
    </script>
@endsection
