<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

// Include necessary utilities
require_once 'utils/product-functions.php';

// Get the product ID from the URL
$product_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$product = get_product_by_id($product_id); // Assume this function fetches product details from the database

if (!$product) {
    http_response_code(404);
    include 'pages/404.php';
    exit;
}

// Product details (mock data based on the image)
$product = [
    'id' => $product_id,
    'name' => 'Rượu Vang Ý 60 Sessantanni Limited Edition (24 Karat Gold)',
    'description' => 'Rượu nồng nàn hương thơm trái cây hoa quý hiếm cùng mức mặn, anh đào. Chất vị vang tròn đầy, căng bằng, vị chất mềm mượt. Hậu vị phảng phất hương cacao, cà phê và vani đầy lưu luyến.',
    'price' => 1870000,
    'old_price' => null,
    'image' => 'path/to/sessantanni-image.jpg', // Replace with actual image path
    'grape' => 'Primitivo',
    'type' => 'Rượu Vang Đỏ',
    'country' => 'Vang Ý (Italy)',
    'alcohol_content' => '15.5% ABV*',
    'volume' => '750ml',
    'vintage' => '2018',
    'producer' => 'San Marzano',
    'rating' => 5,
    'reviews' => 3,
    'stock' => 10, // Example stock quantity
    'additional_info' => [
        'Giá sản phẩm đã bao gồm VAT',
        'Phí giao hàng tùy theo từng khu vực.',
        'Đơn hàng từ 1.000.000 vnd miễn phí giao hàng.'
    ]
];
?>

<link rel="stylesheet" href="assets/css/product-detail.css">
<script src="assets/js/product-detail.js" defer></script>

<section class="content product-detail-page">
    <div class="product-detail-layout">
        <!-- Product Images -->
        <div class="product-images">
            <div class="main-image">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div class="thumbnail-gallery">
                <!-- Thumbnails for additional images (mocked) -->
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="Thumbnail 1" class="thumbnail active">
                <img src="path/to/sessantanni-image-2.jpg" alt="Thumbnail 2" class="thumbnail">
                <img src="path/to/sessantanni-image-3.jpg" alt="Thumbnail 3" class="thumbnail">
            </div>
        </div>

        <!-- Product Information -->
        <div class="product-info">
            <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>

            <!-- Rating -->
            <div class="product-rating">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <i class="fas fa-star <?= $i < $product['rating'] ? 'filled' : '' ?>"></i>
                <?php endfor; ?>
                <span>(<?= $product['reviews'] ?> bình chọn)</span>
            </div>

            <!-- Product Labels -->
            <div class="product-labels">
                <div class="label-item">
                    <i class="fas fa-wine-bottle"></i>
                    <span>Loại: <?= htmlspecialchars($product['type']) ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-globe"></i>
                    <span>Quốc gia: <?= htmlspecialchars($product['country']) ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-percentage"></i>
                    <span>Nồng độ: <?= htmlspecialchars($product['alcohol_content']) ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-tint"></i>
                    <span>Dung tích: <?= htmlspecialchars($product['volume']) ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-leaf"></i>
                    <span>Giống nho: <?= htmlspecialchars($product['grape']) ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-building"></i>
                    <span>Nhà sản xuất: <?= htmlspecialchars($product['producer']) ?></span>
                </div>
                <div class="label-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Niên vụ: <?= htmlspecialchars($product['vintage']) ?></span>
                </div>
            </div>

            <!-- Price -->
            <div class="product-price">
                <?php if ($product['old_price']): ?>
                    <span class="old-price"><?= number_format($product['old_price'], 0, ',', '.') ?> đ</span>
                <?php endif; ?>
                <span class="current-price"><?= number_format($product['price'], 0, ',', '.') ?> đ</span>
            </div>

            <!-- Stock Status -->
            <div class="stock-status">
                <span>Còn hàng: <?= $product['stock'] ?> sản phẩm</span>
            </div>

            <!-- Quantity Selector and Actions -->
            <div class="product-actions">
                <div class="quantity-selector">
                    <button class="quantity-btn decrease">-</button>
                    <input type="number" value="1" min="1" max="<?= $product['stock'] ?>" class="quantity-input">
                    <button class="quantity-btn increase">+</button>
                </div>
                <button class="add-to-cart-btn" data-product-code="<?= htmlspecialchars($product['id']) ?>">Thêm vào giỏ
                    hàng</button>
                <button class="buy-now-btn" data-product-code="<?= htmlspecialchars($product['id']) ?>">Thêm vào yêu
                    thích</button>
            </div>

            <!-- Additional Info -->
            <div class="additional-info">
                <ul>
                    <?php foreach ($product['additional_info'] as $info): ?>
                        <li><?= htmlspecialchars($info) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Product Description -->
    <div class="product-description-section">
        <h2>Mô tả sản phẩm</h2>
        <p><?= htmlspecialchars($product['description']) ?></p>
    </div>

    <!-- Related Products (Optional) -->
    <div class="related-products-section">
        <h2>Sản phẩm liên quan</h2>
        <div class="products-grid">
            <?php
            $related_products = get_related_products($product['id'], 4); // Assume this function fetches related products
            foreach ($related_products as $related_product) {
                echo display_product($related_product); // Reuse the display_product function from product-card.php
            }
            ?>
        </div>
    </div>
</section>