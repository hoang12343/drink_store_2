document.addEventListener('DOMContentLoaded', function() {
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
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
            if (!input) return;
            
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
                updateCartItem(productId, value - 1);
            }
        });
    });

    increaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
            if (!input) return;
            
            let value = parseInt(input.value);
            let max = parseInt(input.max) || 100;
            if (value < max) {
                input.value = value + 1;
                updateCartItem(productId, value + 1);
            }
        });
    });

    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.getAttribute('data-product-id');
            let value = parseInt(this.value);
            let max = parseInt(this.max) || 100;
            let min = parseInt(this.min) || 1;

            if (isNaN(value) || value < min) {
                this.value = min;
                value = min;
            }
            if (value > max) {
                this.value = max;
                value = max;
            }

            updateCartItem(productId, value);
        });
    });
}

function initRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-btn');

    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            if (!productId) return;
            
            if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                removeCartItem(productId);
            }
        });
    });
}

function initCheckoutButton() {
    const checkoutButton = document.getElementById('checkout-btn');
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function() {
            window.location.href = 'index.php?page=checkout';
        });
    }
}

function initPromoCode() {
    const applyButton = document.getElementById('apply-promo-btn');
    if (!applyButton) return;
    
    applyButton.addEventListener('click', function() {
        const promoCodeInput = document.getElementById('promo-code-input');
        if (!promoCodeInput) return;
        
        const promoCode = promoCodeInput.value.trim();
        if (promoCode) {
            applyPromoCode(promoCode);
        }
    });
}

function updateCartItem(productId, quantity) {
    fetch('processes/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'product_id': productId,
            'quantity': quantity
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const subtotalElement = document.querySelector(`.cart-row[data-product-id="${productId}"] .product-subtotal-col`);
            if (subtotalElement) {
                subtotalElement.textContent = data.subtotal;
            }
            updateCartSummary(data);
        }
    })
    .catch(error => {
        console.error('Error updating cart:', error);
    });
}

function removeCartItem(productId) {
    fetch('processes/remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'product_id': productId
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`.cart-row[data-product-id="${productId}"]`);
            if (row) {
                row.remove();
            }

            updateCartSummary(data);

            const cartCountElement = document.querySelector('#cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = data.count;
                cartCountElement.classList.add('update-animation');
                setTimeout(() => {
                    cartCountElement.classList.remove('update-animation');
                }, 1000);
            }

            if (data.count === 0) {
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        }
    })
    .catch(error => {
        console.error('Error removing item:', error);
    });
}

function applyPromoCode(code) {
    fetch('processes/apply_promo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'code': code
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
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
        }
    })
    .catch(error => {
        console.error('Error applying promo code:', error);
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