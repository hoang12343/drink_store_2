<?php
define('APP_START', true);
require_once '../includes/db_connect.php';
require_once '../includes/session_start.php';
require_once '../includes/config.php';

if (!isset($_SESSION['logged_in']) || !isset($_GET['trans_id'])) {
    header('Location: ../index.php?page=orders');
    exit;
}

$trans_id = $_GET['trans_id'];
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
error_log("ZaloPay query response: " . json_encode($result));
if ($result['return_code'] == 1 && $result['data']['status'] == 1) {
    $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE zalopay_trans_id = ?");
    $stmt->execute([$trans_id]);
    header('Location: ../index.php?page=orders&success=' . urlencode('Đã cập nhật trạng thái đơn hàng'));
} else {
    header('Location: ../index.php?page=orders&error=' . urlencode($result['return_message'] ?? 'Không thể xác minh trạng thái thanh toán'));
}
