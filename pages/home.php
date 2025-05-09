<?php
if (!defined('APP_START')) {
    exit('No direct access');
}
?>
<link rel="stylesheet" href="assets/css/home.css">

<?php
// Hàm lấy thông tin danh mục (mô phỏng sản phẩm tiêu biểu hoặc thông tin tổng quan)
function get_category_info($category)
{
    $category_data = [
        'promotion' => ['name' => 'Sản phẩm khuyến mãi', 'description' => 'Ưu đãi đặc biệt, giảm giá hấp dẫn', 'image' => 'assets/image/champange-phap.jpg', 'link' => '?page=products&category=promotion'],
        'wine' => ['name' => 'Rượu vang', 'description' => 'Rượu vang cao cấp', 'image' => 'assets/image/ruou-vang-home.jpg', 'link' => '?page=products&category=wine'],
        'brandy' => ['name' => 'Rượu mạnh', 'description' => 'Cognac và Brandy thượng hạng', 'image' => 'assets/image/ruou-manh-home.jpg', 'link' => '?page=products&category=brandy'],
        'vodka' => ['name' => 'Vodka', 'description' => 'Vodka tinh khiết từ Nga', 'image' => 'assets/image/vodka-home.jpg', 'link' => '?page=products&category=vodka'],
        'beer' => ['name' => 'Bia', 'description' => 'Bia nhập khẩu từ Bỉ, Hà Lan', 'image' => 'assets/image/bia-home.jpg', 'link' => '?page=products&category=beer'],
        'gift' => ['name' => 'Quà tặng', 'description' => 'Hộp quà rượu sang trọng', 'image' => '/api/placeholder/220/180', 'link' => '?page=products&category=gift'],
        'crystal_glasses' => ['name' => 'Ly pha lê Riedel', 'description' => 'Ly pha lê cao cấp', 'image' => 'assets/image/ly-pha-le-riedel-home.jpg', 'link' => '?page=products&category=crystal_glasses'],
    ];

    return $category_data[$category] ?? null;
}

// Hàm hiển thị card danh mục
function display_category_card($category): string
{
    $info = get_category_info($category);
    if (!$info) return '';

    ob_start();
?>
    <article class="category-card">
        <div class="category-img">
            <img src="<?= htmlspecialchars($info['image']) ?>" alt="<?= htmlspecialchars($info['name']) ?>" loading="lazy">
        </div>
        <div class="category-info">
            <h3><?= htmlspecialchars($info['name']) ?></h3>
            <p><?= htmlspecialchars($info['description']) ?></p>
            <a href="<?= htmlspecialchars($info['link']) ?>" class="view-btn">Khám phá ngay</a>
        </div>
    </article>
<?php
    return ob_get_clean();
}

// Danh mục chính (đồng bộ với main-navigation)
$main_categories = [
    'promotion' => 'Sản phẩm khuyến mãi',
    'wine' => 'Rượu vang nhập khẩu',
    'brandy' => 'Rượu mạnh',
    'crystal_glasses' => 'Ly pha lê',
    'whisky' => 'Whisky',
    'vodka' => 'Vodka',
    'beer' => 'Bia',
    'cocktail' => 'Cocktail',
];

// Lấy danh sách sản phẩm bán chạy
require_once 'utils/product-functions.php';
require_once 'components/product-card.php';
$best_selling_products = get_best_selling_products();
?>

<?php include ROOT_PATH . '/pages/banner.php'; ?>

<!-- Danh mục sản phẩm tổng hợp -->
<section class="home-content">
    <h2 class="category-title">Danh mục sản phẩm</h2>
    <div class="categories-grid">
        <?php foreach ($main_categories as $category => $title): ?>
            <?= display_category_card($category) ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- Phần Sản phẩm bán chạy -->
<section class="best-sellers">
    <div class="home-content">
        <h2 class="category-title">Sản phẩm bán chạy</h2>
        <div class="products-grid">
            <?php foreach ($best_selling_products as $product): ?>
                <a href="index.php?page=product-detail&id=<?= $product['id'] ?>" class="product-card clickable">
                    <div class="product-content">
                        <div class="product-img">
                            <img src="<?= htmlspecialchars($product['image'] ?? 'assets/images/placeholder.jpg') ?>"
                                alt="<?= htmlspecialchars($product['name'] ?? 'Sản phẩm') ?>">
                        </div>
                        <div class="product-info">
                            <div class="product-labels">
                                <?php if (!empty($product['grape'])): ?>
                                    <div class="label-item"><i
                                            class="fas fa-leaf"></i><?= htmlspecialchars($product['grape']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($product['type'])): ?>
                                    <div class="label-item"><i
                                            class="fas fa-wine-glass"></i><?= htmlspecialchars($product['type']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($product['brand'])): ?>
                                    <div class="label-item"><i
                                            class="fas fa-star"></i><?= htmlspecialchars($product['brand']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($product['country'])): ?>
                                    <div class="label-item"><i
                                            class="fas fa-globe"></i><?= htmlspecialchars($product['country']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($product['abv'])): ?>
                                    <div class="label-item"><i
                                            class="fas fa-percentage"></i><?= htmlspecialchars($product['abv']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="product-details">
                        <div class="product-name"><?= htmlspecialchars($product['name'] ?? 'Sản phẩm') ?></div>
                        <div class="product-description">
                            <?= htmlspecialchars($product['description'] ?? 'Không có mô tả') ?></div>
                    </div>
                    <div class="price-and-action">
                        <div class="product-price">
                            <?= htmlspecialchars($product['display_price'] ?? 'Liên hệ') ?>
                            <?php if (!empty($product['old_price'])): ?>
                                <span class="old-price"><?= htmlspecialchars($product['old_price']) ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="buy-now-btn">Mua ngay</button>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Tính năng nổi bật -->
<section class="features">
    <div class="container">
        <div class="feature-item">
            <i class="fas fa-shipping-fast"></i>
            <h3>Giao hàng nhanh chóng</h3>
            <p>Miễn phí giao hàng cho đơn từ 1.000.000₫</p>
        </div>
        <div class="feature-item">
            <i class="fas fa-wine-bottle"></i>
            <h3>Sản phẩm chất lượng</h3>
            <p>Cam kết hàng chính hãng, nhập khẩu trực tiếp</p>
        </div>
        <div class="feature-item">
            <i class="fas fa-certificate"></i>
            <h3>Bảo đảm hoàn tiền</h3>
            <p>Hoàn tiền 100% nếu không đúng cam kết</p>
        </div>
        <div class="feature-item">
            <i class="fas fa-headset"></i>
            <h3>Hỗ trợ 24/7</h3>
            <p>Đội ngũ hỗ trợ chuyên nghiệp, nhiệt tình</p>
        </div>
    </div>
</section>