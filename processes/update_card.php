<?php
session_start();
define('APP_START', true);
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to update the cart']);
    exit;
}

// Sanitize inputs
$cart_item_id = filter_input(INPUT_POST, 'cart_item_id', FILTER_SANITIZE_NUMBER_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);

if (!$cart_item_id || $quantity < 1) {
    error_log("Invalid input: cart_item_id=$cart_item_id, quantity=$quantity, user_id={$_SESSION['user_id']}");
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
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
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        error_log("Cart item not found: cart_item_id=$cart_item_id, user_id={$_SESSION['user_id']}");
        echo json_encode(['success' => false, 'message' => 'Cart item does not exist']);
        exit;
    }

    // Validate stock availability
    if ($quantity > $item['stock']) {
        echo json_encode([
            'success' => false,
            'message' => "Quantity exceeds stock (only {$item['stock']} available)"
        ]);
        exit;
    }

    // Update cart item quantity
    $stmt = $pdo->prepare("
        UPDATE cart_items 
        SET quantity = ?, updated_at = NOW() 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$quantity, $cart_item_id, $_SESSION['user_id']]);

    // Calculate new subtotal
    $subtotal = $item['price'] * $quantity;
    $formatted_subtotal = number_format($subtotal, 0, ',', '.') . ' ₫';

    echo json_encode([
        'success' => true,
        'subtotal' => $formatted_subtotal,
        'quantity' => $quantity
    ]);
} catch (PDOException $e) {
    error_log("Error updating cart: {$e->getMessage()}");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'System error, please try again later']);
    exit;
}
