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
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để cập nhật giỏ hàng']);
    exit;
}

$index = filter_input(INPUT_POST, 'index', FILTER_SANITIZE_NUMBER_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);

if ($index === null || !isset($_SESSION['cart'][$index]) || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Lấy thông tin sản phẩm từ cơ sở dữ liệu để kiểm tra tồn kho
$product_id = $_SESSION['cart'][$index]['product_id'];
$stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product || $quantity > $product['stock']) {
    echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho']);
    exit;
}

// Cập nhật số lượng trong session
$_SESSION['cart'][$index]['quantity'] = $quantity;

// Tính toán lại tổng tiền
$subtotal = $_SESSION['cart'][$index]['price_numeric'] * $quantity;
$formatted_subtotal = number_format($subtotal, 0, ',', '.') . ' ₫';

$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price_numeric'] * $item['quantity'];
}

// Tính phí vận chuyển (ví dụ: miễn phí cho đơn hàng trên 1,000,000đ)
$shipping = $total >= 1000000 ? 0 : 30000;
$total_with_shipping = $total + $shipping;

echo json_encode([
    'success' => true,
    'subtotal' => $formatted_subtotal,
    'total' => number_format($total, 0, ',', '.') . ' ₫',
    'shipping' => number_format($shipping, 0, ',', '.') . ' ₫',
    'total_with_shipping' => number_format($total_with_shipping, 0, ',', '.') . ' ₫'
]);
exit;