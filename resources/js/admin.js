import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
import './sb-admin-2.js';
import './base_crud.js';


window.showToast = function (message, type = 'success') {
    const toastEl = document.getElementById('globalToast');
    const toastBody = document.getElementById('globalToastMessage');

    // Típus alapján módosítjuk a háttér színt (Bootstrap 5)
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
    toastEl.classList.add(`bg-${type}`);

    toastBody.textContent = message;

    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
};

window.sendViewRequest = function (model, id) {
    fetch(`${window.appConfig.APP_URL}admin/beallitasok/uj-adatok/megtekintes`, {
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
