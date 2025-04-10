document.addEventListener('DOMContentLoaded', function() {
    // Add filter toggle button for mobile
    const productsHeader = document.querySelector('.products-header');
    const leftBar = document.querySelector('.left-bar');
    
    const filterToggle = document.createElement('button');
    filterToggle.classList.add('filter-toggle');
    filterToggle.textContent = 'Lọc sản phẩm';
    productsHeader.prepend(filterToggle);

    filterToggle.addEventListener('click', function() {
        leftBar.classList.toggle('active');
    });

    // Add-to-cart functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productCode = this.getAttribute('data-product-code');

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