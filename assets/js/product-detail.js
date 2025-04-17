document.addEventListener('DOMContentLoaded', function() {
    initThumbnailGallery();
    initQuantitySelector();
    initActionButtons();
});

function initThumbnailGallery() {
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.querySelector('.main-image img');

    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            mainImage.src = this.src;
        });
    });
}

function initQuantitySelector() {
    const decreaseBtn = document.querySelector('.quantity-btn.decrease');
    const increaseBtn = document.querySelector('.quantity-btn.increase');
    const quantityInput = document.querySelector('.quantity-input');

    decreaseBtn.addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        if (value > 1) {
            quantityInput.value = value - 1;
        }
    });

    increaseBtn.addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        let max = parseInt(quantityInput.max);
        if (value < max) {
            quantityInput.value = value + 1;
        }
    });

    quantityInput.addEventListener('change', function() {
        let value = parseInt(this.value);
        let max = parseInt(this.max);
        let min = parseInt(this.min);

        if (value < min) this.value = min;
        if (value > max) this.value = max;
    });
}

function initActionButtons() {
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    const buyNowBtn = document.querySelector('.buy-now-btn');
    const quantityInput = document.querySelector('.quantity-input');

    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productCode = this.getAttribute('data-product-code');
            const quantity = parseInt(quantityInput.value);
            addToCart(productCode, quantity);
        });
    }

    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function() {
            showNotification('Sản phẩm đã được thêm vào danh sách yêu thích!', 'success');
        });
    }
}

// Hàm addToCart được định nghĩa trong products.js, nên không cần lặp lại
function showNotification(message, type = 'info') {
    let notificationContainer = document.querySelector('.notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.className = 'notification-container';
        document.body.appendChild(notificationContainer);

        const style = document.createElement('style');
        style.textContent = `
            .notification-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
            }
            .notification {
                background-color: white;
                color: #333;
                border-radius: 4px;
                box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
                padding: 15px 20px;
                margin-bottom: 10px;
                transform: translateX(120%);
                transition: transform 0.4s ease;
                display: flex;
                align-items: center;
                min-width: 300px;
            }
            .notification.show {
                transform: translateX(0);
            }
            .notification.success {
                border-left: 4px solid #4CAF50;
            }
            .notification.error {
                border-left: 4px solid #F44336;
            }
            .notification.info {
                border-left: 4px solid #2196F3;
            }
            .notification-icon {
                margin-right: 15px;
                font-size: 20px;
            }
            .notification.success .notification-icon {
                color: #4CAF50;
            }
            .notification.error .notification-icon {
                color: #F44336;
            }
            .notification.info .notification-icon {
                color: #2196F3;
            }
            .notification-message {
                flex: 1;
            }
            .notification-close {
                margin-left: 15px;
                cursor: pointer;
                color: #aaa;
                font-size: 16px;
            }
            .notification-close:hover {
                color: #333;
            }
        `;
        document.head.appendChild(style);
    }

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    let iconClass = '';
    switch (type) {
        case 'success':
            iconClass = 'fas fa-check-circle';
            break;
        case 'error':
            iconClass = 'fas fa-exclamation-circle';
            break;
        default:
            iconClass = 'fas fa-info-circle';
    }
    
    notification.innerHTML = `
        <div class="notification-icon">
            <i class="${iconClass}"></i>
        </div>
        <div class="notification-message">${message}</div>
        <div class="notification-close">&times;</div>
    `;
    
    notificationContainer.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', function() {
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

function initActionButtons() {
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    const buyNowBtn = document.querySelector('.buy-now-btn');
    const quantityInput = document.querySelector('.quantity-input');

    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productCode = this.getAttribute('data-product-code');
            const quantity = parseInt(quantityInput.value);
            addToCart(productCode, quantity);
        });
    }

    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function() {
            const productCode = this.getAttribute('data-product-code');
            const quantity = parseInt(quantityInput.value);
            
            // Add to cart first
            addToCart(productCode, quantity);
            
            // Then redirect to checkout
            setTimeout(() => {
                window.location.href = 'index.php?page=cart';
            }, 500);
        });
    }
}