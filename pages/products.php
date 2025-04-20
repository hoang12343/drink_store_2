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
$page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT) ?? 1;
$page = max(1, (int)$page);

$filters = [
    'price_min' => filter_input(INPUT_GET, 'price_min', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]),
    'price_max' => filter_input(INPUT_GET, 'price_max', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]),
    'custom_price' => filter_input(INPUT_GET, 'custom_price', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]),
    'country' => filter_input(INPUT_GET, 'country', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
    'type' => filter_input(INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
    'volume' => filter_input(INPUT_GET, 'volume', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
    'grape' => filter_input(INPUT_GET, 'grape', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
];

$products_data = get_products($category, $search, $sort, 30, $filters, $page);
$products = $products_data['products'];
$total_products = $products_data['total_products'];
$products_per_page = 30;
$total_pages = ceil($total_products / $products_per_page);

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
                            <?php foreach ($_GET as $key => $value): ?>
                            <?php if ($key !== 'sort' && $value): ?>
                            <input type="hidden" name="<?= htmlspecialchars($key) ?>"
                                value="<?= htmlspecialchars($value) ?>">
                            <?php endif; ?>
                            <?php endforeach; ?>
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
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                            $query_params = $_GET;
                            unset($query_params['p']);
                            $base_url = 'index.php?' . http_build_query($query_params);
                            ?>

                    <?php if ($page > 1): ?>
                    <a href="<?= $base_url ?>&p=<?= $page - 1 ?>" class="pagination-btn prev">Trước</a>
                    <?php endif; ?>

                    <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);

                            if ($start_page > 1): ?>
                    <a href="<?= $base_url ?>&p=1" class="pagination-btn">1</a>
                    <?php if ($start_page > 2): ?>
                    <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="<?= $base_url ?>&p=<?= $i ?>" class="pagination-btn <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                    <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                    <a href="<?= $base_url ?>&p=<?= $total_pages ?>" class="pagination-btn"><?= $total_pages ?></a>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                    <a href="<?= $base_url ?>&p=<?= $page + 1 ?>" class="pagination-btn next">Sau</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>