async function addToCart(productId, quantity = 1) {

    try {
        const response = await fetch(baseURL() + 'kosar/hozzaadas', {
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
            fetchCartSummary();
        } else if (res.result === 'error') {
            showToast(res.result, 'error');
        }
    } catch (error) {
        console.error('Hiba:', error);
    }
}

async function removeItemFromCart(itemId) {
    try {
        const response = await fetch('/kosar/torles', {
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
        const response = await fetch('/kosar/mennyiseg-valtoztatas', {
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
            location.reload(); // Refresh the cart summary
        } else if (res.result === 'error') {
            showToast(res.error_message, 'error');
        }
    } catch (error) {
        showToast(error, 'error');
    }
}

window.addToCart = addToCart;
window.removeItemFromCart = removeItemFromCart;
window.changeQuantity = changeQuantity;
