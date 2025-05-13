<?php
define('APP_START', true);
require_once '../includes/db_connect.php';
require_once '../includes/session_start.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login&redirect=checkout');
    exit;
}

// Kiểm tra dữ liệu đầu vào
$selected_items = isset($_POST['selected_items']) ? json_decode($_POST['selected_items'], true) : [];
$total_amount = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0;
$discount = isset($_POST['discount']) ? floatval($_POST['discount']) : 0;
$shipping = isset($_POST['shipping']) ? floatval($_POST['shipping']) : 0;
$promo_code = isset($_POST['promo_code']) ? trim($_POST['promo_code']) : null;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : null;

// Kiểm tra promo_code
if ($promo_code) {
    $stmt = $pdo->prepare("SELECT id FROM promo_codes WHERE code = ? AND is_active = 1 AND NOW() BETWEEN start_date AND end_date");
    $stmt->execute([$promo_code]);
    if (!$stmt->fetch()) {
        header('Location: ../index.php?page=checkout&error=' . urlencode('Mã giảm giá không hợp lệ hoặc đã hết hạn'));
        exit;
    }
}

if (empty($selected_items) || $total_amount <= 0 || !in_array($payment_method, ['zalopay', 'cod'])) {
    header('Location: ../index.php?page=checkout&error=' . urlencode('Dữ liệu thanh toán không hợp lệ'));
    exit;
}

try {
    $pdo->beginTransaction();

    // Tạo đơn hàng
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, discount, shipping, promo_code, payment_method, status, zalopay_trans_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NULL, NOW())
    ");
    $status = $payment_method === 'cod' ? 'confirmed' : 'pending';
    $stmt->execute([
        $_SESSION['user_id'],
        $total_amount,
        $discount,
        $shipping,
        $promo_code,
        $payment_method,
        $status
    ]);
    $order_id = $pdo->lastInsertId();

    // Thêm chi tiết đơn hàng
    $placeholders = implode(',', array_fill(0, count($selected_items), '(?, ?, ?, ?)'));
    $params = [];
    foreach ($selected_items as $item_id) {
        $stmt = $pdo->prepare("
            SELECT ci.product_id, ci.quantity, p.price
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.id = ? AND ci.user_id = ?
        ");
        $stmt->execute([$item_id, $_SESSION['user_id']]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($item) {
            $params[] = $order_id;
            $params[] = $item['product_id'];
            $params[] = $item['quantity'];
            $params[] = $item['price'];
        }
    }

    if (!empty($params)) {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES $placeholders
        ");
        $stmt->execute($params);
    } else {
        throw new Exception('Không tìm thấy sản phẩm hợp lệ trong giỏ hàng');
    }

    // Xóa các sản phẩm đã thanh toán khỏi giỏ hàng
    $placeholders = implode(',', array_fill(0, count($selected_items), '?'));
    $stmt = $pdo->prepare("
        DELETE FROM cart_items
        WHERE id IN ($placeholders) AND user_id = ?
    ");
    $stmt->execute(array_merge($selected_items, [$_SESSION['user_id']]));

    $pdo->commit();

    // Xử lý theo phương thức thanh toán
    if ($payment_method === 'zalopay') {
        // Gọi zalopay_payment.php
        require_once 'zalopay_payment.php';
    } else {
        // COD: Chuyển hướng đến trang xác nhận đơn hàng
        header('Location: ../index.php?page=order_confirmation&order_id=' . urlencode($order_id) . '&success=' . urlencode('Đơn hàng COD đã được đặt thành công'));
        exit;
    }
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error processing payment: " . $e->getMessage());
    header('Location: ../index.php?page=checkout&error=' . urlencode('Lỗi hệ thống. Vui lòng thử lại.'));
    exit;
}
