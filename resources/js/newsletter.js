window.addEventListener('click', async function (event) {
    const subscriptionBtn = event.target.closest('[data-subscribe-button]');

    if (!subscriptionBtn) return;

    event.preventDefault();

    const emailInput = document.getElementById('subscription_email');

    if (!emailInput || !emailInput.value.trim()) {
        showToast('Kérjük, adja meg az e-mail címét!', 'error');
        return;
    }

    try {
        const response = await fetch(window.appConfig.APP_URL + 'newsletter/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                email: emailInput.value.trim()
            })
        });

        const res = await response.json();

        if (res.result === 'success') {
            showToast(res.message, 'success');
            emailInput.value = '';
        } else {
            showToast(res.error_message || 'Ismeretlen hiba történt.', 'error');
        }

    } catch (error) {
        console.error('Hiba:', error);
        showToast('Hálózati hiba történt.', 'error');
    }
});
