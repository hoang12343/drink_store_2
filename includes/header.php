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
                    <a href="?page=about"><i class="fas fa-info-circle"></i> Giới thiệu</a>
                    <a href="?page=contact"><i class="fas fa-address-book"></i> Liên hệ</a>
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
                        'all' => 'Tất cả sản phẩm',
                        'wine' => 'Rượu vang',
                        'brandy' => 'Rượu mạnh',
                        'crystal' => 'Ly Pha Lê', // Thêm mục Ly Pha Lê vào menu chính
                        'vodka' => 'Vodka',
                        'beer' => 'Bia',


                        'promotion' => 'Khuyến mãi',
                        'knowledge' => 'Kiến thức',
                        'gift' => 'Quà tặng'
                    ];

                    // Sub-menu cho Rượu vang (3 cột dọc)
                    $wine_submenu = [
                        'type' => [
                            'label' => 'Theo loại rượu',
                            'items' => [
                                ['label' => 'Rượu vang đỏ', 'url' => '?page=products&category=wine&type=red', 'highlight' => true],
                                ['label' => 'Rượu vang trắng', 'url' => '?page=products&category=wine&type=white'],
                                ['label' => 'Rượu vang sủi (Champagne)', 'url' => '?page=products&category=wine&type=sparkling'],
                                ['label' => 'Rượu vang hồng', 'url' => '?page=products&category=wine&type=rose'],
                                ['label' => 'Rượu vang ngọt', 'url' => '?page=products&category=wine&type=sweet'],
                                ['label' => 'Rượu vang cường hóa', 'url' => '?page=products&category=wine&type=fortified'],
                                ['label' => 'Rượu vang không cồn', 'url' => '?page=products&category=wine&type=non-alcoholic'],
                                ['label' => 'Rượu vang Organic', 'url' => '?page=products&category=wine&type=organic'],
                                ['label' => 'Tất cả rượu vang', 'url' => '?page=products&category=wine', 'is_all' => true]
                            ]
                        ],
                        'country' => [
                            'label' => 'Theo quốc gia',
                            'items' => [
                                ['label' => 'Rượu vang Pháp', 'url' => '?page=products&category=wine&country=france'],
                                ['label' => 'Rượu vang Ý', 'url' => '?page=products&category=wine&country=italy'],
                                ['label' => 'Rượu vang Tây Ban Nha', 'url' => '?page=products&category=wine&country=spain'],
                                ['label' => 'Rượu vang Chile', 'url' => '?page=products&category=wine&country=chile'],
                                ['label' => 'Rượu vang Mỹ', 'url' => '?page=products&category=wine&country=usa'],
                                ['label' => 'Rượu vang Úc', 'url' => '?page=products&category=wine&country=australia'],
                                ['label' => 'Rượu vang New Zealand', 'url' => '?page=products&category=wine&country=new-zealand'],
                                ['label' => 'Rượu vang Argentina', 'url' => '?page=products&category=wine&country=argentina'],
                                ['label' => 'Rượu vang Bồ Đào Nha', 'url' => '?page=products&category=wine&country=portugal'],
                                ['label' => 'Rượu vang Đức', 'url' => '?page=products&category=wine&country=germany'],
                                ['label' => 'Rượu vang Nam Phi', 'url' => '?page=products&category=wine&country=south-africa']
                            ]
                        ],
                        'grape' => [
                            'label' => 'Theo giống nho',
                            'items' => [
                                ['label' => 'Cabernet Sauvignon', 'url' => '?page=products&category=wine&grape=cabernet-sauvignon'],
                                ['label' => 'Merlot', 'url' => '?page=products&category=wine&grape=merlot'],
                                ['label' => 'Syrah (Shiraz)', 'url' => '?page=products&category=wine&grape=syrah'],
                                ['label' => 'Pinot Noir', 'url' => '?page=products&category=wine&grape=pinot-noir'],
                                ['label' => 'Malbec', 'url' => '?page=products&category=wine&grape=malbec'],
                                ['label' => 'Montepulciano D’Abruzzo', 'url' => '?page=products&category=wine&grape=montepulciano'],
                                ['label' => 'Negramaro', 'url' => '?page=products&category=wine&grape=negramaro'],
                                ['label' => 'Primitivo', 'url' => '?page=products&category=wine&grape=primitivo'],
                                ['label' => 'Chardonnay', 'url' => '?page=products&category=wine&grape=chardonnay'],
                                ['label' => 'Sauvignon Blanc', 'url' => '?page=products&category=wine&grape=sauvignon-blanc'],
                                ['label' => 'Riesling', 'url' => '?page=products&category=wine&grape=riesling'],
                                ['label' => 'Tìm giống nho', 'url' => '?page=products&category=wine&grape=search', 'is_search' => true]
                            ]
                        ]
                    ];

                    // Sub-menu cho Rượu mạnh (3 cột dọc)
                    $brandy_submenu = [
                        'type' => [
                            'label' => 'Loại rượu',
                            'items' => [
                                ['label' => 'Rượu Whisky', 'url' => '?page=products&category=brandy&type=whisky', 'highlight' => true],
                                ['label' => 'Rượu Cognac', 'url' => '?page=products&category=brandy&type=cognac'],
                                ['label' => 'Rượu Rum', 'url' => '?page=products&category=brandy&type=rum'],
                                ['label' => 'Rượu Gin', 'url' => '?page=products&category=brandy&type=gin'],
                                ['label' => 'Rượu Vermouth', 'url' => '?page=products&category=brandy&type=vermouth'],
                                ['label' => 'Tất cả rượu mạnh', 'url' => '?page=products&category=brandy', 'is_all' => true]
                            ]
                        ],
                        'brand' => [
                            'label' => 'Thương hiệu',
                            'items' => [
                                ['label' => 'GlenAllachie', 'url' => '?page=products&category=brandy&brand=glenallachie'],
                                ['label' => 'Tamdhu', 'url' => '?page=products&category=brandy&brand=tamdhu'],
                                ['label' => 'Glengoyne', 'url' => '?page=products&category=brandy&brand=glengoyne'],
                                ['label' => 'Kilchoman', 'url' => '?page=products&category=brandy&brand=kilchoman'],
                                ['label' => 'Meikle Tòir', 'url' => '?page=products&category=brandy&brand=meikle-toir'],
                                ['label' => 'Glen Moray', 'url' => '?page=products&category=brandy&brand=glen-moray'],
                                ['label' => 'Thomas Hine & Co', 'url' => '?page=products&category=brandy&brand=thomas-hine'],
                                ['label' => 'Cognac Lhéraud', 'url' => '?page=products&category=brandy&brand=cognac-lheraud'],
                                ['label' => 'Rosebank', 'url' => '?page=products&category=brandy&brand=rosebank']
                            ]
                        ],
                        'country' => [
                            'label' => 'Quốc gia',
                            'items' => [
                                ['label' => 'Hunter Laing – That Boutique-Y Whisky Company', 'url' => '?page=products&category=brandy&country=hunter-laing'],
                                ['label' => 'Kill Devil', 'url' => '?page=products&category=brandy&country=kill-devil'],
                                ['label' => 'Cadenhead’s', 'url' => '?page=products&category=brandy&country=cadenheads'],
                                ['label' => 'The Ileach', 'url' => '?page=products&category=brandy&country=the-ileach'],
                                ['label' => 'The Original Islay Rum', 'url' => '?page=products&category=brandy&country=original-islay-rum'],
                                ['label' => 'Silver Seal', 'url' => '?page=products&category=brandy&country=silver-seal'],
                                ['label' => 'MacNair’s', 'url' => '?page=products&category=brandy&country=macnairs']
                            ]
                        ]
                    ];

                    // Sub-menu cho Ly Pha Lê (2 cột dọc)
                    $crystal_submenu = [
                        'type' => [
                            'label' => 'Theo loại ly',
                            'items' => [
                                ['label' => 'Ly Vang Đỏ', 'url' => '?page=products&category=crystal&type=red-wine', 'highlight' => true],
                                ['label' => 'Ly Vang Trắng', 'url' => '?page=products&category=crystal&type=white-wine'],
                                ['label' => 'Ly Champagne', 'url' => '?page=products&category=crystal&type=champagne'],
                                ['label' => 'Ly Rượu Mạnh', 'url' => '?page=products&category=crystal&type=brandy'],
                                ['label' => 'Ly Cocktail', 'url' => '?page=products&category=crystal&type=cocktail'],
                                ['label' => 'Cốc Nước', 'url' => '?page=products&category=crystal&type=water-glass'],
                                ['label' => 'Ly Chân Màu', 'url' => '?page=products&category=crystal&type=colored-stem'],
                                ['label' => 'Ly Machine-made', 'url' => '?page=products&category=crystal&type=machine-made'],
                                ['label' => 'Ly Thủ Công Cao Cấp', 'url' => '?page=products&category=crystal&type=handmade-premium'],
                                ['label' => 'Tất cả ly pha lê', 'url' => '?page=products&category=crystal', 'is_all' => true]
                            ]
                        ],
                        'brand' => [
                            'label' => 'Theo thương hiệu',
                            'items' => [
                                ['label' => 'Ly Whisky Riedel', 'url' => '?page=products&category=crystal&brand=riedel'],
                                ['label' => 'Ly Whisky Glencairn', 'url' => '?page=products&category=crystal&brand=glencairn'],
                                ['label' => 'Bình Whisky', 'url' => '?page=products&category=crystal&brand=whisky-decanter']
                            ]
                        ]
                    ];

                    $current_page = isset($_GET['page']) ? $_GET['page'] : 'home';

                    foreach ($main_menu_items as $key => $label) {
                        $active = ($current_page === $key) ? 'active' : '';
                        $has_dropdown = ($key === 'wine' || $key === 'brandy' || $key === 'crystal') ? 'has-dropdown' : '';

                        // Nếu là các mục sản phẩm, thêm category vào URL
                        if ($key !== 'home' && $key !== 'promotion' && $key !== 'knowledge') {
                            echo "<li class='$has_dropdown'><a href='?page=products&category=$key' class='$active'>$label</a>";
                        } else {
                            echo "<li class='$has_dropdown'><a href='?page=$key' class='$active'>$label</a>";
                        }

                        // Thêm sub-menu cho Rượu vang
                        if ($key === 'wine') {
                            echo "<div class='dropdown-menu wine-dropdown'>";
                            foreach ($wine_submenu as $sub_key => $sub_column) {
                                echo "<div class='dropdown-column'>";
                                echo "<h4>" . htmlspecialchars($sub_column['label']) . "</h4>";
                                echo "<ul>";
                                foreach ($sub_column['items'] as $item) {
                                    $highlight_class = isset($item['highlight']) && $item['highlight'] ? 'highlight' : '';
                                    $all_class = isset($item['is_all']) && $item['is_all'] ? 'all-link' : '';
                                    $search_class = isset($item['is_search']) && $item['is_search'] ? 'search-link' : '';
                                    echo "<li><a href='" . htmlspecialchars($item['url']) . "' class='$highlight_class $all_class $search_class'>" . htmlspecialchars($item['label']) . "</a></li>";
                                }
                                echo "</ul>";
                                echo "</div>";
                            }
                            echo "</div>";
                        }

                        // Thêm sub-menu cho Rượu mạnh
                        if ($key === 'brandy') {
                            echo "<div class='dropdown-menu brandy-dropdown'>";
                            foreach ($brandy_submenu as $sub_key => $sub_column) {
                                echo "<div class='dropdown-column'>";
                                echo "<h4>" . htmlspecialchars($sub_column['label']) . "</h4>";
                                echo "<ul>";
                                foreach ($sub_column['items'] as $item) {
                                    $highlight_class = isset($item['highlight']) && $item['highlight'] ? 'highlight' : '';
                                    $all_class = isset($item['is_all']) && $item['is_all'] ? 'all-link' : '';
                                    echo "<li><a href='" . htmlspecialchars($item['url']) . "' class='$highlight_class $all_class'>" . htmlspecialchars($item['label']) . "</a></li>";
                                }
                                echo "</ul>";
                                echo "</div>";
                            }
                            echo "</div>";
                        }

                        // Thêm sub-menu cho Ly Pha Lê
                        if ($key === 'crystal') {
                            echo "<div class='dropdown-menu crystal-dropdown'>";
                            foreach ($crystal_submenu as $sub_key => $sub_column) {
                                echo "<div class='dropdown-column'>";
                                echo "<h4>" . htmlspecialchars($sub_column['label']) . "</h4>";
                                echo "<ul>";
                                foreach ($sub_column['items'] as $item) {
                                    $highlight_class = isset($item['highlight']) && $item['highlight'] ? 'highlight' : '';
                                    $all_class = isset($item['is_all']) && $item['is_all'] ? 'all-link' : '';
                                    echo "<li><a href='" . htmlspecialchars($item['url']) . "' class='$highlight_class $all_class'>" . htmlspecialchars($item['label']) . "</a></li>";
                                }
                                echo "</ul>";
                                echo "</div>";
                            }
                            echo "</div>";
                        }

                        echo "</li>";
                    }
                    ?>
                </ul>
            </div>
        </nav>

        <?php if ($current_page === 'home') include 'pages/banner.php'; ?>