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

$cart_item_id = filter_input(INPUT_POST, 'cart_item_id', FILTER_SANITIZE_NUMBER_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);

if (!$cart_item_id || $quantity < 1) {
    error_log("Invalid input: cart_item_id=$cart_item_id, quantity=$quantity, user_id={$_SESSION['user_id']}");
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    // Fetch cart item and product details
    $stmt = $pdo->prepare("
        SELECT ci.product_id, ci.quantity, p.stock, p.price
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.id = ? AND ci.user_id = ?
    ");
    $stmt->execute([$cart_item_id, $_SESSION['user_id']]);
    $item = $stmt->fetch();

    if (!$item) {
        error_log("Cart item not found: cart_item_id=$cart_item_id, user_id={$_SESSION['user_id']}");
        echo json_encode(['success' => false, 'message' => 'Mục giỏ hàng không tồn tại']);
        exit;
    }

    // Validate stock
    $stmt = $pdo->prepare("
        SELECT SUM(quantity) as total_quantity 
        FROM cart_items 
        WHERE user_id = ? AND product_id = ? AND id != ?
    ");
    $stmt->execute([$_SESSION['user_id'], $item['product_id'], $cart_item_id]);
    $other_quantity = $stmt->fetch()['total_quantity'] ?? 0;
    $new_total_quantity = $other_quantity + $quantity;

    if ($new_total_quantity > $item['stock']) {
        echo json_encode([
            'success' => false,
            'message' => 'Số lượng vượt quá tồn kho (còn ' . $item['stock'] . ' sản phẩm)'
        ]);
        exit;
    }

    // Update quantity
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $cart_item_id, $_SESSION['user_id']]);

    // Calculate totals
    $subtotal = $item['price'] * $quantity;
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
    foreach ($cart_items as $cart_item) {
        $total += $cart_item['price'] * $cart_item['quantity'];
        $count += $cart_item['quantity'];
    }

    $shipping = $total >= 1000000 ? 0 : 30000;
    $total_with_shipping = $total + $shipping;

    echo json_encode([
        'success' => true,
        'subtotal' => $formatted_subtotal,
        'total' => number_format($total, 0, ',', '.') . ' ₫',
        'count' => $count,
        'shipping' => number_format($shipping, 0, ',', '.') . ' ₫',
        'total_with_shipping' => number_format($total_with_shipping, 0, ',', '.') . ' ₫'
    ]);
} catch (PDOException $e) {
    error_log("Error in update_cart: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại sau']);
    exit;
}
