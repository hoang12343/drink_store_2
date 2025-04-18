<?php
session_start();
define('APP_START', true);
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được phép']);
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng']);
    exit;
}

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_STRING);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT) ?: 1;

if (!$product_id || !$name || !$price) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ']);
    exit;
}

// Chuyển đổi giá từ định dạng tiền tệ (VD: 1.500.000 ₫) sang số
$price_numeric = preg_replace('/[^\d]/', '', $price);

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
        'price_numeric' => $price_numeric,
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
