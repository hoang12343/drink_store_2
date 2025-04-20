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
    <title>Bảng điều khiển quản trị</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="assets/js/admin.js" defer></script>
</head>

<body>
    <section class="content admin-page">
        <h1>Bảng điều khiển quản trị</h1>
        <nav class="admin-nav">
            <a href="?page=admin&subpage=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="?page=admin&subpage=admin-products"><i class="fas fa-box"></i> Sản phẩm</a>
        </nav>
        <div class="admin-dashboard">
            <div class="dashboard-card">
                <h3>Quản lý sản phẩm</h3>
                <p>Xem, thêm, sửa, xóa sản phẩm trong hệ thống.</p>
                <a href="index.php?page=admin&subpage=admin-products" class="btn">Quản lý sản phẩm</a>
            </div>
        </div>
    </section>
</body>

</html>