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

if (!$product_id || $quantity < 1) {
    error_log("Invalid input: product_id=$product_id, quantity=$quantity, user_id=$user_id");
    echo json_encode(['success' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ']);
    exit;
}

try {
    // Verify product exists
    $stmt = $pdo->prepare("SELECT id, name, price, stock, image FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        error_log("Product not found: product_id=$product_id, user_id=$user_id");
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }

    error_log("Adding to cart: product_id=$product_id, name={$product['name']}, quantity=$quantity, user_id=$user_id");

    // Check if product is already in cart
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $cart_item = $stmt->fetch();

    // Calculate new quantity
    $new_quantity = $cart_item ? $cart_item['quantity'] + $quantity : $quantity;

    // Validate stock
    if ($new_quantity > $product['stock']) {
        echo json_encode([
            'success' => false,
            'message' => 'Số lượng vượt quá tồn kho (còn ' . $product['stock'] . ' sản phẩm)'
        ]);
        exit;
    }

    // Update or insert cart item
    if ($cart_item) {
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_quantity, $cart_item['id']]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO cart_items (user_id, product_id, quantity, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$user_id, $product_id, $new_quantity]);
    }

    // Calculate total cart items
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_items = $stmt->fetch()['total_items'] ?? 0;

    echo json_encode([
        'success' => true,
        'message' => 'Sản phẩm "' . htmlspecialchars($product['name']) . '" đã được thêm vào giỏ hàng',
        'count' => $total_items,
        'product_name' => htmlspecialchars($product['name']),
        'product_price' => number_format($product['price'], 0, ',', '.') . ' ₫'
    ]);
} catch (PDOException $e) {
    error_log("Error in add_to_cart: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại sau']);
    exit;
}
