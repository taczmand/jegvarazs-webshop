async function addToCart(productId, quantity = 1) {

    try {
        const response = await fetch(window.appConfig.APP_URL + 'kosar/hozzaadas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        });

        const res = await response.json();
        if (res.result === 'success') {
            showToast('A termék kosárba került!', 'success');

            if (typeof fbq === 'function') {
                fbq('track', 'AddToCart', {
                    content_ids: [String(productId)],
                    content_type: 'product',
                    quantity: Number(quantity) || 1,
                    currency: 'HUF',
                });
            }

            fetchCartSummary();
        } else if (res.result === 'error') {
            showToast(res.message, 'error');
        }
    } catch (error) {
        console.error('Hiba:', error);
    }
}
async function goToProductPage(productId) {

}

async function removeItemFromCart(itemId) {
    try {
        const response = await fetch(window.appConfig.APP_URL + 'kosar/torles', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ item_id: itemId })
        });

        const res = await response.json();
        if (res.result === 'success') {
            showToast('A termék eltávolításra került a kosárból!', 'success');

            if (typeof fbq === 'function') {
                fbq('track', 'RemoveFromCart', {
                    content_type: 'product',
                    currency: 'HUF',
                });
            }

            location.reload(); // Refresh the cart summary
        } else if (res.result === 'error') {
            showToast(res.result, 'error');
        }
    } catch (error) {
        showToast(error, 'error');
    }
}

async function changeQuantity(itemId, quantity) {
    try {
        const response = await fetch(window.appConfig.APP_URL + 'kosar/mennyiseg-valtoztatas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ item_id: itemId, quantity: quantity })
        });

        const res = await response.json();
        if (res.result === 'success') {
            showToast('A mennyiség frissítve!', 'success');

            if (typeof fbq === 'function') {
                fbq('trackCustom', 'UpdateCart', {
                    currency: 'HUF',
                });
            }

            return 'success';
        } else if (res.result === 'error') {
            showToast(res.message, 'error');
        }
    } catch (error) {

        showToast(error, 'error');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.quanity_input').forEach(function (input) {
        let previousValue = input.value;

        input.addEventListener('focus', function () {
            previousValue = this.value;
        });

        input.addEventListener('keydown', async function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const itemId = this.getAttribute('item-id');
                const value = this.value;

                if (value === previousValue) {
                    return; // nincs változás
                }

                const result = await changeQuantity(itemId, value);
                if (!result) {
                    this.value = previousValue;
                } else {
                    previousValue = value;
                    location.reload();
                }
            }
        });

        input.addEventListener('blur', async function () {
            const itemId = this.getAttribute('item-id');
            const value = this.value;

            if (value === previousValue) {
                return; // nincs változás
            }

            const result = await changeQuantity(itemId, value);
            if (!result) {
                this.value = previousValue;
            } else {
                previousValue = value;
                location.reload();
            }
        });
    });
});





window.addToCart = addToCart;
window.removeItemFromCart = removeItemFromCart;
window.changeQuantity = changeQuantity;
