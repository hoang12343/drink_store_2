<?php
if (!defined('APP_START')) define('APP_START', true);
require_once '../includes/db_connect.php';
require_once '../includes/session_start.php';
require_once '../includes/config.php';

// Suppress output for production
if (!DEVELOPMENT_MODE) {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Validate session and input data
if (!isset($_SESSION['logged_in']) || !isset($_POST['selected_items']) || !isset($_POST['total_amount'])) {
    error_log('Invalid request: missing session or input data');
    header('Location: ../index.php?page=checkout&error=' . urlencode('Dữ liệu đầu vào không hợp lệ'));
    exit;
}

$selected_items = json_decode($_POST['selected_items'], true);
$total_amount = floatval($_POST['total_amount']);
$user_id = $_SESSION['user_id'] ?? null;

if (empty($selected_items) || $total_amount <= 0 || !$user_id) {
    error_log("Invalid request: selected_items=" . json_encode($selected_items) . ", total_amount=$total_amount, user_id=$user_id");
    header('Location: ../index.php?page=checkout&error=' . urlencode('Dữ liệu đầu vào không hợp lệ'));
    exit;
}

// Validate ZaloPay configuration
if (empty(ZALOPAY_CONFIG['app_id']) || empty(ZALOPAY_CONFIG['key1']) || empty(ZALOPAY_CONFIG['endpoint'])) {
    error_log('Invalid ZaloPay config: missing app_id, key1, or endpoint');
    header('Location: ../index.php?page=checkout&error=' . urlencode('Cấu hình ZaloPay không hợp lệ'));
    exit;
}

// Create order
try {
    $pdo->beginTransaction();

    // Fetch cart items
    $placeholders = implode(',', array_fill(0, count($selected_items), '?'));
    $stmt = $pdo->prepare("
        SELECT ci.id, ci.product_id, ci.quantity, p.price, p.name, p.stock
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ? AND ci.id IN ($placeholders)
    ");
    $stmt->execute(array_merge([$user_id], $selected_items));
    $items = $stmt->fetchAll();

    if (empty($items)) {
        throw new Exception('Không tìm thấy sản phẩm trong giỏ hàng');
    }

    // Check stock availability
    foreach ($items as $item) {
        if ($item['stock'] < $item['quantity']) {
            throw new Exception("Sản phẩm {$item['name']} không đủ tồn kho");
        }
    }

    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, status, payment_method, created_at, updated_at)
        VALUES (?, ?, 'pending', 'zalopay', NOW(), NOW())
    ");
    $stmt->execute([$user_id, $total_amount]);
    $order_id = $pdo->lastInsertId();

    // Insert order_items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($items as $item) {
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // Delete cart_items
    $stmt = $pdo->prepare("
        DELETE FROM cart_items
        WHERE user_id = ? AND id IN ($placeholders)
    ");
    $stmt->execute(array_merge([$user_id], $selected_items));

    $pdo->commit();
    error_log("Order created successfully: Order ID=$order_id, User ID=$user_id");
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("PDO error creating order: " . $e->getMessage());
    header('Location: ../index.php?page=checkout&error=' . urlencode('Không thể tạo đơn hàng'));
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error creating order: " . $e->getMessage());
    header('Location: ../index.php?page=checkout&error=' . urlencode($e->getMessage()));
    exit;
}

// Prepare ZaloPay payment request
$ngrok_domain = 'https://b689-2405-4802-1d49-1bc0-f957-9ef9-600e-fc9c.ngrok-free.app';
$order = [
    'app_id' => ZALOPAY_CONFIG['app_id'],
    'app_user' => 'user_' . $user_id,
    'app_time' => round(microtime(true) * 1000),
    'amount' => (int)$total_amount,
    'app_trans_id' => date('ymd') . '_' . $order_id,
    'embed_data' => json_encode([
        'order_id' => $order_id,
        'redirecturl' => $ngrok_domain . '/BTL-nhom-9-4/index.php?page=orders&check_trans_id=' . urlencode(date('ymd') . '_' . $order_id)
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
    'callback_url' => $ngrok_domain . '/BTL-nhom-9-4/processes/zalopay_callback.php'
];

// Generate HMAC signature
$data = implode('|', [
    $order['app_id'],
    $order['app_trans_id'],
    $order['app_user'],
    $order['amount'],
    $order['app_time'],
    $order['embed_data'],
    $order['item']
]);
$order['mac'] = hash_hmac('sha256', $data, ZALOPAY_CONFIG['key1']);

error_log("ZaloPay payment request: Order ID=$order_id, Trans ID={$order['app_trans_id']}");

// Send ZaloPay request
$ch = curl_init(ZALOPAY_CONFIG['endpoint']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($order));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
$response = curl_exec($ch);

if ($response === false) {
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    curl_close($ch);
    error_log("cURL error in zalopay_payment: [$errno] $error");
    header('Location: ../index.php?page=checkout&error=' . urlencode('Lỗi kết nối ZaloPay'));
    exit;
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    error_log("cURL failed with HTTP code $http_code: " . ($response ?: 'empty'));
    header('Location: ../index.php?page=checkout&error=' . urlencode('Lỗi kết nối ZaloPay'));
    exit;
}

$result = json_decode($response, true);
if (!$result) {
    error_log("Invalid ZaloPay response: " . $response);
    header('Location: ../index.php?page=checkout&error=' . urlencode('Phản hồi ZaloPay không hợp lệ'));
    exit;
}

error_log("ZaloPay payment response: " . json_encode($result));

if (isset($result['return_code']) && $result['return_code'] == 1) {
    try {
        $stmt = $pdo->prepare("UPDATE orders SET zalopay_trans_id = ? WHERE id = ?");
        $stmt->execute([$order['app_trans_id'], $order_id]);
        error_log("Redirecting to ZaloPay order_url for Order ID=$order_id: " . $result['order_url']);
        header('Location: ' . $result['order_url']);
        exit;
    } catch (PDOException $e) {
        error_log("PDO error updating zalopay_trans_id: " . $e->getMessage());
        header('Location: ../index.php?page=checkout&error=' . urlencode('Lỗi lưu giao dịch ZaloPay'));
        exit;
    }
} else {
    error_log('ZaloPay error: ' . json_encode($result));
    header('Location: ../index.php?page=checkout&error=' . urlencode($result['return_message'] ?? 'Không thể tạo thanh toán ZaloPay'));
    exit;
}
