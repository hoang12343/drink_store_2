<?php
define('APP_START', true);
require_once '../includes/db_connect.php';
require_once '../includes/session_start.php';
require_once '../includes/config.php';

if (!isset($_SESSION['logged_in']) || !isset($_GET['trans_id'])) {
    error_log('ZaloPay status check error: Missing session or trans_id');
    header('Location: ../index.php?page=orders&error=' . urlencode('Yêu cầu không hợp lệ'));
    exit;
}

$trans_id = filter_input(INPUT_GET, 'trans_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$status_from_url = isset($_GET['status']) ? (int)$_GET['status'] : null;
error_log("ZaloPay status check: Checking status for trans_id=$trans_id, status_from_url=$status_from_url");

// Xác minh checksum (nếu có)
if (
    isset($_GET['checksum']) && isset($_GET['appid']) && isset($_GET['apptransid']) && isset($_GET['pmcid']) &&
    isset($_GET['bankcode']) && isset($_GET['amount']) && isset($_GET['discountamount']) && isset($_GET['status'])
) {
    $checksum_data = $_GET['appid'] . '|' . $_GET['apptransid'] . '|' . $_GET['pmcid'] . '|' .
        $_GET['bankcode'] . '|' . $_GET['amount'] . '|' . $_GET['discountamount'] . '|' . $_GET['status'];
    $expected_checksum = hash_hmac('sha256', $checksum_data, ZALOPAY_CONFIG['key2']);
    if ($expected_checksum !== $_GET['checksum']) {
        error_log("ZaloPay status check error: Invalid checksum for trans_id=$trans_id");
        header('Location: ../index.php?page=orders&error=' . urlencode('Xác minh yêu cầu thất bại'));
        exit;
    }
}

// Kiểm tra trạng thái đơn hàng trước
try {
    $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE zalopay_trans_id = ? AND user_id = ?");
    $stmt->execute([$trans_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) {
        error_log("ZaloPay status check error: Order not found for trans_id=$trans_id");
        header('Location: ../index.php?page=orders&error=' . urlencode('Đơn hàng không tồn tại'));
        exit;
    }
    if ($order['status'] === 'completed') {
        error_log("ZaloPay status check: Order ID={$order['id']} already completed");
        header('Location: ../index.php?page=orders&success=' . urlencode('Đơn hàng đã hoàn thành'));
        exit;
    }
} catch (PDOException $e) {
    error_log("ZaloPay status check error: Database error - " . $e->getMessage());
    header('Location: ../index.php?page=orders&error=' . urlencode('Lỗi kiểm tra đơn hàng'));
    exit;
}

// Nếu URL có status=1 và checksum hợp lệ, cập nhật ngay
if ($status_from_url === 1 && isset($expected_checksum)) {
    try {
        $pdo->beginTransaction();

        // Cập nhật trạng thái đơn hàng
        $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE zalopay_trans_id = ? AND user_id = ?");
        $stmt->execute([$trans_id, $_SESSION['user_id']]);

        // Giảm tồn kho
        $stmt = $pdo->prepare("
            SELECT oi.order_id, oi.product_id, oi.quantity
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.zalopay_trans_id = ?
        ");
        $stmt->execute([$trans_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
            $stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
            if ($stmt->rowCount() === 0) {
                error_log("ZaloPay status check error: Insufficient stock for product ID={$item['product_id']}");
                $pdo->rollBack();
                header('Location: ../index.php?page=orders&error=' . urlencode('Không đủ tồn kho'));
                exit;
            }
        }

        $pdo->commit();
        error_log("ZaloPay status check: Order ID={$order['id']} updated to completed via URL status=1");
        header('Location: ../index.php?page=orders&success=' . urlencode('Đã cập nhật trạng thái đơn hàng'));
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("ZaloPay status check error: Database error - " . $e->getMessage());
        header('Location: ../index.php?page=orders&error=' . urlencode('Lỗi cập nhật trạng thái đơn hàng'));
        exit;
    }
}

// Gửi yêu cầu kiểm tra trạng thái đến ZaloPay
$data = [
    'app_id' => ZALOPAY_CONFIG['app_id'],
    'app_trans_id' => $trans_id,
    'mac' => hash_hmac('sha256', ZALOPAY_CONFIG['app_id'] . '|' . $trans_id, ZALOPAY_CONFIG['key1'])
];
error_log("ZaloPay status check: Request data=" . json_encode($data));

$ch = curl_init('https://sb-openapi.zalopay.vn/v2/query');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'ngrok-skip-browser-warning: true' // Thêm header để bỏ qua trang ngrok
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    error_log("ZaloPay status check error: cURL error - " . $curl_error);
    header('Location: ../index.php?page=orders&error=' . urlencode('Lỗi kết nối ZaloPay'));
    exit;
}

$result = json_decode($response, true);
error_log("ZaloPay status check: Response for trans_id=$trans_id, Response=" . json_encode($result));

// Xử lý phản hồi từ ZaloPay
if (isset($result['return_code']) && $result['return_code'] == 1 && isset($result['data']['status']) && $result['data']['status'] == 1) {
    try {
        $pdo->beginTransaction();

        // Cập nhật trạng thái đơn hàng
        $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE zalopay_trans_id = ? AND user_id = ?");
        $stmt->execute([$trans_id, $_SESSION['user_id']]);

        // Giảm tồn kho
        $stmt = $pdo->prepare("
            SELECT oi.order_id, oi.product_id, oi.quantity
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.zalopay_trans_id = ?
        ");
        $stmt->execute([$trans_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
            $stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
            if ($stmt->rowCount() === 0) {
                error_log("ZaloPay status check error: Insufficient stock for product ID={$item['product_id']}");
                $pdo->rollBack();
                header('Location: ../index.php?page=orders&error=' . urlencode('Không đủ tồn kho'));
                exit;
            }
        }

        $pdo->commit();
        error_log("ZaloPay status check: Order ID={$order['id']} updated to completed via API");
        header('Location: ../index.php?page=orders&success=' . urlencode('Đã cập nhật trạng thái đơn hàng'));
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("ZaloPay status check error: Database error - " . $e->getMessage());
        header('Location: ../index.php?page=orders&error=' . urlencode('Lỗi cập nhật trạng thái đơn hàng'));
        exit;
    }
} else {
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'failed' WHERE zalopay_trans_id = ? AND user_id = ?");
        $stmt->execute([$trans_id, $_SESSION['user_id']]);
        error_log("ZaloPay status check: Updated to failed for trans_id=$trans_id");
        $error_message = $result['return_message'] ?? 'Không thể xác minh trạng thái thanh toán';
        header('Location: ../index.php?page=orders&error=' . urlencode($error_message));
        exit;
    } catch (PDOException $e) {
        error_log("ZaloPay status check error: Update failed status error - " . $e->getMessage());
        header('Location: ../index.php?page=orders&error=' . urlencode('Lỗi cập nhật trạng thái đơn hàng'));
        exit;
    }
}
