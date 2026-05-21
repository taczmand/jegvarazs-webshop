@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection

@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Ajánlatkészítő',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Ajánlatok', 'url' => route('partner.offers.index')],
            ['title' => 'Új ajánlat', 'url' => route('partner.offers.create')]
        ],
    ]])

    <div class="container mt-3">
        @if($errors->any())
            <div class="shop-validation-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('partner.offers.store') }}" id="partnerOfferForm">
            @csrf

            <div class="card mb-3">
                <div class="card-body">
                    <div class="form-group">
                        <label for="title">Ajánlat megnevezése</label>
                        <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="recipient_email">Címzett e-mail</label>
                        <input type="email" name="recipient_email" id="recipient_email" class="form-control" value="{{ old('recipient_email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="note">Megjegyzés</label>
                        <textarea name="note" id="note" class="form-control" rows="4">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Tételek</h5>

                    <div class="form-group">
                        <label>Termék keresés</label>
                        <input type="text" id="productSearch" class="form-control" placeholder="Kezdj el gépelni...">
                        <div id="productResults" class="list-group" style="display:none;"></div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light">
                            <tr>
                                <th>Tétel</th>
                                <th style="width:120px">Menny.</th>
                                <th style="width:180px">Br. egységár (Ft)</th>
                                <th style="width:80px"></th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-outline-secondary" id="addCustomItem">+ Egyedi tétel</button>
                    <input type="hidden" name="items_json" id="items_json" value="{{ old('items_json') }}">
                </div>
            </div>

            <div class="d-flex justify-content-end align-items-center" style="gap: .5rem;">
                <button type="button" class="btn btn-outline-secondary" id="previewPartnerOfferPdf">Előnézet</button>
                <button type="submit" class="site-btn">Ajánlat mentése és küldése</button>
            </div>

            <div class="mt-3" id="partnerOfferPdfPreviewWrap" style="display:none;">
                <div class="border rounded" style="overflow:hidden; background:#fff;">
                    <iframe id="partnerOfferPdfPreviewFrame" style="width:100%; height:520px; border:0;"></iframe>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
<script>
(function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const items = [];

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderItems() {
        const tbody = document.querySelector('#itemsTable tbody');
        tbody.innerHTML = items.map((it, idx) => {
            const title = escapeHtml(it.title || '');
            return `
                <tr data-idx="${idx}">
                    <td>${title}${it.type === 'product' ? ' <span class="badge badge-info">termék</span>' : ' <span class="badge badge-secondary">egyedi</span>'}</td>
                    <td><input type="number" class="form-control item-qty" min="1" value="${it.quantity || 1}"></td>
                    <td><input type="number" class="form-control item-price" min="0" step="0.01" value="${it.gross_price ?? 0}"></td>
                    <td><button type="button" class="btn btn-sm btn-danger item-remove">X</button></td>
                </tr>
            `;
        }).join('');

        document.getElementById('items_json').value = JSON.stringify(items);
    }

    let searchDebounce;
    let searchAbortController;
    const searchInput = document.getElementById('productSearch');
    const resultsEl = document.getElementById('productResults');

    searchInput.addEventListener('input', function() {
        const q = (searchInput.value || '').trim();
        clearTimeout(searchDebounce);
        resultsEl.style.display = 'none';
        resultsEl.innerHTML = '';
        if (q.length < 2) return;

        searchDebounce = setTimeout(async () => {
            try {
                if (searchAbortController) {
                    searchAbortController.abort();
                }
                searchAbortController = new AbortController();

                const res = await fetch(`${window.appConfig.APP_URL.replace(/\/+$/, '')}/partner/ajanlat/termekek?q=${encodeURIComponent(q)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                    ,
                    signal: searchAbortController.signal
                });

                if (!res.ok) {
                    resultsEl.style.display = 'none';
                    resultsEl.innerHTML = '';
                    return;
                }
                const payload = await res.json();
                const products = Array.isArray(payload.products) ? payload.products : [];

                resultsEl.innerHTML = products.map(p => {
                    return `
                        <button type="button" class="list-group-item list-group-item-action product-pick" data-id="${p.id}" data-title="${escapeHtml(p.title)}" data-price="${p.gross_price}">
                            <div class="d-flex justify-content-between">
                                <div>${escapeHtml(p.title)}</div>
                                <div><strong>${Number(p.gross_price || 0).toFixed(0)} Ft</strong></div>
                            </div>
                        </button>
                    `;
                }).join('');

                resultsEl.style.display = products.length ? 'block' : 'none';
            } catch (e) {
                if (e && e.name === 'AbortError') {
                    return;
                }
                resultsEl.style.display = 'none';
            }
        }, 350);
    });

    resultsEl.addEventListener('click', function(e) {
        const btn = e.target.closest('.product-pick');
        if (!btn) return;

        const id = Number(btn.getAttribute('data-id'));
        const title = btn.getAttribute('data-title') || '';
        const price = Number(btn.getAttribute('data-price') || 0);

        items.push({ type: 'product', product_id: id, title: title, quantity: 1, gross_price: price });
        renderItems();

        resultsEl.style.display = 'none';
        resultsEl.innerHTML = '';
        searchInput.value = '';
    });

    document.getElementById('addCustomItem').addEventListener('click', function() {
        const title = prompt('Egyedi tétel megnevezése:');
        if (!title) return;
        items.push({ type: 'custom', title: title, quantity: 1, gross_price: 0 });
        renderItems();
    });

    document.querySelector('#itemsTable').addEventListener('input', function(e) {
        const row = e.target.closest('tr[data-idx]');
        if (!row) return;
        const idx = Number(row.getAttribute('data-idx'));
        if (!items[idx]) return;

        if (e.target.classList.contains('item-qty')) {
            items[idx].quantity = Math.max(1, Number(e.target.value || 1));
        }
        if (e.target.classList.contains('item-price')) {
            items[idx].gross_price = Math.max(0, Number(e.target.value || 0));
        }
        document.getElementById('items_json').value = JSON.stringify(items);
    });

    document.querySelector('#itemsTable').addEventListener('click', function(e) {
        const btn = e.target.closest('.item-remove');
        if (!btn) return;
        const row = btn.closest('tr[data-idx]');
        const idx = Number(row.getAttribute('data-idx'));
        items.splice(idx, 1);
        renderItems();
    });

    // Restore old items on validation error
    const old = document.getElementById('items_json').value;
    if (old) {
        try {
            const parsed = JSON.parse(old);
            if (Array.isArray(parsed)) {
                parsed.forEach(x => items.push(x));
                renderItems();
            }
        } catch (e) {}
    }

    const previewBtn = document.getElementById('previewPartnerOfferPdf');
    const previewWrap = document.getElementById('partnerOfferPdfPreviewWrap');
    const previewFrame = document.getElementById('partnerOfferPdfPreviewFrame');
    let previewObjectUrl;

    previewBtn.addEventListener('click', async function() {
        const titleInput = document.getElementById('title');
        const noteInput = document.getElementById('note');

        if (!titleInput || !titleInput.value.trim()) {
            alert('Az ajánlat megnevezése kötelező az előnézethez.');
            return;
        }

        document.getElementById('items_json').value = JSON.stringify(items);
        if (!items.length) {
            alert('Legalább 1 tétel szükséges az előnézethez.');
            return;
        }

        try {
            const res = await fetch("{{ route('partner.offers.preview-pdf') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/pdf',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    title: titleInput.value.trim(),
                    note: noteInput ? noteInput.value : null,
                    items_json: document.getElementById('items_json').value,
                }),
            });

            if (!res.ok) {
                alert('Nem sikerült a PDF előnézet generálása.');
                return;
            }

            const blob = await res.blob();
            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
            }
            previewObjectUrl = URL.createObjectURL(blob);

            previewFrame.src = previewObjectUrl + '#page=1&view=FitH';
            previewWrap.style.display = 'block';
        } catch (e) {
            alert('Hiba történt a PDF előnézet generálása során.');
        }
    });
})();
</script>
@endsection
