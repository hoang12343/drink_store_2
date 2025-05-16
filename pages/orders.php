<?php
if (!defined('APP_START')) exit('No direct access');
require_once ROOT_PATH . '/includes/db_connect.php';
require_once ROOT_PATH . '/includes/session_start.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in'])) {
    header('Location: index.php?page=login&redirect=orders');
    exit;
}

// Lấy danh sách đơn hàng
try {
    $stmt = $pdo->prepare("
        SELECT o.id, o.total_amount, o.status, o.created_at, o.zalopay_trans_id, o.payment_method
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching orders: " . $e->getMessage());
    $orders = [];
}

// Kiểm tra trạng thái đơn hàng cụ thể nếu có tham số check_trans_id
if (isset($_GET['check_trans_id'])) {
    $check_trans_id = filter_input(INPUT_GET, 'check_trans_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    foreach ($orders as $order) {
        if ($order['status'] === 'pending' && $order['zalopay_trans_id'] === $check_trans_id) {
            echo "<script>window.location.href='processes/check_zalopay_status.php?trans_id=" . urlencode($order['zalopay_trans_id']) . "';</script>";
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa hàng đồ uống - Đơn hàng</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/usermenu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/orders.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h1>Đơn hàng của tôi</h1>

        <?php if (empty($orders)): ?>
            <p>Bạn chưa có đơn hàng nào.</p>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tổng tiền</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th>Chi tiết</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                            <td><?= number_format($order['total_amount'], 0, ',', '.') ?> VNĐ</td>
                            <td><?= $order['payment_method'] === 'cod' ? 'COD' : 'ZaloPay' ?></td>
                            <td>
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
                            </td>
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td>
                            <td><a href="index.php?page=order_confirmation&order_id=<?= urlencode($order['id']) ?>"
                                    class="details-link">Xem chi tiết</a></td>
                            <td>
                                <?php if (in_array($order['status'], ['delivered', 'confirmed'])): ?>
                                    <form action="processes/confirm_order.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                        <button type="submit" class="btn btn-confirm">Xác nhận</button>
                                    </form>
                                <?php endif; ?>

                                <?php if (in_array($order['status'], ['pending', 'confirmed', 'processing'])): ?>
                                    <form action="processes/cancel_order.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                        <button type="submit" class="btn btn-cancel">Hủy bỏ</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Modal xác nhận đơn hàng -->
        <div class="confirm-modal" id="confirm-order-modal">
            <div class="modal-content">
                <h3>Xác nhận đơn hàng</h3>
                <p>Bạn có chắc muốn xác nhận đơn hàng này?</p>
                <div class="modal-buttons">
                    <button class="confirm-btn" id="confirm-order-btn">Xác nhận</button>
                    <button class="cancel-btn" id="cancel-confirm-btn">Hủy</button>
                </div>
            </div>
        </div>

        <!-- Modal hủy đơn hàng -->
        <div class="cancel-modal" id="cancel-order-modal">
            <div class="modal-content">
                <h3>Hủy đơn hàng</h3>
                <p>Bạn có chắc muốn hủy đơn hàng này?</p>
                <div class="modal-buttons">
                    <button class="confirm-btn" id="confirm-cancel-btn">Xác nhận</button>
                    <button class="cancel-btn" id="cancel-cancel-btn">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Truyền thông báo từ PHP sang JavaScript -->
    <?php if (isset($_GET['success']) || isset($_GET['error'])): ?>
        <script>
            window.orderMessage = {
                message: <?= json_encode(isset($_GET['success']) ? $_GET['success'] : $_GET['error']) ?>,
                type: <?= json_encode(isset($_GET['success']) ? 'success' : 'error') ?>
            };
        </script>
    <?php endif; ?>
    <script src="assets/js/orders.js?v=<?= time() ?>"></script>
</body>

</html>