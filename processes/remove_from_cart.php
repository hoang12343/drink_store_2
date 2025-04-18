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
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để xóa sản phẩm khỏi giỏ hàng']);
    exit;
}

$index = filter_input(INPUT_POST, 'index', FILTER_SANITIZE_NUMBER_INT);

if ($index === null || !isset($_SESSION['cart'][$index])) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
    exit;
}

// Xóa sản phẩm khỏi giỏ hàng
unset($_SESSION['cart'][$index]);
$_SESSION['cart'] = array_values($_SESSION['cart']); // Sắp xếp lại mảng

// Tính toán lại tổng tiền và số lượng
$total = 0;
$count = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price_numeric'] * $item['quantity'];
    $count += $item['quantity'];
}

// Tính phí vận chuyển
$shipping = $total >= 1000000 ? 0 : 30000;
$total_with_shipping = $total + $shipping;

echo json_encode([
    'success' => true,
    'total' => number_format($total, 0, ',', '.') . ' ₫',
    'count' => $count,
    'shipping' => number_format($shipping, 0, ',', '.') . ' ₫',
    'total_with_shipping' => number_format($total_with_shipping, 0, ',', '.') . ' ₫'
]);
exit;
