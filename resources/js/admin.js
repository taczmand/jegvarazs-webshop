import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
import './sb-admin-2.js';
import './base_crud.js';

if (bootstrap?.Modal?.Default) {
    bootstrap.Modal.Default.backdrop = 'static';
    bootstrap.Modal.Default.keyboard = false;
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.modal').forEach((el) => {
        const instance = bootstrap.Modal.getInstance(el);
        if (instance && instance._config) {
            instance._config.backdrop = 'static';
            instance._config.keyboard = false;
        }
    });
});

document.addEventListener('click', (e) => {
    const dismiss_btn = e.target.closest('[data-bs-dismiss="modal"]');
    if (!dismiss_btn) return;

    if (!dismiss_btn.classList.contains('btn-close')) {
        e.preventDefault();
        e.stopPropagation();
    }
});


window.showToast = function (message, type = 'success') {
    const toastEl = document.getElementById('globalToast');
    const toastBody = document.getElementById('globalToastMessage');

    // Típus alapján módosítjuk a háttér színt (Bootstrap 5)
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
    toastEl.classList.add(`bg-${type}`);

    toastBody.textContent = message;

    const delay = type === 'warning' ? 5000 : 3000;
    const toast = new bootstrap.Toast(toastEl, { delay });
    toast.show();
};

window.sendViewRequest = function (model, id) {
    fetch(`/admin/beallitasok/uj-adatok/megtekintes`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        credentials: 'same-origin', // vagy 'include' ha más domainek is érintettek
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

if (window.jQuery) {
    const applyEllipsisTooltips = (tableEl) => {
        if (!tableEl) return;

        const cells = tableEl.querySelectorAll('tbody td');
        cells.forEach((cell) => {
            const text = (cell.textContent || '').trim();
            if (!text) {
                cell.removeAttribute('title');
                return;
            }

            const isOverflowing = cell.scrollWidth > cell.clientWidth;
            if (isOverflowing) {
                cell.setAttribute('title', text);
            } else {
                cell.removeAttribute('title');
            }
        });
    };

    window.jQuery(document).on('draw.dt', function (e, settings) {
        const tableEl = settings && settings.nTable ? settings.nTable : null;
        applyEllipsisTooltips(tableEl);
    });

    window.jQuery(function () {
        document.querySelectorAll('table.dataTable').forEach(t => applyEllipsisTooltips(t));
    });
}
