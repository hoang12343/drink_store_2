<?php
if (!defined('APP_START')) exit('No direct access');
require_once 'includes/db_connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login&redirect=' . urlencode('cart'));
    exit;
}

try {
    // Lấy giỏ hàng
    $stmt = $pdo->prepare("
        SELECT ci.id, ci.product_id, ci.quantity, p.name, p.price, p.image
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();
    error_log("Cart items fetched for user_id={$_SESSION['user_id']}: " . count($cart_items) . " items");

    // Lấy danh sách mã giảm giá
    $stmt = $pdo->prepare("
        SELECT * FROM promo_codes
        WHERE is_active = 1 AND start_date <= NOW() AND end_date >= NOW()
    ");
    $stmt->execute();
    $promo_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching cart or promo codes: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    $cart_items = [];
    $promo_codes = [];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng</title>
    <link rel="stylesheet" href="css/promo_codes.css?v=<?= time() ?>">
</head>

<body>
    <?php if (empty($cart_items)) { ?>
    <div class="empty-cart">
        <i class="fas fa-shopping-cart fa-3x"></i>
        <h2>Giỏ hàng của bạn đang trống</h2>
        <p>Thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
        <a href="index.php?page=products" class="continue-shopping-btn">Tiếp tục mua sắm</a>
    </div>
    <?php } else { ?>
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
                        <button id="open-promo-popup" class="open-promo-btn">Chọn hoặc nhập mã giảm giá</button>
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
        <!-- Promo Code Popup -->
        <div id="promo-popup" class="modal">
            <div class="modal-content">
                <span class="close-promo-popup">×</span>
                <h2>Chọn hoặc nhập mã giảm giá</h2>
                <div class="promo-input">
                    <input type="text" id="promo-code-input" placeholder="Nhập mã giảm giá">
                </div>
                <?php if (count($promo_codes) > 0) { ?>
                <h3>Mã giảm giá khả dụng</h3>
                <div class="promo-list">
                    <?php foreach ($promo_codes as $promo) { ?>
                    <div class="promo-item">
                        <input type="radio" name="promo_code" class="promo-radio"
                            value="<?php echo htmlspecialchars($promo['code']); ?>">
                        <div class="promo-details">
                            <label><strong><?php echo htmlspecialchars($promo['code']); ?></strong> (Giảm
                                <?php echo number_format($promo['discount_percentage'], 2); ?>%)</label>
                            <p>Giảm tối đa:
                                <?php echo $promo['max_discount_value'] ? number_format($promo['max_discount_value'], 2) : 'Không giới hạn'; ?>
                            </p>
                            <p>Ngày bắt đầu: <?php echo date('d/m/Y H:i', strtotime($promo['start_date'])); ?></p>
                            <p>Ngày kết thúc: <?php echo date('d/m/Y H:i', strtotime($promo['end_date'])); ?></p>
                            <p>Giá trị đơn tối thiểu: <?php echo number_format($promo['min_order_value'], 2); ?></p>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <button id="apply-promo-btn" class="apply-promo-btn">Áp dụng</button>
                <?php } else { ?>
                <p>Chưa có mã giảm giá khả dụng.</p>
                <button id="apply-promo-btn" class="apply-promo-btn">Áp dụng</button>
                <?php } ?>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/cart.js?v=<?= time() ?>" defer></script>
</body>

</html>
<?php
}
?>