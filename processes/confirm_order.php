<?php
define('APP_START', true);
require_once '../includes/db_connect.php';
require_once '../includes/session_start.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login&redirect=orders');
    exit;
}

// Kiểm tra dữ liệu đầu vào
$order_id = isset($_POST['order_id']) ? filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT) : null;
if (!$order_id) {
    header('Location: ../index.php?page=orders&error=' . urlencode('Đơn hàng không hợp lệ'));
    exit;
}

try {
    // Kiểm tra đơn hàng thuộc về người dùng và ở trạng thái phù hợp
    $stmt = $pdo->prepare("
        SELECT id, status
        FROM orders
        WHERE id = ? AND user_id = ? AND status IN ('delivered', 'confirmed')
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header('Location: ../index.php?page=orders&error=' . urlencode('Đơn hàng không tồn tại hoặc không thể xác nhận'));
        exit;
    }

    // Cập nhật trạng thái đơn hàng thành 'completed'
    $stmt = $pdo->prepare("
        UPDATE orders
        SET status = 'completed',
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$order_id]);

    // Chuyển hướng về trang đơn hàng với thông báo thành công
    header('Location: ../index.php?page=orders&success=' . urlencode('Đơn hàng đã được xác nhận thành công'));
    exit;
} catch (PDOException $e) {
    error_log("Error confirming order: " . $e->getMessage());
    header('Location: ../index.php?page=orders&error=' . urlencode('Lỗi hệ thống. Vui lòng thử lại.'));
    exit;
}
