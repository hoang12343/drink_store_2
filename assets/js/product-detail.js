document.addEventListener('DOMContentLoaded', function() {
    initThumbnailGallery();
    initQuantitySelector();
    initActionButtons();
});

function initThumbnailGallery() {
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.querySelector('.main-image img');
    if (!mainImage) return;

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
    
    if (!decreaseBtn || !increaseBtn || !quantityInput) return;
    
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
        }
    });

    quantityInput.addEventListener('change', function() {
        let value = parseInt(this.value);
        let max = parseInt(this.max);
        let min = parseInt(this.min);

        if (isNaN(value) || value < min) {
            this.value = min;
        }
        if (value > max) {
            this.value = max;
        }
    });
}

function initActionButtons() {
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    const buyNowBtn = document.querySelector('.buy-now-btn');
    const quantityInput = document.querySelector('.quantity-input');
    
    if (!quantityInput) return;
    
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
    const productPrice = document.querySelector('.current-price').textContent;
    
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
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Cập nhật số lượng sản phẩm trong giỏ hàng
            const cartCount = document.querySelector('#cartCount');
            if (cartCount) {
                cartCount.textContent = data.count;
                cartCount.classList.add('update-animation');
                setTimeout(() => {
                    cartCount.classList.remove('update-animation');
                }, 1000);
            }
            
            // Hiệu ứng cho icon giỏ hàng
            if (window.animateCartIcon) {
                window.animateCartIcon();
            }
            
            // Nếu là chức năng "Mua ngay", chuyển hướng tới trang giỏ hàng
            if (redirectToCart) {
                window.location.href = 'index.php?page=cart';
            }
        } else {
            // Nếu chưa đăng nhập, chuyển hướng tới trang đăng nhập
            if (data.message === 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng') {
                window.location.href = 'index.php?page=login&redirect=' + encodeURIComponent(window.location.href);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}