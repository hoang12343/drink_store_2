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
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="assets/js/admin.js" defer></script>
</head>

<body>
    <section class="content admin-page">
        <h1>Quản lý sản phẩm</h1>
        <nav class="admin-nav">
            <a href="?page=admin&subpage=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="?page=admin&subpage=admin-products"><i class="fas fa-box"></i> Sản phẩm</a>
        </nav>
        <div class="admin-products">
            <p>Chức năng quản lý sản phẩm đang được phát triển. Vui lòng quay lại sau.</p>
            <a href="index.php?page=admin&subpage=dashboard" class="btn">Quay lại bảng điều khiển</a>
        </div>
    </section>
</body>

</html>