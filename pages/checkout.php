<?php
if (!defined('APP_START')) exit('No direct access');
require_once 'includes/db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in'])) {
    header('Location: ../index.php?page=login&redirect=checkout');
    exit;
}

// Lấy danh sách sản phẩm được chọn từ giỏ hàng
$selected_items = [];
$total_amount = 0;
$discount = 0;
$shipping = 0;
$subtotal = 0;

if (isset($_POST['selected_items'])) {
    $selected_item_ids = json_decode($_POST['selected_items'], true);
    if (!is_array($selected_item_ids) || empty($selected_item_ids)) {
        header('Location: ../index.php?page=cart&error=' . urlencode('Danh sách sản phẩm không hợp lệ'));
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($selected_item_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT ci.id, ci.product_id, ci.quantity, p.price, p.name
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ? AND ci.id IN ($placeholders)
    ");
    $stmt->execute(array_merge([$_SESSION['user_id']], $selected_item_ids));
    $selected_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($selected_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    // Áp dụng mã giảm giá từ session
    if (isset($_SESSION['promo_code']) && abs($subtotal - $_SESSION['promo_code']['subtotal']) < 0.01) {
        $discount = floatval($_SESSION['promo_code']['discount']);
        $shipping = floatval($_SESSION['promo_code']['shipping']);
        $total_amount = floatval($_SESSION['promo_code']['total']);
    } else {
        // Xóa session promo_code nếu không hợp lệ
        unset($_SESSION['promo_code']);
        $shipping = $subtotal >= 1000000 ? 0 : 30000;
        $total_amount = $subtotal - $discount + $shipping;
    }

    error_log("checkout.php - Subtotal: $subtotal, Discount: $discount, Shipping: $shipping, Total: $total_amount");
}

// Nếu không có sản phẩm hoặc tổng tiền không hợp lệ, chuyển về giỏ hàng
if (empty($selected_items) || $subtotal <= 0) {
    header('Location: ../index.php?page=cart&error=' . urlencode('Vui lòng chọn sản phẩm để thanh toán'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <link rel="stylesheet" href="assets/css/checkout.css?v=<?= time() ?>">
</head>

<body>
    <div class="checkout-page">
        <div class="container">
            <h2>Thanh toán đơn hàng</h2>
            <?php if (isset($_GET['error'])): ?>
            <div class="form-message error"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <h3>Sản phẩm thanh toán</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                        <th>Tổng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($selected_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($item['price'], 0, ',', '.') ?> VNĐ</td>
                        <td><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> VNĐ</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($discount > 0): ?>
            <div class="total-row">
                <span>Giảm giá (<?= htmlspecialchars($_SESSION['promo_code']['code']) ?>):</span>
                <span>-<?= number_format($discount, 0, ',', '.') ?> VNĐ</span>
            </div>
            <?php endif; ?>
            <div class="total-row">
                <span>Phí vận chuyển:</span>
                <span><?= number_format($shipping, 0, ',', '.') ?> VNĐ</span>
            </div>
            <div class="total-row final">
                <span>Tổng tiền:</span>
                <span><?= number_format($total_amount, 0, ',', '.') ?> VNĐ</span>
            </div>

            <form action="../processes/payment_handler.php" method="POST" id="checkout-form">
                <input type="hidden" name="selected_items"
                    value='<?= htmlspecialchars(json_encode(array_column($selected_items, 'id'))) ?>'>
                <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
                <input type="hidden" name="discount" value="<?= $discount ?>">
                <input type="hidden" name="shipping" value="<?= $shipping ?>">
                <input type="hidden" name="payment_method" id="payment_method" value="zalopay">
                <?php if (isset($_SESSION['promo_code'])): ?>
                <input type="hidden" name="promo_code" value="<?= htmlspecialchars($_SESSION['promo_code']['code']) ?>">
                <?php endif; ?>

                <div class="payment-methods">
                    <h3>Phương thức thanh toán</h3>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="zalopay" checked
                            onchange="document.getElementById('payment_method').value = this.value;">
                        Thanh toán qua ZaloPay
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cod"
                            onchange="document.getElementById('payment_method').value = this.value;">
                        Thanh toán khi nhận hàng (COD)
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Xác nhận thanh toán</button>
            </form>
        </div>
    </div>

    <script src="assets/js/common.js?v=<?= time() ?>"></script>
    <script src="assets/js/checkout.js?v=<?= time() ?>"></script>
    <script>
    // Đảm bảo chỉ một phương thức thanh toán được chọn
    document.querySelectorAll('input[name="payment_method"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            document.getElementById('payment_method').value = radio.value;
            console.log('Payment method selected:', radio.value);
        });
    });
    </script>
</body>

</html>