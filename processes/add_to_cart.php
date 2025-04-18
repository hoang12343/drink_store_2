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

try {
    // Kiểm tra sản phẩm và tồn kho
    $stmt = $pdo->prepare("SELECT stock, price FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }

    // Kiểm tra nếu sản phẩm đã có trong giỏ hàng
    $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $existing_item = $stmt->fetch();

    $new_quantity = $existing_item ? $existing_item['quantity'] + $quantity : $quantity;

    if ($new_quantity > $product['stock']) {
        echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho']);
        exit;
    }

    // Thêm hoặc cập nhật giỏ hàng
    $stmt = $pdo->prepare("
        INSERT INTO cart_items (user_id, product_id, quantity)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = quantity + ?, updated_at = NOW()
    ");
    $stmt->execute([$_SESSION['user_id'], $product_id, $quantity, $quantity]);

    // Tính tổng số lượng
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_items = $stmt->fetch()['total_items'] ?? 0;

    echo json_encode([
        'success' => true,
        'message' => 'Sản phẩm đã được thêm vào giỏ hàng',
        'count' => $total_items
    ]);
} catch (PDOException $e) {
    error_log("Error adding to cart: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại sau']);
}
exit;
