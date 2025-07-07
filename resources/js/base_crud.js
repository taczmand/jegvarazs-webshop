export function initCrud(options) {
    const {
        tableId,
        modalId,
        formId,
        addButtonId,
        dataUrl,
        storeUrl,
        updateUrl,
        destroyUrl,
        columns,
        model,
        order
    } = options;

    const modalDOM = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalDOM);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');


    const form = document.getElementById(formId);

    const table = $(`#${tableId}`).DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/hu.json'
        },
        processing: true,
        serverSide: true,
        ajax: dataUrl,
        columns: columns,
        order: order || [[0, 'desc']],
    });

    document.querySelectorAll('.filter-input').forEach(function (input) {
        input.addEventListener('change', handleFilter);
        input.addEventListener('keyup', handleFilter);
    });

    function handleFilter(event) {
        const columnIndex = event.target.getAttribute('data-column');
        const value = event.target.value;

        if (table) {
            table.column(columnIndex).search(value).draw();
        }
    }

    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            table.ajax.reload();
        });
    });

    const addButton = document.getElementById(addButtonId);
    if (addButton) {
        addButton.addEventListener('click', () => {
            form.reset();
            form.querySelector('[name="id"]').value = '';
            modal.show();
        });
    }

    $(`#${tableId}`).on('click', '.edit', function () {
        const row = table.row($(this).closest('tr')).data();

        for (const key in row) {
            const input = form.querySelector(`[name="${key}"]`);

            if (!input) continue;

            if (input.type === 'checkbox') {
                const value = row[key];
                input.checked = value === 'active' || value === 1 || value === '1' || value === true || value === 'true' || value === 'Aktív';
            } else {
                input.value = row[key];
            }
        }

        const id = row.id;

        if (model && id) {
            fetch(`${window.appConfig.APP_URL}admin/beallitasok/uj-adatok/megtekintes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    model,
                    id
                }),
            })
                .then(res => {
                    if (!res.ok) throw new Error('Megtekintés mentése sikertelen');
                    return res.json();
                })
                .catch(err => {
                    console.error('Hiba a megtekintés mentésében:', err);
                });
        }

        modal.show();
    });


    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const id = form.querySelector('[name="id"]').value;
        const isEdit = id !== '';
        const url = isEdit ? `${storeUrl}/${id}` : storeUrl;

        const formData = new FormData(form);
        if (isEdit) formData.append('_method', 'PUT');
        formData.append('_token', csrfToken);

        const $submitBtn = $(form).find('.save-btn');
        const originalHtml = $submitBtn.html();
        $submitBtn.html('Mentés...').prop('disabled', true);

        $.ajax({
            url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (res) => {
                showToast(res.message || 'Sikeres!', 'success');
                table.ajax.reload();
                modal.hide();
            },
            error: (xhr) => {
                let msg = 'Hiba!';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                } else if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                }
                showToast(msg, 'danger');
            },
            complete: () => {
                $submitBtn.html(originalHtml).prop('disabled', false);
            }
        });
    });

    $(`#${tableId}`).on('click', '.delete', function () {
        if (!confirm('Biztosan törlöd?')) return;
        const id = $(this).data('id');
        $.ajax({
            url: `${destroyUrl}/${id}`,
            type: 'DELETE',
            data: { _token: csrfToken },
            success: (res) =>{
                showToast(res.message || 'Sikeres!', 'success');
                table.ajax.reload();
                modal.hide();
            },
            error: (xhr) => {
                let msg = 'Hiba!';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                } else if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                }
                showToast(msg, 'danger');
            },
        });
    });
}

window.initCrud = initCrud;
