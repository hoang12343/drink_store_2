<?php
session_start();
define('APP_START', true);
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // echo json_encode(['success' => false, 'message' 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng']);
    exit;
}

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT) ?: 1;

if (!$product_id || !$name || !$price) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['product_id'] == $product_id) {
        $item['quantity'] += $quantity;
        $found = true;
        break;
    }
}

if (!$found) {
    $_SESSION['cart'][] = [
        'product_id' => $product_id,
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity
    ];
}

$total_items = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_items += $item['quantity'];
}

echo json_encode([
    'success' => true,
    'message' => 'Sản phẩm đã được thêm vào giỏ hàng',
    'count' => $total_items
]);
exit;