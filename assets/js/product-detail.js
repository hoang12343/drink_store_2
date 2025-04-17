document.addEventListener('DOMContentLoaded', function() {
    // Initialize thumbnail gallery
    initThumbnailGallery();

    // Initialize quantity selector
    initQuantitySelector();

    // Initialize add to cart and buy now buttons
    initActionButtons();
});

/**
 * Initialize thumbnail gallery functionality
 */
function initThumbnailGallery() {
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.querySelector('.main-image img');

    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Remove active class from all thumbnails
            thumbnails.forEach(t => t.classList.remove('active'));
            // Add active class to clicked thumbnail
            this.classList.add('active');
            // Update main image
            mainImage.src = this.src;
        });
    });
}

/**
 * Initialize quantity selector functionality
 */
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

/**
 * Initialize add to cart and buy now buttons
 */
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
            // Add to favorites (mock functionality)
            showNotification('Sản phẩm đã được thêm vào danh sách yêu thích!', 'success');
        });
    }
}