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
            <div class="cart-header-content">
                <input type="checkbox" id="select-all" title="Chọn tất cả">
                <span>Chọn tất cả (<?= count($cart_items) ?> sản phẩm)</span>
            </div>
        </div>
        <div class="cart-items">
            <?php foreach ($cart_items as $item):
                    $formatted_price = number_format($item['price'], 0, ',', '.') . ' ₫';
                    $subtotal = $item['price'] * $item['quantity'];
                    $formatted_subtotal = number_format($subtotal, 0, ',', '.') . ' ₫';
                    $image = $item['image'] ? htmlspecialchars($item['image']) : 'assets/images/placeholder.jpg';
                ?>
            <div class="cart-card" data-cart-item-id="<?= htmlspecialchars($item['id']) ?>"
                data-price="<?= $item['price'] ?>" data-subtotal="<?= $subtotal ?>">
                <div class="cart-card-checkbox">
                    <input type="checkbox" class="cart-item-checkbox"
                        data-cart-item-id="<?= htmlspecialchars($item['id']) ?>">
                </div>
                <div class="cart-card-content">
                    <div class="cart-card-image">
                        <img src="<?= $image ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    </div>
                    <div class="cart-card-details">
                        <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="product-code">Mã: <?= htmlspecialchars($item['product_id']) ?></div>
                    </div>
                    <div class="cart-card-price" data-label="Giá"><?= $formatted_price ?></div>
                    <div class="cart-card-quantity" data-label="Số lượng">
                        <div class="quantity-selector">
                            <button class="quantity-btn decrease"
                                data-cart-item-id="<?= htmlspecialchars($item['id']) ?>">-</button>
                            <input type="number" class="quantity-input"
                                data-cart-item-id="<?= htmlspecialchars($item['id']) ?>"
                                value="<?= $item['quantity'] ?>" min="1" max="100">
                            <button class="quantity-btn increase"
                                data-cart-item-id="<?= htmlspecialchars($item['id']) ?>">+</button>
                        </div>
                    </div>
                    <div class="cart-card-subtotal" data-label="Tổng"><?= $formatted_subtotal ?></div>
                    <div class="cart-card-action">
                        <button class="remove-btn" data-cart-item-id="<?= htmlspecialchars($item['id']) ?>"><i
                                class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="cart-footer">
            <div class="cart-summary">
                <div class="summary-row">
                    <div class="summary-label">Tổng tiền hàng:</div>
                    <div class="summary-value" id="subtotal">0 ₫</div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">Số lượng sản phẩm:</div>
                    <div class="summary-value" id="total-items">0</div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">Phí vận chuyển:</div>
                    <div class="summary-value" id="shipping-fee">0 ₫</div>
                </div>
                <div class="summary-row total">
                    <div class="summary-label">Tổng cộng:</div>
                    <div class="summary-value" id="total-amount">0 ₫</div>
                </div>
                <div class="promo-code">
                    <input type="text" id="promo-code-input" placeholder="Nhập mã giảm giá">
                    <button id="apply-promo-btn">Áp dụng</button>
                </div>
                <form id="checkout-form" action="index.php?page=checkout" method="post">
                    <input type="hidden" name="selected_items" id="selected-items">
                    <div class="checkout-actions">
                        <a href="index.php?page=products" class="continue-shopping-btn">Tiếp tục mua sắm</a>
                        <button type="submit" class="checkout-btn" id="checkout-btn" disabled>Thanh toán</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="assets/js/cart.js?v=<?= time() ?>" defer></script>
<?php
}
?>