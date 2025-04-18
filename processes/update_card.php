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
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để cập nhật giỏ hàng']);
    exit;
}

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);

if (!$product_id || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    // Kiểm tra tồn kho
    $stmt = $pdo->prepare("SELECT stock, price FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }

    if ($quantity > $product['stock']) {
        echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho']);
        exit;
    }

    // Cập nhật hoặc chèn mục giỏ hàng
    $stmt = $pdo->prepare("
        INSERT INTO cart_items (user_id, product_id, quantity)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = ?, updated_at = NOW()
    ");
    $stmt->execute([$_SESSION['user_id'], $product_id, $quantity, $quantity]);

    // Tính toán lại tổng tiền
    $subtotal = $product['price'] * $quantity;
    $formatted_subtotal = number_format($subtotal, 0, ',', '.') . ' ₫';

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

    $shipping = $total >= 1000000 ? 0 : 30000;
    $total_with_shipping = $total + $shipping;

    echo json_encode([
        'success' => true,
        'subtotal' => $formatted_subtotal,
        'total' => number_format($total, 0, ',', '.') . ' ₫',
        'shipping' => number_format($shipping, 0, ',', '.') . ' ₫',
        'total_with_shipping' => number_format($total_with_shipping, 0, ',', '.') . ' ₫'
    ]);
} catch (PDOException $e) {
    error_log("Error updating cart item: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại sau']);
}
exit;
