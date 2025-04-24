<?php
define('APP_START', true);
require_once '../includes/db_connect.php';
require_once '../includes/session_start.php';
require_once '../includes/config.php';

if (!isset($_SESSION['logged_in']) || !isset($_GET['trans_id'])) {
    header('Location: ../index.php?page=orders');
    exit;
}

$trans_id = filter_input(INPUT_GET, 'trans_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
error_log("Checking ZaloPay status for trans_id: $trans_id");

$data = [
    'app_id' => ZALOPAY_CONFIG['app_id'],
    'app_trans_id' => $trans_id,
    'mac' => hash_hmac('sha256', ZALOPAY_CONFIG['app_id'] . '|' . $trans_id, ZALOPAY_CONFIG['key1'])
];

$ch = curl_init('https://sb-openapi.zalopay.vn/v2/query');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
$response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    error_log("cURL error in check_zalopay_status: " . $curl_error);
    header('Location: ../index.php?page=orders&error=' . urlencode('Lỗi kết nối ZaloPay'));
    exit;
}

$result = json_decode($response, true);
error_log("ZaloPay query response for trans_id $trans_id: " . json_encode($result));

if ($result['return_code'] == 1 && isset($result['data']['status']) && $result['data']['status'] == 1) {
    try {
        $pdo->beginTransaction();

        // Kiểm tra trạng thái đơn hàng
        $stmt = $pdo->prepare("SELECT status FROM orders WHERE zalopay_trans_id = ?");
        $stmt->execute([$trans_id]);
        $current_status = $stmt->fetchColumn();

        if ($current_status !== 'completed') {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE zalopay_trans_id = ?");
            $stmt->execute([$trans_id]);

            // Giảm tồn kho
            $stmt = $pdo->prepare("
                SELECT oi.order_id, oi.product_id, oi.quantity
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE o.zalopay_trans_id = ?
            ");
            $stmt->execute([$trans_id]);
            $items = $stmt->fetchAll();

            foreach ($items as $item) {
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
        }

        $pdo->commit();
        error_log("ZaloPay status updated to completed for trans_id: $trans_id");
        header('Location: ../index.php?page=orders&success=' . urlencode('Đã cập nhật trạng thái đơn hàng'));
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Database error in check_zalopay_status: " . $e->getMessage());
        header('Location: ../index.php?page=orders&error=' . urlencode('Lỗi cập nhật trạng thái đơn hàng'));
    }
} else {
    // Cập nhật trạng thái failed nếu thanh toán không thành công
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'failed' WHERE zalopay_trans_id = ?");
        $stmt->execute([$trans_id]);
        error_log("ZaloPay status updated to failed for trans_id: $trans_id");
    } catch (PDOException $e) {
        error_log("Database error updating failed status: " . $e->getMessage());
    }
    $error_message = $result['return_message'] ?? 'Không thể xác minh trạng thái thanh toán';
    error_log("ZaloPay status check failed for trans_id $trans_id: $error_message");
    header('Location: ../index.php?page=orders&error=' . urlencode($error_message));
}
