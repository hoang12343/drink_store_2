<?php
define('APP_START', true);
require_once 'includes/db_connect.php';
require_once 'includes/session_start.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login&redirect=order_confirmation');
    exit;
}

// Lấy thông tin đơn hàng
$order_id = isset($_GET['order_id']) ? filter_var($_GET['order_id'], FILTER_SANITIZE_NUMBER_INT) : null;
if (!$order_id) {
    header('Location: index.php?page=cart&error=' . urlencode('Đơn hàng không hợp lệ'));
    exit;
}

$stmt = $pdo->prepare("
    SELECT o.*, GROUP_CONCAT(oi.product_id) as product_ids
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: index.php?page=cart&error=' . urlencode('Đơn hàng không tồn tại'));
    exit;
}

// Lấy chi tiết sản phẩm
$stmt = $pdo->prepare("
    SELECT oi.product_id, oi.quantity, oi.price, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng - Cửa hàng đồ uống</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/header.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/usermenu.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/orders.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h1>Xác nhận đơn hàng</h1>
        <?php if (isset($_GET['success'])): ?>
            <div class="form-message success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="form-message error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <h2>Đơn hàng #<?= htmlspecialchars($order['id']) ?></h2>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Giá</th>
                    <th>Tổng</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($item['price'], 0, ',', '.') ?> VNĐ</td>
                        <td><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> VNĐ</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="order-summary">
            <?php if ($order['discount'] > 0): ?>
                <div class="summary-row">
                    <span>Giảm giá (<?= htmlspecialchars($order['promo_code'] ?? 'N/A') ?>):</span>
                    <span>-<?= number_format($order['discount'], 0, ',', '.') ?> VNĐ</span>
                </div>
            <?php endif; ?>
            <div class="summary-row">
                <span>Phí vận chuyển:</span>
                <span><?= number_format($order['shipping'], 0, ',', '.') ?> VNĐ</span>
            </div>
            <div class="summary-row">
                <span>Phương thức thanh toán:</span>
                <span><?= $order['payment_method'] === 'cod' ? 'Thanh toán khi nhận hàng' : 'ZaloPay' ?></span>
            </div>
            <div class="summary-row total">
                <span>Tổng tiền:</span>
                <span><?= number_format($order['total_amount'], 0, ',', '.') ?> VNĐ</span>
            </div>
            <div class="summary-row">
                <span>Trạng thái:</span>
                <span>
                    <?php
                    $status_labels = [
                        'pending' => 'Chờ xử lý',
                        'confirmed' => 'Đã xác nhận',
                        'completed' => 'Hoàn thành',
                        'failed' => 'Thất bại',
                        'cancelled' => 'Hủy',
                        'processing' => 'Đang xử lý',
                        'shipped' => 'Đã giao',
                        'delivered' => 'Đã nhận'
                    ];
                    echo htmlspecialchars($status_labels[$order['status']] ?? $order['status']);
                    ?>
                </span>
            </div>
        </div>

        <div class="actions">
            <?php if (in_array($order['status'], ['delivered', 'confirmed'])): ?>
                <form action="processes/confirm_order.php" method="POST" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                    <button type="submit" class="btn btn-confirm">Xác nhận</button>
                </form>
            <?php endif; ?>
            <a href="index.php?page=products" class="btn btn-primary">Tiếp tục mua sắm</a>
            <a href="index.php?page=orders" class="btn btn-secondary">Xem tất cả đơn hàng</a>
        </div>
    </div>
</body>

</html>