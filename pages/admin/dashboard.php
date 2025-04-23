<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

require_once ROOT_PATH . '/includes/db_connect.php';

// Lấy thống kê kho hàng
try {
    // Tổng số sản phẩm
    $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
    $total_products = $stmt->fetchColumn();

    // Tổng số lượng tồn kho
    $stmt = $pdo->query("SELECT SUM(stock) as total_stock FROM products");
    $total_stock = $stmt->fetchColumn() ?: 0;

    // Số sản phẩm gần hết hàng (stock < 10)
    $stmt = $pdo->query("SELECT COUNT(*) as low_stock FROM products WHERE stock < 10");
    $low_stock = $stmt->fetchColumn();

    // Tổng số người dùng
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetchColumn();

    // Giả định dữ liệu cho đơn hàng (thay bằng truy vấn thực tế)
    $total_orders = 0; // Thay bằng truy vấn từ bảng orders
} catch (PDOException $e) {
    $error_message = 'Lỗi khi lấy dữ liệu: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển - Quản trị</title>
    <link rel="stylesheet" href="assets/css/admin/admin-variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-content.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="assets/js/admin.js" defer></script>
</head>

<body>
    <section class="content admin-page">
        <h1>Tổng quan</h1>

        <?php if (isset($error_message)): ?>
            <div class="form-message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="admin-dashboard">
            <div class="dashboard-card">
                <i class="fas fa-boxes"></i>
                <h3>Tổng sản phẩm</h3>
                <p><?= $total_products ?? 'N/A' ?></p>
                <a href="?page=admin&subpage=admin-products" class="btn">Xem chi tiết</a>
            </div>
            <div class="dashboard-card">
                <i class="fas fa-shopping-cart"></i>
                <h3>Tổng đơn hàng</h3>
                <p><?= $total_orders ?? 'N/A' ?></p>
                <a href="?page=admin&subpage=admin-orders" class="btn">Xem chi tiết</a>
            </div>
            <div class="dashboard-card">
                <i class="fas fa-users"></i>
                <h3>Tổng người dùng</h3>
                <p><?= $total_users ?? 'N/A' ?></p>
                <a href="?page=admin&subpage=admin-users" class="btn">Xem chi tiết</a>
            </div>
            <div class="dashboard-card">
                <i class="fas fa-warehouse"></i>
                <h3>Kho hàng</h3>
                <p>
                    Tổng tồn: <?= $total_stock ?><br>
                    Gần hết: <?= $low_stock ?? 0 ?>
                </p>
                <a href="?page=admin&subpage=admin-inventory" class="btn">Quản lý kho</a>
            </div>
        </div>
    </section>
</body>

</html>