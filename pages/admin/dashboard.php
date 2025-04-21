<?php
if (!defined('APP_START')) {
    exit('No direct access');
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

        <div class="admin-dashboard">
            <div class="dashboard-card">
                <h3>Tổng sản phẩm</h3>
                <p>Đang tải dữ liệu...</p>
                <a href="?page=admin&subpage=admin-products" class="btn">Xem chi tiết</a>
            </div>
            <div class="dashboard-card">
                <h3>Tổng đơn hàng</h3>
                <p>Đang tải dữ liệu...</p>
                <a href="?page=admin&subpage=admin-orders" class="btn">Xem chi tiết</a>
            </div>
            <div class="dashboard-card">
                <h3>Tổng người dùng</h3>
                <p>Đang tải dữ liệu...</p>
                <a href="?page=admin&subpage=admin-users" class="btn">Xem chi tiết</a>
            </div>
        </div>
    </section>
</body>

</html>