document.addEventListener('DOMContentLoaded', function() {
    // Add filter toggle button for mobile
    const productsHeader = document.querySelector('.products-header');
    const leftBar = document.querySelector('.left-bar');
    
    if (productsHeader && leftBar) {
        const filterToggle = document.createElement('button');
        filterToggle.classList.add('filter-toggle');
        filterToggle.textContent = 'Lọc sản phẩm';
        productsHeader.prepend(filterToggle);

        filterToggle.addEventListener('click', function() {
            leftBar.classList.toggle('active');
        });
    }

    // Buy Now functionality (same as add-to-cart)
    const buyNowButtons = document.querySelectorAll('.buy-now-btn');
    buyNowButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productCard = this.closest('.product-card');
            const productCode = productCard.querySelector('.buy-now-btn').getAttribute('data-product-code');

            fetch('processes/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_code=' + encodeURIComponent(productCode) + '&quantity=1'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('cartCount').textContent = data.cart_count;
                        alert('Sản phẩm đã được thêm vào giỏ hàng!');
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng.');
                });
        });
    });
});