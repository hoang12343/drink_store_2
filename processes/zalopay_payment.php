<?php
if (!defined('APP_START')) exit('No direct access');
require_once '../includes/db_connect.php';
require_once '../includes/session_start.php';
require_once '../includes/config.php';

// Kiểm tra phiên đăng nhập và dữ liệu đầu vào
if (!isset($_SESSION['logged_in']) || !isset($_POST['selected_items']) || !isset($_POST['total_amount'])) {
    header('Location: ../index.php?page=cart');
    exit;
}

$selected_items = json_decode($_POST['selected_items'], true);
$total_amount = floatval($_POST['total_amount']);
if (empty($selected_items) || $total_amount <= 0) {
    header('Location: ../index.php?page=cart');
    exit;
}

// Tạo đơn hàng trong database
try {
    $pdo->beginTransaction();

    // Tạo order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, status)
        VALUES (?, ?, 'pending')
    ");
    $stmt->execute([$_SESSION['user_id'], $total_amount]);
    $order_id = $pdo->lastInsertId();

    // Tạo order_items
    $placeholders = implode(',', array_fill(0, count($selected_items), '?'));
    $stmt = $pdo->prepare("
        SELECT ci.id, ci.product_id, ci.quantity, p.price, p.name
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ? AND ci.id IN ($placeholders)
    ");
    $stmt->execute(array_merge([$_SESSION['user_id']], $selected_items));
    $items = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($items as $item) {
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // Xóa các mục đã chọn khỏi giỏ hàng
    $stmt = $pdo->prepare("
        DELETE FROM cart_items
        WHERE user_id = ? AND id IN ($placeholders)
    ");
    $stmt->execute(array_merge([$_SESSION['user_id']], $selected_items));

    $pdo->commit();
    error_log("Order created successfully: Order ID=$order_id, User ID={$_SESSION['user_id']}");
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error creating order: " . $e->getMessage());
    header('Location: ../index.php?page=cart&error=' . urlencode('Không thể tạo đơn hàng'));
    exit;
}

// Tạo yêu cầu thanh toán ZaloPay
$order = [
    'app_id' => ZALOPAY_CONFIG['app_id'],
    'app_user' => 'user_' . $_SESSION['user_id'],
    'app_time' => round(microtime(true) * 1000),
    'amount' => (int)$total_amount,
    'app_trans_id' => date('ymd') . '_' . $order_id,
    'embed_data' => json_encode([
        'order_id' => $order_id,
        'redirecturl' => 'https://73e1-2405-4802-1d49-1bc0-e5f9-b1dd-745f-13a9.ngrok-free.app/BTL-nhom-9-4/index.php?page=orders&check_trans_id=' . urlencode(date('ymd') . '_' . $order_id)
    ]),
    'item' => json_encode(array_map(function ($item) {
        return [
            'itemid' => (string)$item['product_id'],
            'itemname' => $item['name'],
            'itemquantity' => $item['quantity'],
            'itemprice' => (int)$item['price']
        ];
    }, $items)),
    'description' => 'Thanh toán đơn hàng #' . $order_id,
    'bank_code' => '',
    'callback_url' => 'https://73e1-2405-4802-1d49-1bc0-e5f9-b1dd-745f-13a9.ngrok-free.app/BTL-nhom-9-4/processes/zalopay_callback.php'
];

// Tạo chữ ký
$data = $order['app_id'] . '|' . $order['app_trans_id'] . '|' . $order['app_user'] . '|' .
    $order['amount'] . '|' . $order['app_time'] . '|' . $order['embed_data'] . '|' . $order['item'];
$order['mac'] = hash_hmac('sha256', $data, ZALOPAY_CONFIG['key1']);

error_log("ZaloPay payment request: Order ID=$order_id, Trans ID={$order['app_trans_id']}");

// Gửi yêu cầu đến ZaloPay
$ch = curl_init(ZALOPAY_CONFIG['endpoint']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($order));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);

// Kiểm tra lỗi cURL
if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    error_log("cURL error in zalopay_payment: " . $error);
    header('Location: ../index.php?page=checkout&error=' . urlencode('Lỗi kết nối đến ZaloPay'));
    exit;
}
curl_close($ch);

$result = json_decode($response, true);
error_log("ZaloPay payment response: " . json_encode($result));

if (isset($result['return_code']) && $result['return_code'] == 1) {
    $stmt = $pdo->prepare("UPDATE orders SET zalopay_trans_id = ? WHERE id = ?");
    $stmt->execute([$order['app_trans_id'], $order_id]);
    error_log("Redirecting to ZaloPay order_url for Order ID=$order_id");
    header('Location: ' . $result['order_url']);
    exit;
} else {
    error_log('ZaloPay error: ' . json_encode($result));
    header('Location: ../index.php?page=checkout&error=' . urlencode($result['return_message'] ?? 'Không thể tạo thanh toán'));
    exit;
}