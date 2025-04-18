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

$user_id = $_SESSION['user_id'];
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT) ?: 1;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ']);
    exit;
}

try {
    // Verify product exists and check stock
    $stmt = $pdo->prepare("SELECT stock, price FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }

    // Check if product already in cart
    $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing_item = $stmt->fetch();

    $new_quantity = $existing_item ? $existing_item['quantity'] + $quantity : $quantity;

    if ($new_quantity > $product['stock']) {
        echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho']);
        exit;
    }

    // Add or update cart
    $pdo->beginTransaction();

    if ($existing_item) {
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $user_id, $product_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }

    // Calculate total items
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_items = $stmt->fetch()['total_items'] ?? 0;

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Sản phẩm đã được thêm vào giỏ hàng',
        'count' => $total_items
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error adding to cart: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại sau']);
}
exit;
