<?php
if (!defined('APP_START')) exit('No direct access');
require_once 'includes/db_connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login&redirect=' . urlencode('cart'));
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT ci.id, ci.product_id, ci.quantity, p.name, p.price, p.image
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();
    error_log("Cart items fetched for user_id={$_SESSION['user_id']}: " . count($cart_items) . " items");
} catch (PDOException $e) {
    error_log("Error fetching cart: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    $cart_items = [];
}

if (empty($cart_items)) {
    echo '<div class="empty-cart">
        <i class="fas fa-shopping-cart fa-3x"></i>
        <h2>Giỏ hàng của bạn đang trống</h2>
        <p>Thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
        <a href="index.php?page=products" class="continue-shopping-btn">Tiếp tục mua sắm</a>
    </div>';
} else {
?>
    <div class="cart-page">
        <h1 class="page-title">Giỏ hàng</h1>
        <div class="cart-container">
            <div class="cart-header">
                <div class="cart-row">
                    <div class="cart-col product-info-col">Sản phẩm</div>
                    <div class="cart-col product-price-col">Giá</div>
                    <div class="cart-col product-quantity-col">Số lượng</div>
                    <div class="cart-col product-subtotal-col">Tổng</div>
                    <div class="cart-col product-action-col"></div>
                </div>
            </div>
            <?php foreach ($cart_items as $item):
                $formatted_price = number_format($item['price'], 0, ',', '.') . ' ₫';
                $subtotal = $item['price'] * $item['quantity'];
                $formatted_subtotal = number_format($subtotal, 0, ',', '.') . ' ₫';
                $image = $item['image'] ? htmlspecialchars($item['image']) : 'assets/images/placeholder.jpg';
            ?>
                <div class="cart-row" data-cart-item-id="<?= htmlspecialchars($item['id']) ?>">
                    <div class="cart-col product-info-col">
                        <div class="product-info">
                            <div class="product-image">
                                <img src="<?= $image ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <div class="product-details">
                                <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="product-code">Mã: <?= htmlspecialchars($item['product_id']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="cart-col product-price-col" data-label="Giá"><?= $formatted_price ?></div>
                    <div class="cart-col product-quantity-col" data-label="Số lượng">
                        <div class="quantity-selector">
                            <button class="quantity-btn decrease"
                                data-cart-item-id="<?= htmlspecialchars($item['id']) ?>">-</button>
                            <input type="number" class="quantity-input" data-cart-item-id="<?= htmlspecialchars($item['id']) ?>"
                                value="<?= $item['quantity'] ?>" min="1" max="100">
                            <button class="quantity-btn increase"
                                data-cart-item-id="<?= htmlspecialchars($item['id']) ?>">+</button>
                        </div>
                    </div>
                    <div class="cart-col product-subtotal-col" data-label="Tổng"><?= $formatted_subtotal ?></div>
                    <div class="cart-col product-action-col">
                        <button class="remove-btn" data-cart-item-id="<?= htmlspecialchars($item['id']) ?>"><i
                                class="fas fa-trash"></i></button>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="cart-footer">
                <div class="cart-summary">
                    <?php
                    $total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart_items));
                    $count = array_sum(array_map(fn($item) => $item['quantity'], $cart_items));
                    $shipping = $total >= 1000000 ? 0 : 30000;
                    $total_with_shipping = $total + $shipping;
                    ?>
                    <div class="summary-row">
                        <div class="summary-label">Tổng tiền hàng:</div>
                        <div class="summary-value" id="subtotal"><?= number_format($total, 0, ',', '.') . ' ₫' ?></div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Số lượng sản phẩm:</div>
                        <div class="summary-value" id="total-items"><?= $count ?></div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Phí vận chuyển:</div>
                        <div class="summary-value" id="shipping-fee"><?= number_format($shipping, 0, ',', '.') . ' ₫' ?>
                        </div>
                    </div>
                    <div class="summary-row total">
                        <div class="summary-label">Tổng cộng:</div>
                        <div class="summary-value" id="total-amount">
                            <?= number_format($total_with_shipping, 0, ',', '.') . ' ₫' ?></div>
                    </div>
                    <div class="promo-code">
                        <input type="text" id="promo-code-input" placeholder="Nhập mã giảm giá">
                        <button id="apply-promo-btn">Áp dụng</button>
                    </div>
                    <div class="checkout-actions">
                        <a href="index.php?page=products" class="continue-shopping-btn">Tiếp tục mua sắm</a>
                        <button class="checkout-btn" id="checkout-btn">Thanh toán</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/cart.js" defer></script>
<?php
}
