<?php
define('APP_START', true);  // Thêm dòng này nếu chưa có
require_once '../includes/db_connect.php';
require_once '../includes/config.php';

// Thêm header vào tất cả các yêu cầu HTTP
header('ngrok-skip-browser-warning: true');

// Log toàn bộ request để debug
error_log('ZaloPay callback received: ' . file_get_contents('php://input'));
error_log('ZaloPay callback headers: ' . json_encode(getallheaders()));

// Lấy dữ liệu từ ZaloPay
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    error_log('ZaloPay callback error: Invalid request data');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Kiểm tra chữ ký
$mac = hash_hmac('sha256', $data['data'], ZALOPAY_CONFIG['key2']);
if ($mac !== $data['mac']) {
    error_log('ZaloPay callback error: Invalid signature. Received: ' . $data['mac'] . ', Calculated: ' . $mac);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// Xử lý callback
$result = json_decode($data['data'], true);
$order_id = json_decode($result['embed_data'], true)['order_id'];
$trans_id = $result['app_trans_id'];
$status = $result['status'] == 1 ? 'completed' : 'failed';

error_log("ZaloPay callback received: Order ID=$order_id, Trans ID=$trans_id, Status=$status");

try {
    $pdo->beginTransaction();

    // Kiểm tra đơn hàng tồn tại
    $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND zalopay_trans_id = ?");
    $stmt->execute([$order_id, $trans_id]);
    $order = $stmt->fetch();
    if (!$order) {
        error_log("ZaloPay callback error: Order not found for ID=$order_id, Trans ID=$trans_id");
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['error' => 'Order not found']);
        exit;
    }

    // Chỉ cập nhật nếu trạng thái chưa phải completed
    if ($order['status'] !== 'completed') {
        // Cập nhật trạng thái đơn hàng
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND zalopay_trans_id = ?");
        $stmt->execute([$status, $order_id, $trans_id]);

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
    } else {
        error_log("ZaloPay callback: Order ID=$order_id already completed, skipping update");
    }

    $pdo->commit();
    error_log("ZaloPay callback processed successfully: Order ID=$order_id, Status=$status");
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('ZaloPay callback database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
