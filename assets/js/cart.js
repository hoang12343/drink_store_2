document.addEventListener('DOMContentLoaded', function() {
    // Initialize all cart functionalities
    initCart();
});

// Main initialization function
function initCart() {
    initQuantitySelectors();
    initRemoveButtons();
    initCheckoutButton();
    initPromoCode();
}

// Initialize quantity selectors for increasing/decreasing product quantities
function initQuantitySelectors() {
    const decreaseButtons = document.querySelectorAll('.quantity-btn.decrease');
    const increaseButtons = document.querySelectorAll('.quantity-btn.increase');
    const quantityInputs = document.querySelectorAll('.quantity-input');

    // Handle decrease button clicks
    decreaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            const input = document.querySelector(`.quantity-input[data-index="${index}"]`);
            if (!input) {
                console.error(`Quantity input with data-index="${index}" not found`);
                return;
            }
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
                updateCartItem(index, value - 1);
            }
        });
    });

    // Handle increase button clicks
    increaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            const input = document.querySelector(`.quantity-input[data-index="${index}"]`);
            if (!input) {
                console.error(`Quantity input with data-index="${index}" not found`);
                return;
            }
            let value = parseInt(input.value);
            let max = parseInt(input.max) || 100; // Default max if not specified
            if (value < max) {
                input.value = value + 1;
                updateCartItem(index, value + 1);
            } else {
                showNotification(`Số lượng tối đa là ${max}`, 'info');
            }
        });
    });

    // Handle manual input changes
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const index = this.getAttribute('data-index');
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

            updateCartItem(index, value);
        });
    });
}

// Initialize remove buttons for deleting products from the cart
function initRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-btn');

    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            if (!index) {
                console.error('Remove button missing data-index attribute');
                return;
            }
            if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                removeCartItem(index);
            }
        });
    });
}

// Initialize checkout button to redirect to checkout page
function initCheckoutButton() {
    const checkoutButton = document.getElementById('checkout-btn');

    if (checkoutButton) {
        checkoutButton.addEventListener('click', function() {
            showNotification('Đang chuyển hướng đến trang thanh toán...', 'info');
            setTimeout(() => {
                window.location.href = 'index.php?page=checkout';
            }, 800); // Short delay to show notification
        });
    } else {
        console.warn('Checkout button (#checkout-btn) not found');
    }
}

// Initialize promo code application
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

// Update cart item quantity
function updateCartItem(index, quantity) {
    showNotification('Đang cập nhật giỏ hàng...', 'info');

    fetch('processes/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'index': index,
            'quantity': quantity
        })
    })
    .then(response => {
        if (!response.ok) {
            console.error('Lỗi mạng:', response.status, response.statusText);
            throw new Error('Phản hồi mạng không thành công');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update subtotal for the specific item
            const subtotalElement = document.querySelector(`.cart-row[data-index="${index}"] .product-subtotal`);
            if (subtotalElement) {
                subtotalElement.textContent = data.subtotal;
            } else {
                console.warn(`Subtotal element for data-index="${index}" not found`);
            }

            // Update cart summary
            updateCartSummary(data);

            showNotification('Giỏ hàng đã được cập nhật', 'success');
        } else {
            console.error('Lỗi từ server:', data.message);
            showNotification(data.message || 'Có lỗi khi cập nhật giỏ hàng', 'error');
        }
    })
    .catch(error => {
        console.error('Lỗi khi cập nhật giỏ hàng:', error);
        showNotification('Lỗi kết nối server. Vui lòng thử lại.', 'error');
    });
}

// Remove item from cart
function removeCartItem(index) {
    showNotification('Đang xóa sản phẩm...', 'info');

    fetch('processes/remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'index': index
        })
    })
    .then(response => {
        if (!response.ok) {
            console.error('Lỗi mạng:', response.status, response.statusText);
            throw new Error('Phản hồi mạng không thành công');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Remove the cart row from the DOM
            const row = document.querySelector(`.cart-row[data-index="${index}"]`);
            if (row) {
                row.remove();
                console.log(`Đã xóa dòng sản phẩm với data-index="${index}"`);
            } else {
                console.warn(`Không tìm thấy dòng sản phẩm với data-index="${index}"`);
            }

            // Update cart summary
            updateCartSummary(data);

            // Update cart count in header
            const cartCountElement = document.querySelector('#cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = data.count;
                cartCountElement.classList.add('update-animation');
                setTimeout(() => {
                    cartCountElement.classList.remove('update-animation');
                }, 1000);
            }

            showNotification('Sản phẩm đã được xóa khỏi giỏ hàng', 'success');

            // Reload page if cart is empty
            if (data.count === 0) {
                setTimeout(() => {
                    window.location.reload();
                }, 1500); // Delay to show success notification
            }
        } else {
            console.error('Lỗi từ server:', data.message);
            showNotification(data.message || 'Có lỗi khi xóa sản phẩm', 'error');
        }
    })
    .catch(error => {
        console.error('Lỗi khi xóa sản phẩm:', error);
        showNotification('Lỗi kết nối server. Vui lòng thử lại.', 'error');
    });
}

// Apply promo code
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
            console.error('Lỗi mạng:', response.status, response.statusText);
            throw new Error('Phản hồi mạng không thành công');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update subtotal and total
            const subtotalElement = document.getElementById('subtotal');
            const totalElement = document.getElementById('total-amount');
            if (subtotalElement) subtotalElement.textContent = data.subtotal;
            if (totalElement) totalElement.textContent = data.total;

            // Add or update discount row
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
            console.error('Lỗi từ server:', data.message);
            showNotification(data.message || 'Mã giảm giá không hợp lệ', 'error');
        }
    })
    .catch(error => {
        console.error('Lỗi khi áp dụng mã giảm giá:', error);
        showNotification('Lỗi kết nối server. Vui lòng thử lại.', 'error');
    });
}

// Update cart summary (subtotal, shipping, total, items count)
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

// Display notification
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

    // Show notification with animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    // Handle close button
    const closeButton = notification.querySelector('.notification-close');
    closeButton.addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 400);
    });

    // Auto-close after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 400);
        }
    }, 5000);
}