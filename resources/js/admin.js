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
