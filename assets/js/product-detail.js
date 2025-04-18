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
    const maxStock = parseInt(quantityInput.getAttribute('max') || 10);

    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productCode = this.getAttribute('data-product-code');
            const quantity = parseInt(quantityInput.value);
            
            // Hiển thị xác nhận nếu số lượng lớn
            if (quantity > 5) {
                if (confirm(`Bạn có chắc muốn thêm ${quantity} sản phẩm vào giỏ hàng?`)) {
                    addToCart(productCode, quantity);
                }
            } else {
                addToCart(productCode, quantity);
            }
        });
    }

    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function() {
            const productCode = this.getAttribute('data-product-code');
            const quantity = parseInt(quantityInput.value);
            addToCart(productCode, quantity, true); // Gọi với redirect = true
        });
    }
}

function addToCart(productCode, quantity, redirectToCart = false) {
    const productName = document.querySelector('.product-name').textContent;
    // Lấy giá từ phần tử chứa giá hiện tại, không phải giá cũ
    const productPrice = document.querySelector('.current-price').textContent;
    
    // Hiển thị thông báo loading
    showNotification('Đang thêm sản phẩm vào giỏ hàng...', 'info');

    fetch('processes/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'product_id': productCode,
            'name': productName,
            'price': productPrice,
            'quantity': quantity
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Cập nhật số lượng sản phẩm trong giỏ hàng
            const cartCount = document.querySelector('#cartCount');
            if (cartCount) {
                cartCount.textContent = data.count;
                // Thêm hiệu ứng nhấp nháy cho cartCount
                cartCount.classList.add('update-animation');
                setTimeout(() => {
                    cartCount.classList.remove('update-animation');
                }, 1000);
            }
            
            // Hiệu ứng cho icon giỏ hàng
            if (window.animateCartIcon) {
                window.animateCartIcon();
            }
            
            // Hiển thị thông báo thành công
            showNotification(data.message, 'success');
            
            // Nếu là chức năng "Mua ngay", chuyển hướng tới trang giỏ hàng
            if (redirectToCart) {
                setTimeout(() => {
                    window.location.href = 'index.php?page=cart';
                }, 800);
            }
        } else {
            // Nếu chưa đăng nhập, chuyển hướng tới trang đăng nhập
            if (data.message === 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng') {
                window.location.href = 'index.php?page=login&redirect=' + encodeURIComponent(window.location.href);
            } else {
                showNotification(data.message || 'Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi kết nối server', 'error');
    });
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
        <div class="notification-close">×</div>
    `;
    
    notificationContainer.appendChild(notification);
    
    // Đảm bảo trình duyệt nhận biết thay đổi trước khi thêm class show
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
    
    // Tự động đóng thông báo sau 5 giây
    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 400);
        }
    }, 5000);
}

function initQuantitySelector() {
    const decreaseBtn = document.querySelector('.quantity-btn.decrease');
    const increaseBtn = document.querySelector('.quantity-btn.increase');
    const quantityInput = document.querySelector('.quantity-input');
    const maxStock = parseInt(quantityInput.getAttribute('max') || 10);

    decreaseBtn.addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        if (value > 1) {
            quantityInput.value = value - 1;
        }
    });

    increaseBtn.addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        if (value < maxStock) {
            quantityInput.value = value + 1;
        } else {
            showNotification(`Số lượng tối đa là ${maxStock}`, 'info');
        }
    });

    quantityInput.addEventListener('change', function() {
        let value = parseInt(this.value);
        let max = parseInt(this.max);
        let min = parseInt(this.min);

        if (value < min) {
            this.value = min;
            showNotification('Số lượng không thể nhỏ hơn 1', 'info');
        }
        if (value > max) {
            this.value = max;
            showNotification(`Số lượng tối đa là ${max}`, 'info');
        }
    });
}