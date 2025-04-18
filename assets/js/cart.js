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
            if (!input) {
                console.error(`Quantity input with data-product-id="${productId}" not found`);
                return;
            }
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
            if (!input) {
                console.error(`Quantity input with data-product-id="${productId}" not found`);
                return;
            }
            let value = parseInt(input.value);
            let max = parseInt(input.max) || 100;
            if (value < max) {
                input.value = value + 1;
                updateCartItem(productId, value + 1);
            } else {
                showNotification(`Số lượng tối đa là ${max}`, 'info');
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
                showNotification('Số lượng không thể nhỏ hơn 1', 'info');
                value = min;
            }
            if (value > max) {
                this.value = max;
                showNotification(`Số lượng tối đa là ${max}`, 'info');
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
            if (!productId) {
                console.error('Remove button missing data-product-id attribute');
                return;
            }
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
            showNotification('Đang chuyển hướng đến trang thanh toán...', 'info');
            setTimeout(() => {
                window.location.href = 'index.php?page=checkout';
            }, 800);
        });
    } else {
        console.warn('Checkout button (#checkout-btn) not found');
    }
}

function initPromoCode() {
    const applyButton = document.getElementById('apply-promo-btn');

    if (applyButton) {
        applyButton.addEventListener('click', function() {
            const promoCodeInput = document.getElementById('promo-code-input');
            if (!promoCodeInput) {
                console.error('Promo code input (#promo-code-input) not found');
                return;
            }
            const promoCode = promoCodeInput.value.trim();
            if (promoCode) {
                applyPromoCode(promoCode);
            } else {
                showNotification('Vui lòng nhập mã giảm giá', 'info');
            }
        });
    } else {
        console.warn('Apply promo button (#apply-promo-btn) not found');
    }
}

function updateCartItem(productId, quantity) {
    showNotification('Đang cập nhật giỏ hàng...', 'info');

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
        if (!response.ok) {
            console.error('Network error:', response.status, response.statusText);
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const subtotalElement = document.querySelector(`.cart-row[data-product-id="${productId}"] .product-subtotal`);
            if (subtotalElement) {
                subtotalElement.textContent = data.subtotal;
            } else {
                console.warn(`Subtotal element for data-product-id="${productId}" not found`);
            }

            updateCartSummary(data);
            showNotification('Giỏ hàng đã được cập nhật', 'success');
        } else {
            console.error('Server error:', data.message);
            showNotification(data.message || 'Có lỗi khi cập nhật giỏ hàng', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating cart:', error);
        showNotification('Lỗi kết nối server. Vui lòng thử lại.', 'error');
    });
}

function removeCartItem(productId) {
    showNotification('Đang xóa sản phẩm...', 'info');

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
        if (!response.ok) {
            console.error('Network error:', response.status, response.statusText);
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`.cart-row[data-product-id="${productId}"]`);
            if (row) {
                row.remove();
                console.log(`Removed cart row with data-product-id="${productId}"`);
            } else {
                console.warn(`Cart row with data-product-id="${productId}" not found`);
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

            showNotification('Sản phẩm đã được xóa khỏi giỏ hàng', 'success');

            if (data.count === 0) {
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } else {
            console.error('Server error:', data.message);
            showNotification(data.message || 'Có lỗi khi xóa sản phẩm', 'error');
        }
    })
    .catch(error => {
        console.error('Error removing item:', error);
        showNotification('Lỗi kết nối server. Vui lòng thử lại.', 'error');
    });
}

function applyPromoCode(code) {
    showNotification('Đang áp dụng mã giảm giá...', 'info');

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
        if (!response.ok) {
            console.error('Network error:', response.status, response.statusText);
            throw new Error('Network response was not ok');
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
                    <div class="summary-label">Giảm giá:</div>
                    <div class="summary-value" id="discount-amount">${data.discount}</div>
                `;
                summaryContainer.insertBefore(discountElement, document.querySelector('.summary-row.total'));
            } else if (discountRow && data.discount) {
                const discountAmount = document.getElementById('discount-amount');
                if (discountAmount) discountAmount.textContent = data.discount;
            }

            showNotification('Áp dụng mã giảm giá thành công', 'success');
        } else {
            console.error('Server error:', data.message);
            showNotification(data.message || 'Mã giảm giá không hợp lệ', 'error');
        }
    })
    .catch(error => {
        console.error('Error applying promo code:', error);
        showNotification('Lỗi kết nối server. Vui lòng thử lại.', 'error');
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

function showNotification(message, type = 'info') {
    let notificationContainer = document.querySelector('.notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.className = 'notification-container';
        document.body.appendChild(notificationContainer);
    }

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;

    const iconClass = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        info: 'fas fa-info-circle'
    }[type] || 'fas fa-info-circle';

    notification.innerHTML = `
        <div class="notification-icon">
            <i class="${iconClass}"></i>
        </div>
        <div class="notification-message">${message}</div>
        <div class="notification-close">×</div>
    `;

    notificationContainer.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    const closeButton = notification.querySelector('.notification-close');
    closeButton.addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 400);
    });

    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 400);
        }
    }, 5000);
}