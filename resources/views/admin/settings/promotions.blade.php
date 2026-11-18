@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Webshop / Promóciók</h2>
            @if(auth('admin')->user()->can('create-promotion'))
                <button class="btn btn-success" id="addPromotion"><i class="fas fa-plus me-1"></i> Új promóció</button>
            @endif
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">

            @if(auth('admin')->user()->can('view-promotions'))

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
                        <input type="text" placeholder="Slug" class="filter-input form-control" data-column="2">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Érvényesség kezdete" class="filter-input form-control" data-column="4">
                    </div>

                    <div class="filter-group flex-grow-1 flex-md-shrink-0">
                        <input type="text" placeholder="Érvényesség vége" class="filter-input form-control" data-column="5">
                    </div>
                </div>

                <table class="table table-bordered display responsive nowrap" id="promotionsTable" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th data-priority="1">Név</th>
                        <th>Slug</th>
                        <th>Kedvezmény %</th>
                        <th>Érvényes tól</th>
                        <th>Érvényes ig</th>
                        <th>Státusz</th>
                        <th data-priority="2">Műveletek</th>
                    </tr>
                    </thead>
                </table>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i> Nincs jogosultságod a promóciók megtekintésére.
                </div>
            @endif
        </div>
    </div>


    <div class="modal fade" id="couponModal" tabindex="-1" aria-labelledby="couponModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="couponModalLabel">Kuponok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="coupon_promotion_id">
                    <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
                        <div>
                            <label for="coupon_quantity" class="form-label mb-1">Mennyiség</label>
                            <input type="number" class="form-control" id="coupon_quantity" min="1" max="500" value="10" style="width: 110px">
                        </div>
                        <div>
                            <label for="coupon_prefix" class="form-label mb-1">Prefix (opcionális)</label>
                            <input type="text" class="form-control" id="coupon_prefix" maxlength="20" placeholder="pl. NYEREM-" style="width: 180px">
                        </div>
                        <button type="button" class="btn btn-success" id="generateCouponsBtn" @if(!auth('admin')->user()->can('edit-promotion')) disabled @endif>
                            <i class="fas fa-plus me-1"></i> Kuponok generálása
                        </button>
                    </div>

                    <div id="couponLoader" class="text-center py-4" style="display:none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted">Betöltés...</div>
                    </div>

                    <div id="couponsTableWrapper">
                    <table class="table table-bordered table-sm" id="couponsTable">
                        <thead>
                        <tr>
                            <th>Kód</th>
                            <th>Státusz</th>
                            <th>Email</th>
                            <th>Használva</th>
                            <th>Műveletek</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="promotionModal" tabindex="-1" aria-labelledby="promotionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="promotionForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="promotionModalLabel">Promóció szerkesztése</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="promotion_id" name="id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Név</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug (opcionális, üresen a névből generálódik)</label>
                            <input type="text" class="form-control" id="slug" name="slug" maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Leírás</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="starts_at" class="form-label">Érvényes tól</label>
                                <input type="date" class="form-control" id="starts_at" name="starts_at">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ends_at" class="form-label">Érvényes ig</label>
                                <input type="date" class="form-control" id="ends_at" name="ends_at">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="discount_percent" class="form-label">Kedvezmény (%)</label>
                            <input type="number" class="form-control" id="discount_percent" name="discount_percent" min="0" max="100" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="active" class="form-label">Aktív</label>
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" class="" id="active" name="active" value="1" checked>
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
                tableId: 'promotionsTable',
                modalId: 'promotionModal',
                formId: 'promotionForm',
                addButtonId: 'addPromotion',
                dataUrl: '{{ route('admin.promotions.data') }}',
                storeUrl: '{{ route('admin.promotions.store') }}',
                destroyUrl: '{{ url('/admin/beallitasok/promociok') }}',
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'slug' },
                    { data: 'discount_percent' },
                    { data: 'starts_at' },
                    { data: 'ends_at' },
                    { data: 'active' },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });
        });

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const couponModalEl = document.getElementById('couponModal');
        const couponModal = couponModalEl ? new bootstrap.Modal(couponModalEl) : null;
        let currentPromotionId = null;

        function escapeHtml(str) {
            return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        }

        let couponsDataTable = null;

        function setCouponLoading(loading) {
            document.getElementById('couponLoader').style.display = loading ? '' : 'none';
            document.getElementById('couponsTableWrapper').style.display = loading ? 'none' : '';
        }

        function loadCoupons() {
            if (!currentPromotionId) return;
            setCouponLoading(true);
            fetch(`{{ url('/admin/beallitasok/promociok') }}/${currentPromotionId}/kuponok`, {
                headers: { 'Accept': 'application/json' }
            })
                .then(res => res.json())
                .then(data => {
                    renderCoupons(data.coupons || []);
                    setCouponLoading(false);
                })
                .catch(() => {
                    showToast('Hiba a kuponok betöltésekor', 'danger');
                    setCouponLoading(false);
                });
        }

        function renderCoupons(coupons) {
            if (!couponsDataTable) {
                couponsDataTable = $('#couponsTable').DataTable({
                    data: coupons,
                    columns: [
                        {
                            data: 'code',
                            render: d => `<code>${escapeHtml(d)}</code>`
                        },
                        {
                            data: 'assigned',
                            render: (d, t, row) => {
                                if (row.used) return '<span class="badge bg-secondary">Felhasznált</span>';
                                if (row.assigned) return '<span class="badge bg-info text-dark">Kiosztva</span>';
                                return '<span class="badge bg-success">Szabad</span>';
                            }
                        },
                        {
                            data: 'email',
                            defaultContent: '',
                            render: d => escapeHtml(d || '')
                        },
                        {
                            data: 'used_at',
                            orderable: false,
                            render: (d, t, row) => {
                                if (!row.assigned) return '';
                                if (row.used) {
                                    return `<span class="badge bg-info text-dark">Használva</span>${d ? `<br><small class="text-muted">${escapeHtml(d)}</small>` : ''}`;
                                }
                                return '<span class="badge bg-light text-dark border">Nincs</span>';
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: (d, t, row) => {
                                let html = '';
                                if (!row.assigned) {
                                    html += `<button class="btn btn-sm btn-primary use-coupon" data-id="${row.id}"><i class="fas fa-check"></i> Felhasznál</button>`;
                                } else if (!row.used) {
                                    html += `<button class="btn btn-sm btn-outline-info toggle-used" data-id="${row.id}"><i class="fas fa-check-double"></i> Használtnak jelöl</button>`;
                                }
                                return html;
                            }
                        }
                    ],
                    order: [],
                    pageLength: 25,
                    language: { url: '/lang/datatables/hu.json' }
                });
            } else {
                couponsDataTable.clear().rows.add(coupons).draw();
            }
        }

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.show-coupons');
            if (btn) {
                currentPromotionId = btn.dataset.id;
                document.getElementById('coupon_promotion_id').value = currentPromotionId;
                document.getElementById('couponModalLabel').textContent = 'Kuponok – ' + btn.dataset.name;
                loadCoupons();
                couponModal.show();
            }
        });

        document.getElementById('generateCouponsBtn')?.addEventListener('click', function () {
            if (!currentPromotionId) return;
            const quantity = document.getElementById('coupon_quantity').value;
            const prefix = document.getElementById('coupon_prefix').value;
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generálás...';
            setCouponLoading(true);

            fetch(`{{ url('/admin/beallitasok/promociok') }}/${currentPromotionId}/kuponok-generalas`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ quantity: parseInt(quantity, 10), prefix: prefix })
            })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Hiba');
                    showToast(data.message || 'Sikeres!', 'success');
                    loadCoupons();
                })
                .catch(err => showToast(err.message || 'Hiba!', 'danger'))
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
        });

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.toggle-used');
            if (!btn) return;

            fetch(`{{ url('/admin/beallitasok/promociok/kupon') }}/${btn.dataset.id}/hasznalat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({})
            })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Hiba');
                    showToast(data.message || 'Sikeres!', 'success');
                    loadCoupons();
                })
                .catch(err => showToast(err.message || 'Hiba!', 'danger'));
        });

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.use-coupon');
            if (!btn) return;
            const email = prompt('Add meg a felhasználó email címét:');
            if (!email) return;

            fetch(`{{ url('/admin/beallitasok/promociok/kupon') }}/${btn.dataset.id}/felhasznalas`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email: email })
            })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Hiba');
                    showToast(data.message || 'Sikeres!', 'success');
                    loadCoupons();
                })
                .catch(err => showToast(err.message || 'Hiba!', 'danger'));
        });
    </script>
@endsection