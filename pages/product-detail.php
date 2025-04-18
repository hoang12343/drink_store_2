<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

require_once 'utils/product-functions.php';
require_once 'components/product-card.php';

$product_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$product = get_product_by_id($product_id);

if (!$product) {
    http_response_code(404);
    include 'pages/404.php';
    exit;
}
?>

<link rel="stylesheet" href="assets/css/product-detail.css?v=<?= time() ?>">
<script src="assets/js/product-detail.js?v=<?= time() ?>" defer></script>

<section class="content product-detail-page">
    <div class="product-detail-layout">
        <!-- Product Images -->
        <div class="product-images">
            <div class="main-image">
                <img src="<?= htmlspecialchars($product['image'] ?? 'assets/images/placeholder.jpg') ?>"
                    alt="<?= htmlspecialchars($product['name'] ?? 'Sản phẩm') ?>">
            </div>
        </div>

        <!-- Product Information -->
        <div class="product-info">
            <h1 class="product-name"><?= htmlspecialchars($product['name'] ?? 'Sản phẩm') ?></h1>

            <!-- Rating -->
            <div class="product-rating">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <i class="fas fa-star <?= $i < ($product['rating'] ?? 5) ? 'filled' : '' ?>"></i>
                <?php endfor; ?>
                <span>(<?= $product['reviews'] ?? 0 ?> bình chọn)</span>
            </div>

            <!-- Product Labels -->
            <div class="product-labels">
                <div class="label-item">
                    <i class="fas fa-wine-bottle"></i>
                    <span>Loại: <?= htmlspecialchars($product['type'] ?? 'N/A') ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-globe"></i>
                    <span>Quốc gia: <?= htmlspecialchars($product['country'] ?? 'N/A') ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-percentage"></i>
                    <span>Nồng độ: <?= htmlspecialchars($product['abv'] ?? 'N/A') ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-tint"></i>
                    <span>Dung tích: <?= htmlspecialchars($product['volume'] ?? '750ml') ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-leaf"></i>
                    <span>Giống nho: <?= htmlspecialchars($product['grape'] ?? 'N/A') ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-building"></i>
                    <span>Nhà sản xuất: <?= htmlspecialchars($product['brand'] ?? 'N/A') ?></span>
                </div>
            </div>

            <!-- Price -->
            <div class="product-price">
                <?php if (!empty($product['display_old_price'])): ?>
                    <span class="old-price"><?= htmlspecialchars($product['display_old_price']) ?></span>
                <?php endif; ?>
                <span class="current-price"><?= htmlspecialchars($product['display_price'] ?? 'Liên hệ') ?></span>
            </div>

            <!-- Stock Status -->
            <div class="stock-status">
                <span>Còn hàng: <?= ($product['stock'] ?? 0) > 0 ? $product['stock'] : 'Hết hàng' ?> sản phẩm</span>
            </div>

            <!-- Quantity Selector and Actions -->
            <div class="product-actions">
                <div class="quantity-selector">
                    <button class="quantity-btn decrease">-</button>
                    <input type="number" value="1" min="1" max="<?= $product['stock'] ?? 10 ?>" class="quantity-input">
                    <button class="quantity-btn increase">+</button>
                </div>
                <button class="add-to-cart-btn" data-product-code="<?= htmlspecialchars($product['id']) ?>"
                    <?= ($product['stock'] ?? 0) <= 0 ? 'disabled' : '' ?>>
                    Thêm vào giỏ hàng
                </button>
                <button class="buy-now-btn" data-product-code="<?= htmlspecialchars($product['id']) ?>"
                    <?= ($product['stock'] ?? 0) <= 0 ? 'disabled' : '' ?>>
                    Mua ngay
                </button>
            </div>

            <!-- Additional Info -->
            <div class="additional-info">
                <ul>
                    <?php
                    $additional_info = $product['additional_info'] ?? [
                        'Giá sản phẩm đã bao gồm VAT',
                        'Phí giao hàng tùy theo từng khu vực.',
                        'Đơn hàng từ 1.000.000 vnd miễn phí giao hàng.'
                    ];
                    foreach ($additional_info as $info): ?>
                        <li><?= htmlspecialchars($info) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Product Description -->
    <div class="product-description-section">
        <h2>Mô tả sản phẩm</h2>
        <p><?= htmlspecialchars($product['description'] ?? 'Không có mô tả') ?></p>
    </div>

    <!-- Related Products -->
    <div class="related-products-section">
        <h2>Sản phẩm liên quan</h2>
        <div class="products-grid">
            <?php
            $related_products = get_related_products($product['id'], 4);
            foreach ($related_products as $related_product) {
                echo display_product($related_product);
            }
            ?>
        </div>
    </div>
</section>