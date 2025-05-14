<?php
require_once '../includes/db_connect.php';
require_once '../includes/session_start.php';

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = 'completed' 
            WHERE id = ? AND user_id = ? AND status IN ('confirmed', 'delivered')
        ");
        $stmt->execute([$order_id, $user_id]);

        if ($stmt->rowCount() > 0) {
            header('Location: ../index.php?page=order_confirmation&order_id=' . $order_id . '&success=' . urlencode('Đơn hàng đã được xác nhận.'));
        } else {
            header('Location: ../index.php?page=order_confirmation&order_id=' . $order_id . '&error=' . urlencode('Không thể xác nhận đơn hàng. Đơn hàng không tồn tại hoặc trạng thái không hợp lệ.'));
        }
    } catch (PDOException $e) {
        error_log('Error confirming order: ' . $e->getMessage());
        header('Location: ../index.php?page=order_confirmation&order_id=' . $order_id . '&error=' . urlencode('Lỗi hệ thống. Vui lòng thử lại.'));
    }
} else {
    header('Location: ../index.php?page=orders');
}
exit;
