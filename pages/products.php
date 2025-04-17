<?php
if (!defined('APP_START')) {
    exit('No direct access');
}
?>

<link rel="stylesheet" href="assets/css/products.css">
<script src="assets/js/products.js" defer></script>

<?php
require_once 'utils/product-functions.php';
require_once 'components/product-card.php';

$category = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'all';
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'default';

$filters = [
    'price_min' => filter_input(INPUT_GET, 'price_min', FILTER_SANITIZE_NUMBER_INT),
    'price_max' => filter_input(INPUT_GET, 'price_max', FILTER_SANITIZE_NUMBER_INT),
    'custom_price' => filter_input(INPUT_GET, 'custom_price', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
    'country' => filter_input(INPUT_GET, 'country', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
    'type' => filter_input(INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
    'volume' => filter_input(INPUT_GET, 'volume', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
    'grape' => filter_input(INPUT_GET, 'grape', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
];

$products = get_products($category, $search, $sort, 12, $filters);
$total_products = count($products);

// Đồng bộ danh mục với home.php và cơ sở dữ liệu
$categories = [
    'all' => 'Tất cả sản phẩm',
    'promotion' => 'Sản phẩm khuyến mãi',
    'wine' => 'Rượu vang nhập khẩu',
    'brandy' => 'Rượu mạnh',
    'crystal_glasses' => 'Ly pha lê',
    'whisky' => 'Whisky',
    'vodka' => 'Vodka',
    'beer' => 'Bia',
    'cocktail' => 'Cocktail',
    'gift' => 'Quà tặng'
];

$sort_options = [
    'default' => 'Mặc định',
    'price_asc' => 'Giá tăng dần',
    'price_desc' => 'Giá giảm dần',
    'name_asc' => 'Tên A-Z',
    'name_desc' => 'Tên Z-A',
    'rating' => 'Đánh giá cao nhất'
];
?>

<section class="content products-page wine-page">
    <div class="products-layout">
        <?php include_once 'components/filter-bar.php'; ?>

        <div class="right-content">
            <div class="products-header">
                <h1>
                    <?= $search ? "Kết quả tìm kiếm cho: " . htmlspecialchars($search) : htmlspecialchars($categories[$category] ?? 'Sản phẩm') ?>
                </h1>
                <div class="filter-container">
                    <div class="products-count">
                        <span><?= $total_products ?> sản phẩm</span>
                    </div>
                    <div class="filter-options">
                        <form action="index.php" method="get" class="sort-form">
                            <input type="hidden" name="page" value="products">
                            <?php if ($category !== 'all'): ?>
                                <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                            <?php endif; ?>
                            <?php if ($search): ?>
                                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                            <?php endif; ?>
                            <label for="sort">Sắp xếp:</label>
                            <select name="sort" id="sort" onchange="this.form.submit()">
                                <?php foreach ($sort_options as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $sort === $key ? 'selected' : '' ?>><?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="no-products">
                    <p>Không tìm thấy sản phẩm phù hợp.</p>
                    <a href="?page=products" class="btn">Xem tất cả sản phẩm</a>
                </div>
            <?php else: ?>
                <div class="products-container">
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <?= display_product($product) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>