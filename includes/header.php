<?php
if (!defined('APP_START')) exit('No direct access');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa hàng đồ uống - <?php echo ucfirst($current_page ?? 'home'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php if ($current_page === 'cart'): ?>
        <link rel="stylesheet" href="assets/css/cart.css?v=<?php echo time(); ?>">
    <?php elseif (str_starts_with($current_page, 'admin/')): ?>
        <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo time(); ?>">
    <?php elseif ($current_page === 'home'): ?>

    <?php elseif ($current_page === 'products'): ?>
        <link rel="stylesheet" href="assets/css/products.css?v=<?php echo time(); ?>">
    <?php endif; ?>
    <script src="assets/js/script.js" defer></script>
    <?php if ($current_page === 'home'): ?>

    <?php elseif ($current_page === 'products'): ?>
        <script src="assets/js/products.js" defer></script>
    <?php endif; ?>
</head>

<body>
    <div class="wrapper">
        <div class="top-bar">
            <div class="container">
                <div class="contact-info">
                    <a href="tel:1900299232"><i class="fas fa-phone"></i> 1900 299 232</a>
                    <a href="mailto:contact@cuahangdouong.vn"><i class="fas fa-envelope"></i>
                        contact@cuahangdouong.vn</a>
                    <a href="?page=about"><i class="fas fa-info-circle"></i> Giới thiệu</a>
                    <a href="?page=contact"><i class="fas fa-address-book"></i> Liên hệ</a>
                </div>
                <div class="auth-links">
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <?php
                        $usermenu_file = __DIR__ . '/usermenu.php';
                        if (file_exists($usermenu_file)) {
                            include $usermenu_file;
                        } else {
                            echo '<a href="?page=profile"><i class="fas fa-user"></i> ' . htmlspecialchars($_SESSION['username'] ?? 'User') . '</a>';
                            echo '<a href="?page=logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>';
                        }
                        ?>
                    <?php else: ?>
                        <a href="?page=login"><i class="fas fa-user"></i> Đăng nhập</a>
                        <a href="?page=register"><i class="fas fa-user-plus"></i> Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Phần còn lại của header.php giữ nguyên -->
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
                        <span id="cartCount" class="cart-count">
                            <?php
                            $total_items = 0;
                            if (isset($_SESSION['user_id'])) {
                                $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                $total_items = $stmt->fetch()['total'] ?? 0;
                            }
                            echo $total_items;
                            ?>
                        </span>
                    </a>
                    <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </header>

        <nav class="main-navigation" id="navWrapper">
            <div class="container">
                <ul class="main-nav">
                    <?php
                    $main_menu_items = [
                        'home' => 'Trang chủ',
                        'all' => 'Tất cả sản phẩm',
                        'wine' => 'Rượu vang',
                        'brandy' => 'Rượu mạnh',
                        'crystal' => 'Ly Pha Lê',
                        'vodka' => 'Vodka',
                        'beer' => 'Bia',
                        'promotion' => 'Khuyến mãi',
                        'knowledge' => 'Kiến thức',
                        'gift' => 'Quà tặng'
                    ];
                    foreach ($main_menu_items as $key => $label) {
                        $active = ($current_page === $key || ($current_page === 'products' && isset($_GET['category']) && $_GET['category'] === $key)) ? 'active' : '';
                        $url = in_array($key, ['home', 'promotion', 'knowledge']) ? "?page=$key" : "?page=products&category=$key";
                        echo "<li><a href='$url' class='$active'>$label</a></li>";
                    }
                    ?>
                </ul>
            </div>
        </nav>


    </div>
</body>

</html>