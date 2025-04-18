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

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để xóa sản phẩm khỏi giỏ hàng']);
    exit;
}

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
    exit;
}

try {
    // Xóa sản phẩm khỏi giỏ hàng
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        exit;
    }

    // Tính toán lại tổng tiền và số lượng
    $stmt = $pdo->prepare("
        SELECT ci.quantity, p.price
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();

    $total = 0;
    $count = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
        $count += $item['quantity'];
    }

    // Tính phí vận chuyển (miễn phí cho đơn hàng >= 1,000,000đ)
    $shipping = $total >= 1000000 ? 0 : 30000;
    $total_with_shipping = $total + $shipping;

    echo json_encode([
        'success' => true,
        'total' => number_format($total, 0, ',', '.') . ' ₫',
        'count' => $count,
        'shipping' => number_format($shipping, 0, ',', '.') . ' ₫',
        'total_with_shipping' => number_format($total_with_shipping, 0, ',', '.') . ' ₫'
    ]);
} catch (PDOException $e) {
    error_log("Error removing cart item: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại sau']);
}
exit;
