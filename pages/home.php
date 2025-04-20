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
        'promotion' => ['name' => 'Sản phẩm khuyến mãi', 'description' => 'Ưu đãi đặc biệt, giảm giá hấp dẫn', 'image' => '/api/placeholder/220/180', 'link' => '?page=products&category=promotion'],
        'wine' => ['name' => 'Rượu vang', 'description' => 'Rượu vang cao cấp ', 'image' => '/api/placeholder/220/180', 'link' => '?page=products&category=wine'],
        'brandy' => ['name' => 'Rượu mạnh', 'description' => 'Cognac và Brandy thượng hạng', 'image' => '/api/placeholder/220/180', 'link' => '?page=products&category=brandy'],
        'vodka' => ['name' => 'Vodka', 'description' => 'Vodka tinh khiết từ Nga', 'image' => '/api/placeholder/220/180', 'link' => '?page=products&category=vodka'],
        'beer' => ['name' => 'Bia', 'description' => 'Bia nhập khẩu từ Bỉ, Hà Lan', 'image' => '/api/placeholder/220/180', 'link' => '?page=products&category=beer'],
        'gift' => ['name' => 'Quà tặng', 'description' => 'Hộp quà rượu sang trọng', 'image' => '/api/placeholder/220/180', 'link' => '?page=products&category=gift'],
        'crystal_glasses' => ['name' => 'Ly pha lê Riedel', 'description' => 'Ly pha lê cao cấp ', 'image' => '/api/placeholder/220/180', 'link' => '?page=products&category=crystal_glasses'],
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
?>

<?php include ROOT_PATH . './pages/banner.php'; ?>

<!-- Danh mục sản phẩm tổng hợp -->
<section class="home-content">
    <h2 class="category-title">Danh mục sản phẩm</h2>
    <div class="categories-grid">
        <?php foreach ($main_categories as $category => $title): ?>
            <?= display_category_card($category) ?>
        <?php endforeach; ?>
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