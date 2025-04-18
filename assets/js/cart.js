document.addEventListener('DOMContentLoaded', () => {
    console.log('cart.js loaded');
    initCart();
});

function initCart() {
    initQuantitySelectors();
    initRemoveButtons();
    initCheckoutButton();
    initPromoCode();
}

function initQuantitySelectors() {
    const decreaseButtons = document.querySelectorAll('.quantity-btn.decrease');
    const increaseButtons = document.querySelectorAll('.quantity-btn.increase');
    const quantityInputs = document.querySelectorAll('.quantity-input');

    decreaseButtons.forEach(button => {
        button.addEventListener('click', () => {
            const cartItemId = button.getAttribute('data-cart-item-id');
            const input = document.querySelector(`.quantity-input[data-cart-item-id="${cartItemId}"]`);
            if (!input) return console.warn(`Input not found for cartItemId=${cartItemId}`);
            
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
                updateCartItem(cartItemId, value - 1);
            }
        });
    });

    increaseButtons.forEach(button => {
        button.addEventListener('click', () => {
            const cartItemId = button.getAttribute('data-cart-item-id');
            const input = document.querySelector(`.quantity-input[data-cart-item-id="${cartItemId}"]`);
            if (!input) return console.warn(`Input not found for cartItemId=${cartItemId}`);
            
            let value = parseInt(input.value);
            let max = parseInt(input.max) || 100;
            if (value < max) {
                input.value = value + 1;
                updateCartItem(cartItemId, value + 1);
            }
        });
    });

    quantityInputs.forEach(input => {
        input.addEventListener('change', () => {
            const cartItemId = input.getAttribute('data-cart-item-id');
            let value = parseInt(input.value);
            let max = parseInt(input.max) || 100;
            let min = parseInt(input.min) || 1;

            if (isNaN(value) || value < min) {
                input.value = min;
                value = min;
            }
            if (value > max) {
                input.value = max;
                value = max;
            }

            updateCartItem(cartItemId, value);
        });
    });
}

function initRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-btn');

    removeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const cartItemId = button.getAttribute('data-cart-item-id');
            if (!cartItemId) return console.warn('Missing cartItemId');

            if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                removeCartItem(cartItemId);
            }
        });
    });
}

function initCheckoutButton() {
    const checkoutButton = document.getElementById('checkout-btn');
    if (checkoutButton) {
        checkoutButton.addEventListener('click', () => {
            window.location.href = 'index.php?page=checkout';
        });
    }
}

function initPromoCode() {
    const applyButton = document.getElementById('apply-promo-btn');
    if (!applyButton) return console.warn('Apply promo button not found');

    applyButton.addEventListener('click', () => {
        const promoCodeInput = document.getElementById('promo-code-input');
        if (!promoCodeInput) return console.warn('Promo code input not found');

        const promoCode = promoCodeInput.value.trim();
        if (promoCode) {
            applyPromoCode(promoCode);
        } else {
            alert('Vui lòng nhập mã giảm giá');
        }
    });
}

function updateCartItem(cartItemId, quantity) {
    fetch('/processes/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'cart_item_id': cartItemId,
            'quantity': quantity
        })
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const subtotalElement = document.querySelector(`.cart-row[data-cart-item-id="${cartItemId}"] .product-subtotal-col`);
            if (subtotalElement) {
                subtotalElement.textContent = data.subtotal;
            }
            updateCartSummary(data);
        } else {
            console.error('Update cart failed:', data.message);
            alert(data.message || 'Lỗi khi cập nhật giỏ hàng.');
        }
    })
    .catch(error => {
        console.error('Error updating cart:', error);
        alert('Lỗi hệ thống. Vui lòng thử lại.');
    });
}

function removeCartItem(cartItemId) {
    fetch('/processes/remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'cart_item_id': cartItemId
        })
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`.cart-row[data-cart-item-id="${cartItemId}"]`);
            if (row) {
                row.remove();
            }

            updateCartSummary(data);

            const cartCountElement = document.querySelector('#cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = data.count;
                cartCountElement.classList.add('update-animation');
                setTimeout(() => cartCountElement.classList.remove('update-animation'), 1000);
            }

            if (data.count === 0) {
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        } else {
            console.error('Remove cart item failed:', data.message);
            alert(data.message || 'Lỗi khi xóa sản phẩm.');
        }
    })
    .catch(error => {
        console.error('Error removing item:', error);
        alert('Lỗi hệ thống. Vui lòng thử lại.');
    });
}

function applyPromoCode(code) {
    fetch('/processes/apply_promo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'code': code
        })
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const subtotalElement = document.getElementById('subtotal');
            const totalElement = document.getElementById('total-amount');
            if (subtotalElement) subtotalElement.textContent = data.subtotal;
            if (totalElement) totalElement.textContent = data.total;

            const discountRow = document.querySelector('.discount-row');
            if (!discountRow && data.discount) {
                const summaryContainer = document.querySelector('.summary-row.total').parentNode;
                const discountElement = document.createElement('div');
                discountElement.className = 'summary-row discount-row';
                discountElement.innerHTML = `
                    <div class="summary-label">Giảm giá:</div>
                    <div class="summary-value" id="discount-amount">${data.discount}</div>
                `;
                summaryContainer.insertBefore(discountElement, document.querySelector('.summary-row.total'));
            } else if (discountRow && data.discount) {
                const discountAmount = document.getElementById('discount-amount');
                if (discountAmount) discountAmount.textContent = data.discount;
            }
        } else {
            console.error('Apply promo failed:', data.message);
            alert(data.message || 'Mã giảm giá không hợp lệ.');
        }
    })
    .catch(error => {
        console.error('Error applying promo code:', error);
        alert('Lỗi hệ thống. Vui lòng thử lại.');
    });
}

function updateCartSummary(data) {
    const subtotalElement = document.getElementById('subtotal');
    const totalItemsElement = document.getElementById('total-items');
    const shippingFeeElement = document.getElementById('shipping-fee');
    const totalAmountElement = document.getElementById('total-amount');

    if (subtotalElement) subtotalElement.textContent = data.total;
    if (totalItemsElement) totalItemsElement.textContent = data.count;
    if (shippingFeeElement) shippingFeeElement.textContent = data.shipping;
    if (totalAmountElement) totalAmountElement.textContent = data.total_with_shipping;
}