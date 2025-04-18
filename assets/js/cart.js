// Base URL for API requests (adjust if project is in a subdirectory, e.g., '/project')
const BASE_URL = '';

document.addEventListener('DOMContentLoaded', () => {
    console.log('cart.js loaded');
    initCart();
});

// Initialize all cart functionalities
function initCart() {
    initQuantitySelectors();
    initRemoveButtons();
    initCheckoutButton();
    initPromoCode();
    initCheckboxes();
}

// Initialize quantity selectors (increase/decrease buttons and input)
function initQuantitySelectors() {
    const decreaseButtons = document.querySelectorAll('.quantity-btn.decrease');
    const increaseButtons = document.querySelectorAll('.quantity-btn.increase');
    const quantityInputs = document.querySelectorAll('.quantity-input');

    decreaseButtons.forEach(button => {
        button.addEventListener('click', () => {
            const cartItemId = button.getAttribute('data-cart-item-id');
            const input = document.querySelector(`.quantity-input[data-cart-item-id="${cartItemId}"]`);
            if (!input) {
                console.warn(`Input not found for cartItemId=${cartItemId}`);
                return;
            }

            const currentValue = parseInt(input.value, 10);
            if (currentValue > 1) {
                input.disabled = true;
                button.disabled = true;
                updateCartItem(cartItemId, currentValue - 1, input, button);
            }
        });
    });

    increaseButtons.forEach(button => {
        button.addEventListener('click', () => {
            const cartItemId = button.getAttribute('data-cart-item-id');
            const input = document.querySelector(`.quantity-input[data-cart-item-id="${cartItemId}"]`);
            if (!input) {
                console.warn(`Input not found for cartItemId=${cartItemId}`);
                return;
            }

            const currentValue = parseInt(input.value, 10);
            const max = parseInt(input.max, 10) || 100;
            if (currentValue < max) {
                input.disabled = true;
                button.disabled = true;
                updateCartItem(cartItemId, currentValue + 1, input, button);
            }
        });
    });

    quantityInputs.forEach(input => {
        input.addEventListener('change', () => {
            const cartItemId = input.getAttribute('data-cart-item-id');
            let value = parseInt(input.value, 10);
            const max = parseInt(input.max, 10) || 100;
            const min = parseInt(input.min, 10) || 1;

            if (isNaN(value) || value < min) {
                value = min;
                input.value = min;
            } else if (value > max) {
                value = max;
                input.value = max;
            }

            input.disabled = true;
            updateCartItem(cartItemId, value, input);
        });
    });
}

// Update cart item quantity via API
function updateCartItem(cartItemId, quantity, input, button = null) {
    const card = document.querySelector(`.cart-card[data-cart-item-id="${cartItemId}"]`);
    if (!card) {
        console.warn(`Card not found for cartItemId=${cartItemId}`);
        input.disabled = false;
        if (button) button.disabled = false;
        return;
    }

    const subtotalElement = card.querySelector('.cart-card-subtotal');
    const originalValue = parseInt(input.value, 10);

    console.log(`Updating cart item: cartItemId=${cartItemId}, quantity=${quantity}`);
    fetch(`${BASE_URL}/processes/update_cart.php`, {
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
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        input.disabled = false;
        if (button) button.disabled = false;

        if (data.success) {
            input.value = data.quantity;
            input.setAttribute('value', data.quantity);
            subtotalElement.textContent = data.subtotal;
            card.setAttribute('data-subtotal', data.subtotal.replace(/[^0-9]/g, ''));

            // Visual feedback
            subtotalElement.classList.add('subtotal-updated');
            setTimeout(() => subtotalElement.classList.remove('subtotal-updated'), 1000);

            updateCartSummary();
        } else {
            alert(data.message || 'Failed to update cart item.');
            input.value = originalValue;
        }
    })
    .catch(error => {
        console.error('Error updating cart item:', error);
        input.disabled = false;
        if (button) button.disabled = false;
        alert('System error. Please try again.');
        input.value = originalValue;
    });
}

// Initialize remove buttons
function initRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-btn');
    removeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const cartItemId = button.getAttribute('data-cart-item-id');
            if (!cartItemId) {
                console.warn('Missing cartItemId');
                return;
            }

            if (confirm('Are you sure you want to remove this item from the cart?')) {
                removeCartItem(cartItemId);
            }
        });
    });
}

// Remove cart item via API
function removeCartItem(cartItemId) {
    fetch(`${BASE_URL}/processes/remove_from_cart.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'cart_item_id': cartItemId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const card = document.querySelector(`.cart-card[data-cart-item-id="${cartItemId}"]`);
            if (card) card.remove();

            updateCartSummary();

            const cartCountElement = document.querySelector('#cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = data.count;
                cartCountElement.classList.add('update-animation');
                setTimeout(() => cartCountElement.classList.remove('update-animation'), 1000);
            }

            if (data.count === 0) {
                setTimeout(() => window.location.reload(), 500);
            }
        } else {
            alert(data.message || 'Failed to remove item.');
        }
    })
    .catch(error => {
        console.error('Error removing cart item:', error);
        alert('System error. Please try again.');
    });
}

// Initialize checkout button
function initCheckoutButton() {
    const checkoutButton = document.getElementById('checkout-btn');
    const checkoutForm = document.getElementById('checkout-form');
    const selectedItemsInput = document.getElementById('selected-items');

    if (checkoutButton && checkoutForm && selectedItemsInput) {
        checkoutButton.addEventListener('click', () => {
            const selectedCheckboxes = document.querySelectorAll('.cart-item-checkbox:checked');
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.getAttribute('data-cart-item-id'));
            selectedItemsInput.value = JSON.stringify(selectedIds);
            checkoutForm.submit();
        });
    }
}

// Initialize promo code input
function initPromoCode() {
    const applyButton = document.getElementById('apply-promo-btn');
    if (!applyButton) {
        console.warn('Apply promo button not found');
        return;
    }

    applyButton.addEventListener('click', () => {
        const promoCodeInput = document.getElementById('promo-code-input');
        if (!promoCodeInput) {
            console.warn('Promo code input not found');
            return;
        }

        const promoCode = promoCodeInput.value.trim();
        if (promoCode) {
            applyPromoCode(promoCode);
        } else {
            alert('Please enter a promo code');
        }
    });
}

// Apply promo code via API
function applyPromoCode(code) {
    fetch(`${BASE_URL}/processes/apply_promo.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'code': code
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
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
                    <div class="summary-label">Discount:</div>
                    <div class="summary-value" id="discount-amount">${data.discount}</div>
                `;
                summaryContainer.insertBefore(discountElement, document.querySelector('.summary-row.total'));
            } else if (discountRow && data.discount) {
                const discountAmount = document.getElementById('discount-amount');
                if (discountAmount) discountAmount.textContent = data.discount;
            }
        } else {
            alert(data.message || 'Invalid promo code.');
        }
    })
    .catch(error => {
        console.error('Error applying promo code:', error);
        alert('System error. Please try again.');
    });
}

// Initialize checkboxes for selecting cart items
function initCheckboxes() {
    const selectAllCheckbox = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.cart-item-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', () => {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateCartSummary();
        });
    }

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            if (!checkbox.checked && selectAllCheckbox.checked) {
                selectAllCheckbox.checked = false;
            }
            if (Array.from(itemCheckboxes).every(cb => cb.checked)) {
                selectAllCheckbox.checked = true;
            }
            updateCartSummary();
        });
    });

    updateCartSummary();
}

// Update cart summary (subtotal, total items, shipping, total)
function updateCartSummary() {
    const itemCheckboxes = document.querySelectorAll('.cart-item-checkbox:checked');
    let subtotal = 0;
    let totalItems = 0;

    itemCheckboxes.forEach(checkbox => {
        const cartItemId = checkbox.getAttribute('data-cart-item-id');
        const card = document.querySelector(`.cart-card[data-cart-item-id="${cartItemId}"]`);
        if (card) {
            const price = parseFloat(card.getAttribute('data-price'));
            const quantity = parseInt(card.querySelector('.quantity-input').value, 10);
            subtotal += price * quantity;
            totalItems += quantity;
        }
    });

    const shipping = subtotal >= 1000000 ? 0 : 30000;
    const totalWithShipping = subtotal + shipping;

    const subtotalElement = document.getElementById('subtotal');
    const totalItemsElement = document.getElementById('total-items');
    const shippingFeeElement = document.getElementById('shipping-fee');
    const totalAmountElement = document.getElementById('total-amount');
    const checkoutButton = document.getElementById('checkout-btn');

    if (subtotalElement) subtotalElement.textContent = formatCurrency(subtotal);
    if (totalItemsElement) totalItemsElement.textContent = totalItems;
    if (shippingFeeElement) shippingFeeElement.textContent = formatCurrency(shipping);
    if (totalAmountElement) totalAmountElement.textContent = formatCurrency(totalWithShipping);
    if (checkoutButton) checkoutButton.disabled = totalItems === 0;
}

// Format currency for display
function formatCurrency(amount) {
    return amount.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' }).replace('₫', '₫').trim();
}