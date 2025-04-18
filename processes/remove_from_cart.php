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

$cart_item_id = filter_input(INPUT_POST, 'cart_item_id', FILTER_SANITIZE_NUMBER_INT);

if (!$cart_item_id) {
    error_log("Invalid cart_item_id: cart_item_id=$cart_item_id, user_id={$_SESSION['user_id']}");
    echo json_encode(['success' => false, 'message' => 'ID mục giỏ hàng không hợp lệ']);
    exit;
}

try {
    // Delete cart item
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_item_id, $_SESSION['user_id']]);

    if ($stmt->rowCount() === 0) {
        error_log("Cart item not found for deletion: cart_item_id=$cart_item_id, user_id={$_SESSION['user_id']}");
        echo json_encode(['success' => false, 'message' => 'Mục giỏ hàng không tồn tại']);
        exit;
    }

    // Calculate totals
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
        'total' => number_format($total, 0, ',', '.') . ' ₫',
        'count' => $count,
        'shipping' => number_format($shipping, 0, ',', '.') . ' ₫',
        'total_with_shipping' => number_format($total_with_shipping, 0, ',', '.') . ' ₫'
    ]);
} catch (PDOException $e) {
    error_log("Error in remove_from_cart: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại sau']);
    exit;
}