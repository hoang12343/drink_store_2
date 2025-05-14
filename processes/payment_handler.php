<?php
// Bật ghi log lỗi
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/php_errors.log');

// Khởi động session
session_start();

// Kiểm tra đăng nhập
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ../index.php?page=login&error=' . urlencode('Chưa đăng nhập'));
    exit;
}

// Kết nối cơ sở dữ liệu
try {
    require_once '../includes/db_connect.php';
} catch (Exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    header('Location: ../index.php?page=checkout&error=' . urlencode('Lỗi kết nối cơ sở dữ liệu'));
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
            throw new Exception('Giỏ hàng trống');
        }

        $total_price = 0;

        // Kiểm tra sản phẩm và tính tổng giá
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            // Kiểm tra sản phẩm tồn tại
            $stmt = $pdo->prepare("SELECT price, stock FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Sản phẩm ID $product_id không tồn tại");
            }

            // Kiểm tra số lượng trong kho
            if ($product['stock'] < $quantity) {
                throw new Exception("Không đủ hàng cho sản phẩm ID $product_id");
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

        return [
            'success' => true,
            'order_id' => $order_id,
            'message' => 'Đặt hàng thành công! Cảm ơn bạn đã mua sắm.'
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Order processing failed: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Xử lý yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? null;
    $shipping = isset($_POST['shipping']) ? (float)$_POST['shipping'] : 0;
    $promo_code = $_POST['promo_code'] ?? null;
    $selected_items = isset($_POST['selected_items']) ? json_decode($_POST['selected_items'], true) : null;
    $total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;

    // Log received data
    error_log('Received data: ' . json_encode($_POST));

    // Kiểm tra dữ liệu đầu vào
    if (!$payment_method) {
        error_log("Invalid payment method: payment_method=$payment_method");
        header('Location: ../index.php?page=checkout&error=' . urlencode('Phương thức thanh toán không hợp lệ'));
        exit;
    }

    if ($payment_method === 'cod') {
        // Xử lý đơn hàng COD
        $result = processCODOrder($pdo, $user_id, $shipping, $promo_code);

        if ($result['success']) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
            header('Location: ../index.php?page=order_confirmation&order_id=' . $result['order_id']);
            exit;
        } else {
            header('Location: ../index.php?page=checkout&error=' . urlencode($result['error']));
            exit;
        }
    } elseif ($payment_method === 'zalopay') {
        // Kiểm tra dữ liệu ZaloPay
        if (empty($selected_items) || $total_amount <= 0) {
            error_log("Invalid ZaloPay input: selected_items=" . json_encode($selected_items) . ", total_amount=$total_amount");
            header('Location: ../index.php?page=checkout&error=' . urlencode('Dữ liệu đầu vào không hợp lệ'));
            exit;
        }

        // Gọi zalopay_payment.php trực tiếp
        try {
            $_POST['selected_items'] = json_encode($selected_items);
            $_POST['total_amount'] = $total_amount;
            $_POST['shipping'] = $shipping;
            $_POST['promo_code'] = $promo_code;

            ob_start();
            include dirname(__FILE__) . '/zalopay_payment.php';
            $response = ob_get_clean();

            $result = json_decode($response, true);
            if (!$result) {
                error_log("Invalid JSON response from zalopay_payment: " . $response);
                header('Location: ../index.php?page=checkout&error=' . urlencode('Lỗi xử lý thanh toán ZaloPay'));
                exit;
            }

            if (isset($result['success']) && $result['success'] && isset($result['order_url'])) {
                error_log("Redirecting to ZaloPay order_url: " . $result['order_url']);
                header('Location: ' . $result['order_url']);
                exit;
            } else {
                error_log("ZaloPay error: " . json_encode($result));
                header('Location: ../index.php?page=checkout&error=' . urlencode($result['error'] ?? 'Không thể tạo thanh toán ZaloPay'));
                exit;
            }
        } catch (Exception $e) {
            error_log("Error including zalopay_payment.php: " . $e->getMessage());
            header('Location: ../index.php?page=checkout&error=' . urlencode('Lỗi xử lý thanh toán ZaloPay: ' . $e->getMessage()));
            exit;
        }
    } else {
        error_log("Unsupported payment method: $payment_method for user_id=$user_id");
        header('Location: ../index.php?page=checkout&error=' . urlencode('Phương thức thanh toán không hỗ trợ'));
        exit;
    }
}

// Phương thức không được hỗ trợ
header('Location: ../index.php?page=checkout&error=' . urlencode('Phương thức yêu cầu không được hỗ trợ'));
