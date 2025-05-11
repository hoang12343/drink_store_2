<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$promo_code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$selected_items = isset($_POST['selected_items']) ? json_decode($_POST['selected_items'], true) : [];

if (empty($promo_code)) {
    $response['message'] = 'Promo code is required.';
    echo json_encode($response);
    exit;
}

try {
    // Lấy thông tin mã giảm giá
    $stmt = $pdo->prepare("
        SELECT * FROM promo_codes 
        WHERE code = ? 
        AND is_active = 1 
        AND start_date <= NOW() 
        AND end_date >= NOW()
    ");
    $stmt->execute([$promo_code]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$promo) {
        $response['message'] = 'Invalid or expired promo code.';
        echo json_encode($response);
        exit;
    }

    // Tính lại tổng giỏ hàng một cách chi tiết
    $user_id = $_SESSION['user_id'] ?? 0;

    // Log thông tin user_id để kiểm tra
    error_log("apply_promo.php - Processing for user_id: $user_id");

    // Nếu có danh sách sản phẩm được chọn
    if (!empty($selected_items)) {
        $placeholders = implode(',', array_fill(0, count($selected_items), '?'));
        $stmt = $pdo->prepare("
            SELECT ci.id, ci.product_id, ci.quantity, p.price, p.name
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ? AND ci.id IN ($placeholders)
        ");
        $stmt->execute(array_merge([$user_id], $selected_items));
    } else {
        // Nếu không có danh sách sản phẩm được chọn, lấy tất cả
        $stmt = $pdo->prepare("
            SELECT ci.id, ci.product_id, ci.quantity, p.price, p.name
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ?
        ");
        $stmt->execute([$user_id]);
    }

    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tính tổng từng sản phẩm
    $subtotal = 0;
    $item_details = [];
    foreach ($cart_items as $item) {
        $item_price = floatval($item['price']);
        $item_quantity = intval($item['quantity']);
        $item_total = $item_price * $item_quantity;
        $subtotal += $item_total;
        $item_details[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'quantity' => $item_quantity,
            'price' => $item_price,
            'total' => $item_total
        ];
    }

    // Log chi tiết từng sản phẩm để kiểm tra
    error_log("apply_promo.php - Selected cart items: " . json_encode($item_details));
    error_log("apply_promo.php - Calculated subtotal: $subtotal");

    if ($subtotal < $promo['min_order_value']) {
        $response['message'] = 'Order value must be at least ' . number_format($promo['min_order_value'], 0, ',', '.') . ' VNĐ.';
        echo json_encode($response);
        exit;
    }

    // Tính giảm giá
    $discount_percentage = floatval($promo['discount_percentage']);
    $discount = $subtotal * ($discount_percentage / 100);

    // Kiểm tra giới hạn giảm giá tối đa
    $max_discount = $promo['max_discount_value'] ? floatval($promo['max_discount_value']) : null;
    if ($max_discount && $discount > $max_discount) {
        $discount = $max_discount;
    }

    $total = $subtotal - $discount;
    $shipping = $subtotal >= 1000000 ? 0 : 30000;
    $total_with_shipping = $total + $shipping;

    error_log("apply_promo.php - Discount percentage: $discount_percentage%");
    error_log("apply_promo.php - Calculated discount: $discount");
    error_log("apply_promo.php - Final total: $total_with_shipping");

    // Lưu vào session với các giá trị số nguyên vẹn
    $_SESSION['promo_code'] = [
        'code' => $promo['code'],
        'discount' => floatval($discount),
        'subtotal' => floatval($subtotal),
        'shipping' => floatval($shipping),
        'total' => floatval($total_with_shipping),
        'selected_items' => $selected_items // Lưu danh sách sản phẩm được chọn
    ];

    // Log giá trị session để kiểm tra
    error_log("apply_promo.php - Session values: " . json_encode($_SESSION['promo_code']));

    $response['success'] = true;
    $response['subtotal'] = number_format($subtotal, 0, ',', '.') . ' VNĐ';
    $response['discount'] = number_format($discount, 0, ',', '.') . ' VNĐ';
    $response['shipping'] = number_format($shipping, 0, ',', '.') . ' VNĐ';
    $response['total'] = number_format($total_with_shipping, 0, ',', '.') . ' VNĐ';
} catch (PDOException $e) {
    error_log("Error applying promo code: " . $e->getMessage());
    $response['message'] = 'System error. Please try again.';
}

echo json_encode($response);
exit;
