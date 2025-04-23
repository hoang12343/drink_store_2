<?php
if (!defined('APP_START')) exit('No direct access');
require_once '../includes/db_connect.php';
require_once '../includes/config.php';

// Lấy dữ liệu từ ZaloPay
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Kiểm tra chữ ký
$mac = hash_hmac('sha256', $data['data'], ZALOPAY_CONFIG['key2']);
if ($mac !== $data['mac']) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// Xử lý callback
$result = json_decode($data['data'], true);
$order_id = json_decode($result['embed_data'], true)['order_id'];

try {
    $pdo->beginTransaction();

    // Cập nhật trạng thái đơn hàng
    $status = $result['status'] == 1 ? 'completed' : 'failed';
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND zalopay_trans_id = ?");
    $stmt->execute([$status, $order_id, $result['app_trans_id']]);

    // Giảm số lượng tồn kho nếu thanh toán thành công
    if ($status == 'completed') {
        $stmt = $pdo->prepare("
            SELECT oi.product_id, oi.quantity
            FROM order_items oi
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();

        foreach ($items as $item) {
            $stmt = $pdo->prepare("
                UPDATE products
                SET stock = stock - ?
                WHERE id = ?
            ");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
    }

    $pdo->commit();
    echo json_encode(['status' => 'success']);
    error_log('ZaloPay callback received: ' . json_encode($data));
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Callback error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
