<?php
if (!defined('APP_START')) exit('No direct access');
require_once 'includes/db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in'])) {
    header('Location: ../index.php?page=login&redirect=checkout');
    exit;
}

// Lấy danh sách sản phẩm được chọn từ giỏ hàng
$selected_items = [];
$total_amount = 0;
if (isset($_POST['selected_items'])) {
    $selected_item_ids = json_decode($_POST['selected_items'], true);
    if (!is_array($selected_item_ids) || empty($selected_item_ids)) {
        header('Location: ../index.php?page=cart&error=' . urlencode('Danh sách sản phẩm không hợp lệ'));
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($selected_item_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT ci.id, ci.product_id, ci.quantity, p.price, p.name
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ? AND ci.id IN ($placeholders)
    ");
    $stmt->execute(array_merge([$_SESSION['user_id']], $selected_item_ids));
    $selected_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($selected_items as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
}

// Nếu không có sản phẩm hoặc tổng tiền không hợp lệ, chuyển về giỏ hàng
if (empty($selected_items) || $total_amount <= 0) {
    header('Location: ../index.php?page=cart&error=' . urlencode('Vui lòng chọn sản phẩm để thanh toán'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
</head>

<body>
    <div class="checkout-page">
        <div class="container">
            <h2>Thanh toán đơn hàng</h2>
            <?php if (isset($_GET['error'])): ?>
                <div class="form-message error"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <h3>Sản phẩm thanh toán</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                        <th>Tổng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($selected_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['price'], 0, ',', '.') ?> VNĐ</td>
                            <td><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> VNĐ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="total-row final">
                <span>Tổng tiền:</span>
                <span><?= number_format($total_amount, 0, ',', '.') ?> VNĐ</span>
            </div>

            <form action="../processes/payment_handler.php" method="POST">
                <input type="hidden" name="selected_items"
                    value='<?= htmlspecialchars(json_encode(array_column($selected_items, 'id'))) ?>'>
                <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
                <button type="submit" class="btn btn-primary">Thanh toán qua ZaloPay</button>
            </form>
        </div>
    </div>
</body>

</html>