<?php
// Bật ghi log lỗi
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/php_errors.log');

// Khởi động session
session_start();

// Kiểm tra đăng nhập
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    error_log('Unauthorized access attempt: user_id not set');
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập']);
    exit;
}

// Thiết lập header cho phản hồi JSON
header('Content-Type: application/json; charset=utf-8');

// Kết nối cơ sở dữ liệu
try {
    require_once '../includes/db_connect.php';
} catch (Exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Lỗi kết nối cơ sở dữ liệu']);
    exit;
}

/**
 * Xử lý đơn hàng COD
 * @param PDO $pdo
 * @param int $user_id
 * @param float $shipping
 * @param string|null $promo_code
 * @return array
 */
function processCODOrder($pdo, $user_id, $shipping, $promo_code = null)
{
    try {
        // Bắt đầu giao dịch
        $pdo->beginTransaction();

        // Lấy giỏ hàng của người dùng
        $stmt = $pdo->prepare("
            SELECT ci.product_id, ci.quantity
            FROM cart_items ci
            WHERE ci.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cart_items)) {
            error_log("Empty cart for user_id: $user_id");
            throw new Exception('Giỏ hàng trống');
        }

        $total_price = 0;

        // Kiểm tra sản phẩm và tính tổng giá
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            // Kiểm tra sản phẩm tồn tại
            $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                error_log("Product not found: product_id=$product_id for user_id=$user_id");
                throw new Exception("Sản phẩm $product_id không tồn tại");
            }

            // Kiểm tra số lượng trong kho
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product_stock = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product_stock || $product_stock['stock'] < $quantity) {
                error_log("Insufficient stock for product_id=$product_id, requested=$quantity, available=" . ($product_stock['stock'] ?? 0));
                throw new Exception("Không đủ hàng cho sản phẩm $product_id");
            }

            $total_price += $product['price'] * $quantity;
        }

        // Áp dụng mã khuyến mãi (nếu có)
        $discount = 0;
        if ($promo_code) {
            $stmt = $pdo->prepare("
                SELECT discount_percentage
                FROM promo_codes
                WHERE code = ? AND valid_until >= NOW() AND is_active = 1
            ");
            $stmt->execute([$promo_code]);
            $promo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($promo) {
                $discount = $total_price * ($promo['discount_percentage'] / 100);
            } else {
                error_log("Invalid or expired promo_code: $promo_code for user_id=$user_id");
            }
        }

        $total_amount = $total_price - $discount + $shipping;

        // Tạm thời tắt strict mode
        $pdo->exec("SET SESSION sql_mode = ''");

        // Tạo đơn hàng
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, status, payment_method, shipping, discount, promo_code, created_at, updated_at)
            VALUES (?, ?, 'confirmed', 'cod', ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$user_id, $total_amount, $shipping, $discount, $promo_code]);
        $order_id = $pdo->lastInsertId();

        // Thêm chi tiết đơn hàng và cập nhật kho
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            // Lấy giá sản phẩm
            $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            // Thêm vào order_items
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$order_id, $product_id, $quantity, $product['price']]);

            // Cập nhật kho
            $stmt = $pdo->prepare("
                UPDATE products
                SET stock = stock - ?
                WHERE id = ?
            ");
            $stmt->execute([$quantity, $product_id]);
        }

        // Xóa giỏ hàng
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Cam kết giao dịch
        $pdo->commit();

        error_log("Order created successfully: order_id=$order_id, user_id=$user_id, total_amount=$total_amount");
        return [
            'success' => true,
            'order_id' => $order_id,
            'message' => 'Đặt hàng thành công! Cảm ơn bạn đã mua sắm.',
            'redirect' => '../index.php?page=order_confirmation&order_id=' . $order_id
        ];
    } catch (Exception $e) {
        // Hoàn tác giao dịch nếu có lỗi
        $pdo->rollBack();
        error_log('Order processing failed for user_id=' . $user_id . ': ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Xử lý yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    // Log received data for debugging
    error_log('Received data: ' . json_encode($data));

    $payment_method = $data['payment_method'] ?? null;
    $shipping = isset($data['shipping']) ? (float)$data['shipping'] : 0;
    $promo_code = $data['promo_code'] ?? null;

    if ($payment_method !== 'cod') {
        error_log("Unsupported payment method: $payment_method for user_id=$user_id");
        echo json_encode(['success' => false, 'error' => 'Phương thức thanh toán không hỗ trợ']);
        exit;
    }

    // Xử lý đơn hàng COD
    $result = processCODOrder($pdo, $user_id, $shipping, $promo_code);

    if ($result['success']) {
        // Trả về JSON cho AJAX
        echo json_encode($result);
        exit;
    } else {
        // Trả về JSON nếu có lỗi
        http_response_code(400);
        echo json_encode($result);
        exit;
    }
}

// Phương thức không được hỗ trợ
http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Phương thức yêu cầu không được hỗ trợ']);
