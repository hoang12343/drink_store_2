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
        SELECT o.id, o.total_amount, o.status, o.created_at, o.zalopay_trans_id
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

    <div class="container">
        <h1>Đơn hàng của tôi</h1>

        <?php if (isset($_GET['success'])): ?>
        <div class="form-message success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php elseif (isset($_GET['error'])): ?>
        <div class="form-message error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
        <p>Bạn chưa có đơn hàng nào.</p>
        <?php else: ?>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']) ?></td>
                    <td><?= number_format($order['total_amount'], 0, ',', '.') ?> VNĐ</td>
                    <td>
                        <?php
                                $status_labels = [
                                    'pending' => 'Chờ xử lý',
                                    'completed' => 'Hoàn thành',
                                    'failed' => 'Thất bại',
                                    'cancelled' => 'Hủy'
                                ];
                                echo htmlspecialchars($status_labels[$order['status']] ?? $order['status']);
                                ?>
                    </td>
                    <td><?= htmlspecialchars($order['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</body>

</html>