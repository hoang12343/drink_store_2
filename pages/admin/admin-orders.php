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
    <title>Quản lý <?php echo $admin_subpage === 'admin-orders' ? 'Đơn hàng' : 'Người dùng'; ?></title>
    <link rel="stylesheet" href="assets/css/admin/admin-variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-content.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-products.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="assets/js/admin.js" defer></script>
</head>

<body>
    <section class="content admin-page">
        <h1>Quản lý <?php echo $admin_subpage === 'admin-orders' ? 'Đơn hàng' : 'Người dùng'; ?></h1>
        <div class="admin-products">
            <p>Chức năng đang được phát triển. Vui lòng quay lại sau.</p>
            <a href="index.php?page=admin&subpage=dashboard" class="btn">Quay lại bảng điều khiển</a>
        </div>
    </section>
</body>

</html>