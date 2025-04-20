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
    <link rel="stylesheet" href="assets/css/usermenu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="assets/js/usermenu.js" defer></script>
</head>

<body>
    <div class="user-menu" id="userMenu">
        <button class="user-toggle">
            <i class="fas fa-user"></i>
            <span class="username"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
            <i class="fas fa-caret-down"></i>
        </button>
        <div class="dropdown-menu">
            <a href="?page=profile"><i class="fas fa-user-circle"></i> Hồ sơ</a>
            <a href="?page=orders"><i class="fas fa-shopping-bag"></i> Đơn hàng</a>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <a href="?page=admin&subpage=dashboard"><i class="fas fa-cog"></i> Quản trị</a>
            <?php endif; ?>
            <a href="?page=logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>
</body>

</html>