@extends('layouts.admin')

@section('content')

    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-3 pb-2">
            <h2 class="color-dark-blue mb-0">Ügyviteli folyamatok / Készpénz tételek</h2>
        </div>

        <div class="rounded-xl bg-white shadow-lg p-4">
            @if(isset($canViewCashReceipts) && !$canViewCashReceipts)
                <div class="alert alert-warning mb-0">
                    Nincs jogosultságod a készpénz tételek megtekintéséhez.
                </div>
            @else
                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-2">
                        <label class="form-label mb-1">Forrás</label>
                        <select class="form-select form-select-sm" id="filter_related_type">
                            <option value="">Összes</option>
                            <option value="contract">Szerződés</option>
                            <option value="worksheet">Munkalap</option>
                            <option value="other">Egyéb</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label mb-1">Név</label>
                        <input type="text" class="form-control form-control-sm" id="filter_received_from_name" />
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label mb-1">Átvette</label>
                        <input type="text" class="form-control form-control-sm" id="filter_received_by_name" />
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label mb-1">Megjegyzés</label>
                        <input type="text" class="form-control form-control-sm" id="filter_note" />
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label mb-1">Létrehozva tól</label>
                        <input type="date" class="form-control form-control-sm" id="filter_created_at_from" />
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label mb-1">Létrehozva ig</label>
                        <input type="date" class="form-control form-control-sm" id="filter_created_at_to" />
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label mb-1">Státusz</label>
                        <select class="form-select form-select-sm" id="filter_status">
                            <option value="">Összes</option>
                            <option value="pending">Függőben</option>
                            <option value="acknowledged">Nyugtázva</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label mb-1">Nyugtázta</label>
                        <input type="text" class="form-control form-control-sm" id="filter_acknowledged_by_name" />
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label mb-1">Nyugtázva tól</label>
                        <input type="date" class="form-control form-control-sm" id="filter_acknowledged_at_from" />
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label mb-1">Nyugtázva ig</label>
                        <input type="date" class="form-control form-control-sm" id="filter_acknowledged_at_to" />
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end gap-2">
                        <button class="btn btn-sm btn-primary" id="applyFilters" type="button">Szűrés</button>
                        <button class="btn btn-sm btn-outline-secondary" id="resetFilters" type="button">Törlés</button>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-2">
                    <button class="btn btn-sm btn-success" type="button" id="openCreateCashReceipt">+ Új tétel</button>
                </div>

                <table class="table table-bordered display responsive nowrap" id="adminTable" style="width:100%">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllRows" /></th>
                        <th>ID</th>
                        <th>Forrás</th>
                        <th>Név</th>
                        <th>Átvette</th>
                        <th>Összeg</th>
                        <th>Elszámolt összeg</th>
                        <th>Megjegyzés</th>
                        <th>Dátum</th>
                        <th>Státusz</th>
                        <th>Nyugtázta</th>
                        <th>Nyugtázva</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Kijelöltek összege összesen:</th>
                        <th id="amountSumFooter" class="fw-bold text-end"></th>
                        <th id="settledSumFooter" class="fw-bold text-end"></th>
                        <th colspan="5" class="text-end">
                            <button class="btn btn-sm btn-success" id="bulkAcknowledge" type="button" disabled>Kijelöltek nyugtázása</button>
                        </th>
                    </tr>
                    </tfoot>
                </table>
            @endif
        </div>

    </div>

    <div class="modal fade" id="createCashReceiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" id="createCashReceiptForm">
                <div class="modal-header">
                    <h5 class="modal-title">Új készpénz tétel (Egyéb)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Forrás</label>
                        <input type="text" class="form-control" value="Egyéb" disabled />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Név</label>
                        <select class="form-select" name="received_from_user_id" required>
                            <option value="">Válassz...</option>
                            @foreach(($users ?? []) as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Összeg (Ft)</label>
                        <input type="number" class="form-control" name="amount" step="1" required />
                        <div class="form-text">Lehet negatív is (mínuszos tétel).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dátum</label>
                        <input type="date" class="form-control" name="received_date" />
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Megjegyzés</label>
                        <input type="text" class="form-control" name="note" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="submit" class="btn btn-success" id="createCashReceiptSubmit">Létrehozás</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            @if(isset($canViewCashReceipts) && !$canViewCashReceipts)
            return;
            @endif

            $('#filter_status').val('pending');

            const selectedIds = new Set();
            const editedValues = {};

            function updateBulkButtonState() {
                const hasSelected = selectedIds.size > 0;
                $('#bulkAcknowledge').prop('disabled', !hasSelected);
            }

            function updateSelectedTotals() {
                let amountSum = 0;
                let settledSum = 0;

                const rows = table.rows({page: 'current'}).data().toArray();
                for (const r of rows) {
                    if (!r || !selectedIds.has(String(r.id))) {
                        continue;
                    }

                    const amountRaw = (r.amount_raw !== undefined) ? r.amount_raw : null;
                    let settledRaw = (r.settled_amount_raw !== undefined) ? r.settled_amount_raw : r.settled_amount;
                    if (settledRaw === null || settledRaw === undefined || settledRaw === '') {
                        settledRaw = amountRaw;
                    }

                    const a = parseInt(String(amountRaw ?? '').replace(/[^0-9\-]/g, ''), 10);
                    const s = parseInt(String(settledRaw ?? '').replace(/[^0-9\-]/g, ''), 10);

                    if (!isNaN(a)) amountSum += a;
                    if (!isNaN(s)) settledSum += s;
                }

                const fmt = function (n) {
                    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' Ft';
                };

                $('#amountSumFooter').text(fmt(amountSum));
                $('#settledSumFooter').text(fmt(settledSum));

                updateBulkButtonState();
            }

            const table = $('#adminTable').DataTable({
                language: {
                    url: '/lang/datatables/hu.json'
                },
                processing: true,
                serverSide: true,
                responsive: true,
                order: [[1, 'desc']],
                ajax: {
                    type: 'POST',
                    url: '{{ route('admin.cash-receipts.data-simple.post') }}',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    data: function (d) {
                        const order = (d && d.order && d.order[0]) ? d.order[0] : null;
                        const orderIdx = order ? order.column : null;
                        const orderDir = order ? order.dir : 'desc';
                        const orderCol = (orderIdx !== null && d.columns && d.columns[orderIdx]) ? d.columns[orderIdx].data : 'id';

                        return {
                            _token: '{{ csrf_token() }}',
                            draw: d.draw,
                            start: d.start,
                            length: d.length,
                            order_col: orderCol,
                            order_dir: orderDir,
                            search: (d.search && d.search.value) ? d.search.value : '',
                            filter_related_type: $('#filter_related_type').val(),
                            filter_received_from_name: $('#filter_received_from_name').val(),
                            filter_received_by_name: $('#filter_received_by_name').val(),
                            filter_note: $('#filter_note').val(),
                            filter_created_at_from: $('#filter_created_at_from').val(),
                            filter_created_at_to: $('#filter_created_at_to').val(),
                            filter_status: $('#filter_status').val(),
                            filter_acknowledged_by_name: $('#filter_acknowledged_by_name').val(),
                            filter_acknowledged_at_from: $('#filter_acknowledged_at_from').val(),
                            filter_acknowledged_at_to: $('#filter_acknowledged_at_to').val(),
                        };
                    },
                    error: function (xhr) {
                        const preview = (xhr && xhr.responseText)
                            ? xhr.responseText.toString().slice(0, 500)
                            : '';
                        const headers = (xhr && typeof xhr.getAllResponseHeaders === 'function')
                            ? xhr.getAllResponseHeaders()
                            : '';
                        console.error('Cash receipts DataTables AJAX error', {
                            status: xhr ? xhr.status : null,
                            statusText: xhr ? xhr.statusText : null,
                            responseLength: (xhr && xhr.responseText) ? xhr.responseText.toString().length : 0,
                            responseHeaders: headers,
                            responsePreview: preview,
                        });
                    }
                },
                columnDefs: [
                    {targets: 0, orderable: false},
                    {targets: [4, 5], className: 'text-end'}
                ],
                columns: [
                    {
                        data: 'id',
                        name: 'row_select',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            const checked = selectedIds.has(String(row.id)) ? 'checked' : '';
                            const disabled = (row.status !== 'Függőben') ? 'disabled' : '';
                            return '<input type="checkbox" class="row-select" data-id="' + row.id + '" ' + checked + ' ' + disabled + ' />';
                        }
                    },
                    {data: 'id', name: 'id'},
                    {data: 'related_type', name: 'related_type'},
                    {data: 'received_from_name', name: 'received_from_name'},
                    {data: 'received_by_name', name: 'received_by_name', orderable: false, searchable: false},
                    {data: 'amount', name: 'amount'},
                    {
                        data: 'settled_amount',
                        name: 'settled_amount',
                        orderable: false,
                        render: function (data, type, row) {
                            let raw = (row && row.settled_amount_raw !== undefined) ? row.settled_amount_raw : data;
                            if (raw === null || raw === undefined || raw === '') {
                                raw = (row && row.amount_raw !== undefined) ? row.amount_raw : '';
                            }
                            const formatFt = function (v) {
                                if (v === null || v === undefined || v === '') return '';
                                const n = parseInt(String(v).replace(/[^0-9\-]/g, ''), 10);
                                if (isNaN(n)) return '';
                                return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' Ft';
                            };
                            return '<span class="editable-cell" data-field="settled_amount">' + formatFt(raw) + '</span>';
                        }
                    },
                    {
                        data: 'note',
                        name: 'note',
                        orderable: false,
                        render: function (data, type, row) {
                            const val = (row && row.note_raw !== undefined) ? row.note_raw : (data ?? '');
                            const safe = $('<div>').text(val ?? '').html();
                            return '<span class="editable-cell" data-field="note">' + safe + '</span>';
                        }
                    },
                    {data: 'received_date', name: 'received_date'},
                    {data: 'status', name: 'status'},
                    {data: 'acknowledged_by_name', name: 'acknowledged_by_name', orderable: false, searchable: false},
                    {data: 'acknowledged_at', name: 'acknowledged_at'},
                ]
                ,
                footerCallback: function (row, data, start, end, display) {
                    updateSelectedTotals();
                }
            });

            table.on('draw', function () {
                $('#selectAllRows').prop('checked', false);
                updateBulkButtonState();
            });

            $('#applyFilters').on('click', function () {
                selectedIds.clear();
                for (const k in editedValues) {
                    delete editedValues[k];
                }
                updateSelectedTotals();
                table.ajax.reload();
            });

            $('#resetFilters').on('click', function () {
                $('#filter_related_type').val('');
                $('#filter_received_from_name').val('');
                $('#filter_received_by_name').val('');
                $('#filter_note').val('');
                $('#filter_created_at_from').val('');
                $('#filter_created_at_to').val('');
                $('#filter_status').val('');
                $('#filter_acknowledged_by_name').val('');
                $('#filter_acknowledged_at_from').val('');
                $('#filter_acknowledged_at_to').val('');
                selectedIds.clear();
                for (const k in editedValues) {
                    delete editedValues[k];
                }
                updateSelectedTotals();
                table.ajax.reload();
            });

            const createCashReceiptModalEl = document.getElementById('createCashReceiptModal');
            const createCashReceiptModal = new bootstrap.Modal(createCashReceiptModalEl);

            $('#openCreateCashReceipt').on('click', function () {
                const form = document.getElementById('createCashReceiptForm');
                if (form) {
                    form.reset();
                }
                createCashReceiptModal.show();
            });

            $('#createCashReceiptForm').on('submit', function (e) {
                e.preventDefault();

                const $btn = $('#createCashReceiptSubmit');
                const originalHtml = $btn.html();

                const payload = $(this).serializeArray().reduce((acc, item) => {
                    acc[item.name] = item.value;
                    return acc;
                }, {});
                payload._token = '{{ csrf_token() }}';

                $.ajax({
                    url: '{{ route('admin.cash-receipts.store') }}',
                    method: 'POST',
                    data: payload,
                    beforeSend: function () {
                        $btn.prop('disabled', true);
                        $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Létrehozás...');
                    },
                    success: function (resp) {
                        createCashReceiptModal.hide();
                        table.ajax.reload(null, false);
                        if (resp && resp.message && typeof window.showToast === 'function') {
                            window.showToast(resp.message, 'success');
                        }
                    },
                    error: function (xhr) {
                        let msg = 'Hiba történt.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        }
                        if (typeof window.showToast === 'function') {
                            window.showToast(msg, 'danger');
                        }
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                        $btn.html(originalHtml);
                    }
                });
            });

            function isPendingRow(rowData) {
                return rowData && rowData.status === 'Függőben';
            }

            function parseNumber(value) {
                if (value === null || value === undefined) return null;
                const s = String(value).replace(/[^0-9\-]/g, '').trim();
                if (s === '') return null;
                const n = parseInt(s, 10);
                return isNaN(n) ? null : n;
            }

            $(document).on('dblclick', '#adminTable tbody td', function () {
                const cell = table.cell(this);
                const rowData = table.row(this).data();
                if (!rowData || !isPendingRow(rowData)) {
                    return;
                }

                const $target = $(this).find('.editable-cell');
                if ($target.length === 0) {
                    return;
                }

                const field = $target.data('field');
                if (field !== 'settled_amount' && field !== 'note') {
                    return;
                }

                if (field === 'settled_amount') {
                    const current = (rowData.settled_amount_raw !== undefined) ? rowData.settled_amount_raw : rowData.settled_amount;
                    const currentNumber = parseNumber(current);
                    $(this).html('<input type="text" class="form-control form-control-sm cash-edit" data-field="settled_amount" value="' + (currentNumber ?? '') + '" />');
                    $(this).find('input').focus().select();
                }

                if (field === 'note') {
                    const current = (rowData.note_raw !== undefined) ? rowData.note_raw : (rowData.note ?? '');
                    const safe = $('<div>').text(current ?? '').html();
                    $(this).html('<input type="text" class="form-control form-control-sm cash-edit" data-field="note" value="' + safe + '" />');
                    $(this).find('input').focus().select();
                }
            });

            $(document).on('blur keydown', '.cash-edit', function (e) {
                if (e.type === 'keydown' && e.key !== 'Enter') {
                    return;
                }
                if (e.type === 'keydown') {
                    e.preventDefault();
                }

                const $input = $(this);
                const td = $input.closest('td');
                const field = $input.data('field');
                const newVal = $input.val();

                const row = table.row(td.closest('tr'));
                const rowData = row.data();
                if (!rowData || !isPendingRow(rowData)) {
                    table.draw(false);
                    return;
                }

                if (field === 'settled_amount') {
                    const n = parseNumber(newVal);
                    rowData.settled_amount_raw = (n === null) ? '' : n;
                    editedValues[String(rowData.id)] = editedValues[String(rowData.id)] || {};
                    editedValues[String(rowData.id)].settled_amount = (n === null) ? '' : n;
                }
                if (field === 'note') {
                    rowData.note_raw = (newVal ?? '').toString();
                    editedValues[String(rowData.id)] = editedValues[String(rowData.id)] || {};
                    editedValues[String(rowData.id)].note = (newVal ?? '').toString();
                }

                row.data(rowData).invalidate();

                if (selectedIds.has(String(rowData.id))) {
                    updateSelectedTotals();
                }
            });

            $(document).on('change', '.row-select', function () {
                const id = String($(this).data('id'));
                if ($(this).is(':checked')) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);

                    if (editedValues[id]) {
                        delete editedValues[id];
                    }

                    const tr = $(this).closest('tr');
                    const row = table.row(tr);
                    const rowData = row.data();
                    if (rowData && String(rowData.id) === id) {
                        rowData.settled_amount_raw = '';
                        rowData.note_raw = '';
                        row.data(rowData).invalidate();
                    }
                }
                updateSelectedTotals();
            });

            $('#selectAllRows').on('change', function () {
                const checked = $(this).is(':checked');
                $('#adminTable tbody .row-select').each(function () {
                    if ($(this).is(':disabled')) {
                        return;
                    }
                    $(this).prop('checked', checked);
                    const id = String($(this).data('id'));
                    if (checked) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);

                        if (editedValues[id]) {
                            delete editedValues[id];
                        }

                        const tr = $(this).closest('tr');
                        const row = table.row(tr);
                        const rowData = row.data();
                        if (rowData && String(rowData.id) === id) {
                            rowData.settled_amount_raw = '';
                            rowData.note_raw = '';
                            row.data(rowData).invalidate();
                        }
                    }
                });
                updateSelectedTotals();
            });

            $('#bulkAcknowledge').on('click', function () {
                @if(isset($canAcknowledgeCashReceipt) && !$canAcknowledgeCashReceipt)
                return;
                @endif

                const $btn = $('#bulkAcknowledge');
                if ($btn.is(':disabled')) {
                    return;
                }

                const originalHtml = $btn.html();

                const ids = Array.from(selectedIds);
                if (ids.length === 0) {
                    alert('Nincs kijelölt tétel.');
                    return;
                }

                const payloadValues = {};
                for (const id of ids) {
                    payloadValues[id] = payloadValues[id] || {};
                    if (editedValues[id]) {
                        if (editedValues[id].settled_amount !== undefined) payloadValues[id].settled_amount = editedValues[id].settled_amount;
                        if (editedValues[id].note !== undefined) payloadValues[id].note = editedValues[id].note;
                    }
                }

                $.ajax({
                    url: '{{ route('admin.cash-receipts.bulk-acknowledge') }}',
                    method: 'POST',
                    beforeSend: function () {
                        $btn.prop('disabled', true);
                        $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Nyugtázás...');
                    },
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: ids,
                        values: payloadValues,
                    },
                    success: function (resp) {
                        selectedIds.clear();
                        for (const k in editedValues) {
                            delete editedValues[k];
                        }
                        updateSelectedTotals();
                        table.ajax.reload(null, false);
                        if (resp && resp.message) {
                            if (typeof window.showToast === 'function') {
                                window.showToast(resp.message, 'success');
                            }
                        }
                    },
                    error: function (xhr) {
                        let msg = 'Hiba történt.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        if (typeof window.showToast === 'function') {
                            window.showToast(msg, 'danger');
                        }
                    },
                    complete: function () {
                        updateBulkButtonState();
                        $btn.html(originalHtml);
                    }
                });
            });
        });
    </script>
@endsection
