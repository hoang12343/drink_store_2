<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

// Đảm bảo user đã đăng nhập (kiểm tra đã được thực hiện ở index.php)
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // Hiển thị thông báo giỏ hàng trống
    echo '<div class="empty-cart">
            <i class="fas fa-shopping-cart fa-4x"></i>
            <h2>Giỏ hàng của bạn đang trống</h2>
            <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
            <a href="index.php?page=products" class="continue-shopping-btn">Tiếp tục mua sắm</a>
          </div>';
    return;
}
?>

<link rel="stylesheet" href="assets/css/cart.css">
<script src="assets/js/cart.js" defer></script>

<section class="cart-page">
    <h1 class="page-title">Giỏ hàng của bạn</h1>

    <div class="cart-container">
        <div class="cart-header">
            <div class="cart-row">
                <div class="cart-col product-info-col">Sản phẩm</div>
                <div class="cart-col product-price-col">Đơn giá</div>
                <div class="cart-col product-quantity-col">Số lượng</div>
                <div class="cart-col product-subtotal-col">Thành tiền</div>
                <div class="cart-col product-action-col"></div>
            </div>
        </div>

        <div class="cart-body">
            <?php
            $total = 0; // Tổng giá trị đơn hàng
            foreach ($_SESSION['cart'] as $index => $item):
                $subtotal = $item['price_numeric'] * $item['quantity'];
                $total += $subtotal;
            ?>
                <div class="cart-row" data-product-id="<?= htmlspecialchars($item['product_id']) ?>">
                    <div class="cart-col product-info-col">
                        <div class="product-info">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($item['image'] ?? 'assets/images/products/default.jpg') ?>"
                                    alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <div class="product-details">
                                <h3 class="product-name"><?= htmlspecialchars($item['name']) ?></h3>
                                <p class="product-code">Mã: <?= htmlspecialchars($item['product_id']) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="cart-col product-price-col">
                        <span class="product-price"><?= htmlspecialchars($item['price']) ?></span>
                    </div>
                    <div class="cart-col product-quantity-col">
                        <div class="quantity-selector">
                            <button class="quantity-btn decrease" data-index="<?= $index ?>">-</button>
                            <input type="number" value="<?= $item['quantity'] ?>" min="1" max="99" class="quantity-input"
                                data-index="<?= $index ?>">
                            <button class="quantity-btn increase" data-index="<?= $index ?>">+</button>
                        </div>
                    </div>
                    <div class="cart-col product-subtotal-col">
                        <span class="product-subtotal"><?= number_format($subtotal, 0, ',', '.') ?> ₫</span>
                    </div>
                    <div class="cart-col product-action-col">
                        <button class="remove-btn" data-index="<?= $index ?>">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-footer">
            <div class="cart-summary">
                <div class="summary-row">
                    <div class="summary-label">Tổng sản phẩm:</div>
                    <div class="summary-value" id="total-items"><?= count($_SESSION['cart']) ?></div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">Tạm tính:</div>
                    <div class="summary-value" id="subtotal"><?= number_format($total, 0, ',', '.') ?> ₫</div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">Phí vận chuyển:</div>
                    <div class="summary-value" id="shipping-fee">
                        <?php
                        $shipping_fee = ($total < 1000000) ? 30000 : 0;
                        echo number_format($shipping_fee, 0, ',', '.') . ' ₫';
                        ?>
                    </div>
                </div>
                <div class="summary-row total">
                    <div class="summary-label">Tổng cộng:</div>
                    <div class="summary-value" id="total-amount">
                        <?= number_format($total + $shipping_fee, 0, ',', '.') ?> ₫</div>
                </div>

                <!-- Mã giảm giá -->
                <div class="promo-code">
                    <input type="text" placeholder="Nhập mã giảm giá" id="promo-code-input">
                    <button id="apply-promo-btn">Áp dụng</button>
                </div>

                <div class="checkout-actions">
                    <a href="index.php?page=products" class="continue-shopping-btn">Tiếp tục mua sắm</a>
                    <button id="checkout-btn" class="checkout-btn">Thanh toán</button>
                </div>
            </div>
        </div>
    </div>
</section>