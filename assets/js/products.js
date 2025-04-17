document.addEventListener('DOMContentLoaded', function() {
    // Add filter toggle button for mobile
    initFilterToggle();
    
    // Initialize buy now buttons
    initBuyNowButtons();
    
    // Initialize product action handlers for product detail navigation
    initProductDetailNavigation();
});

/**
 * Initialize filter toggle for mobile view
 */
function initFilterToggle() {
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
}

/**
 * Initialize buy now buttons functionality
 */
function initBuyNowButtons() {
    const buyNowButtons = document.querySelectorAll('.buy-now-btn');
    
    buyNowButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productCard = this.closest('.product-card');
            const productCode = this.getAttribute('data-product-code');
            
            if (!productCode) {
                console.error('Product code not found');
                return;
            }

            addToCart(productCode, 1);
        });
    });
}

/**
 * Add product to cart via AJAX
 * 
 * @param {string} productCode The product code to add to cart
 * @param {number} quantity The quantity to add
 */
function addToCart(productCode, quantity) {
    fetch('processes/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_code=' + encodeURIComponent(productCode) + '&quantity=' + encodeURIComponent(quantity)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cartCountElement = document.getElementById('cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = data.cart_count;
            }
            showNotification('Sản phẩm đã được thêm vào giỏ hàng!', 'success');
        } else {
            showNotification(data.message || 'Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng.', 'error');
    });
}

/**
 * Initialize all product elements to navigate to product detail
 */
function initProductDetailNavigation() {
    // Product cards
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        // Get product ID from the data attribute
        const productId = card.getAttribute('data-product-id');
        if (!productId) return;
        
        // Product image click
        const productImage = card.querySelector('.product-image');
        if (productImage) {
            makeClickableForDetail(productImage, productId);
        }
        
        // Product title click
        const productTitle = card.querySelector('.product-title');
        if (productTitle) {
            makeClickableForDetail(productTitle, productId);
        }
        
        // Quick view button click
        const quickViewBtn = card.querySelector('.quick-view-btn');
        if (quickViewBtn) {
            makeClickableForDetail(quickViewBtn, productId);
        }
        
        // Add to cart action (prevent navigation when clicked)
        const addToCartBtn = card.querySelector('.add-to-cart-btn');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const productCode = this.getAttribute('data-product-code') || card.getAttribute('data-product-code');
                if (productCode) {
                    addToCart(productCode, 1);
                }
            });
        }
    });
    
    // Product action buttons (for any layout)
    const productActions = document.querySelectorAll('.product-action');
    productActions.forEach(action => {
        if (action.classList.contains('add-to-cart-btn') || action.classList.contains('buy-now-btn')) {
            // Skip cart buttons as they're handled separately
            return;
        }
        
        action.addEventListener('click', function(e) {
            // Find product ID from closest container
            const container = this.closest('[data-product-id]');
            if (container) {
                const productId = container.getAttribute('data-product-id');
                if (productId) {
                    e.preventDefault();
                    navigateToProductDetail(productId);
                }
            }
        });
    });
    
    // Make entire product card clickable if it has a specific class
    const clickableCards = document.querySelectorAll('.product-card.clickable');
    clickableCards.forEach(card => {
        const productId = card.getAttribute('data-product-id');
        if (productId) {
            card.addEventListener('click', function(e) {
                // Don't navigate if clicking on buttons or links
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || 
                    e.target.closest('button') || e.target.closest('a')) {
                    return;
                }
                
                navigateToProductDetail(productId);
            });
            
            // Add cursor pointer style
            card.style.cursor = 'pointer';
        }
    });
}

/**
 * Make an element clickable to navigate to product detail
 * 
 * @param {HTMLElement} element The element to make clickable
 * @param {string} productId The product ID to navigate to
 */
function makeClickableForDetail(element, productId) {
    element.style.cursor = 'pointer';
    element.addEventListener('click', function(e) {
        e.preventDefault();
        navigateToProductDetail(productId);
    });
}

/**
 * Navigate to product detail page
 * 
 * @param {string} productId The product ID to navigate to
 */
function navigateToProductDetail(productId) {
    window.location.href = 'index.php?page=product-detail&id=' + encodeURIComponent(productId);
}

/**
 * Show notification
 * 
 * @param {string} message The message to display
 * @param {string} type The notification type (success, error, info)
 */
function showNotification(message, type = 'info') {
    // Check if notification container exists, if not create it
    let notificationContainer = document.querySelector('.notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.className = 'notification-container';
        document.body.appendChild(notificationContainer);
        
        // Create notification container styles
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
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    // Icon based on type
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
    
    // Show notification with a slight delay for transition
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Close notification when clicking on X
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', function() {
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