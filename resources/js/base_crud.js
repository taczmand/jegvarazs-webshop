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
        columns
    } = options;

    const modalDOM = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalDOM);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');


    const form = document.getElementById(formId);

    const table = $(`#${tableId}`).DataTable({
        processing: true,
        serverSide: true,
        ajax: dataUrl,
        columns: columns
    });

    if (addButtonId) {
        document.getElementById(addButtonId).addEventListener('click', () => {
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
                input.checked = value === 'active' || value === 1 || value === '1' || value === true || value === 'true';
            } else {
                input.value = row[key];
            }
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
