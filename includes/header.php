<?php // includes/header.php 
if (!defined('APP_START')) {
    exit('No direct access');
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa hàng đồ uống</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <div class="wrapper">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="container">
                <div class="contact-info">
                    <a href="tel:1900299232"><i class="fas fa-phone"></i> 1900 299 232</a>
                    <a href="mailto:contact@cuahangdouong.vn"><i class="fas fa-envelope"></i>
                        contact@cuahangdouong.vn</a>
                </div>
                <div class="user-actions">
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                    <a href="?page=account"><i class="fas fa-user-circle"></i> Tài khoản</a>
                    <a href="processes/logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                    <?php else: ?>
                    <a href="?page=login"><i class="fas fa-user"></i> Đăng nhập</a>
                    <a href="?page=register"><i class="fas fa-user-plus"></i> Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <header class="main-header">
            <div class="container">
                <div class="logo">
                    <a href="?page=home">Cửa hàng đồ uống</a>
                </div>

                <div class="search-bar">
                    <form action="index.php" method="get">
                        <input type="hidden" name="page" value="products">
                        <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." required>
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>

                <div class="header-actions">
                    <a href="?page=cart" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cartCount"
                            class="cart-count"><?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?></span>
                    </a>
                    <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Navigation Menu -->
        <nav class="main-navigation" id="navWrapper">
            <div class="container">
                <ul class="main-nav">
                    <?php
                    $main_menu_items = [
                        'home' => 'Trang chủ',
                        'products' => [
                            'label' => 'Sản phẩm',
                            'submenu' => [
                                'all' => 'Tất cả sản phẩm',
                                'wine' => 'Rượu vang',
                                'whisky' => 'Whisky',
                                'vodka' => 'Vodka',
                                'gin' => 'Gin',
                                'beer' => 'Bia',
                                'cocktail' => 'Cocktail',
                                'promotion' => 'Khuyến mãi'
                            ]
                        ],
                        'promotion' => 'Khuyến mãi',
                        'about' => 'Giới thiệu',
                        'contact' => 'Liên hệ'
                    ];

                    $current_page = isset($_GET['page']) ? $_GET['page'] : 'home';
                    $current_category = isset($_GET['category']) ? $_GET['category'] : 'all';

                    foreach ($main_menu_items as $key => $item) {
                        if (is_array($item)) {
                            // Menu item with submenu
                            $active = ($current_page === $key) ? 'active' : '';
                            echo "<li class='has-dropdown'>";
                            echo "<a href='?page=$key' class='dropdown-toggle $active'>{$item['label']} <span class='dropdown-indicator'><i class='fas fa-chevron-down'></i></span></a>";
                            echo "<ul class='dropdown-menu'>";

                            foreach ($item['submenu'] as $subKey => $subLabel) {
                                $subActive = ($current_page === $key && $current_category === $subKey) ? 'active' : '';
                                echo "<li><a href='?page=$key&category=$subKey' class='$subActive'>$subLabel</a></li>";
                            }

                            echo "</ul>";
                            echo "</li>";
                        } else {
                            // Regular menu item
                            $active = ($current_page === $key) ? 'active' : '';
                            echo "<li><a href='?page=$key' class='$active'>$item</a></li>";
                        }
                    }
                    ?>
                </ul>
            </div>
        </nav>

        <?php if ($current_page === 'home') include 'pages/banner.php'; ?>