<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

function display_product(array $product): string
{
    $product = array_merge([
        'old_price' => '',
        'discount' => '',
        'grape' => '',
        'type' => '',
        'brand' => '',
        'country' => '',
        'abv' => '',
        'description' => '',
        'display_price' => '',
        'display_old_price' => ''
    ], $product);

    // Dự phòng nếu display_price không có
    if (empty($product['display_price']) && isset($product['price'])) {
        $product['display_price'] = format_price($product['price']);
    }

    ob_start();
?>
    <article class="product-card clickable" data-product-id="<?= htmlspecialchars($product['id']) ?>"
        data-product-code="<?= htmlspecialchars($product['code']) ?>">
        <!-- Product Content - Image and Labels -->
        <div class="product-content">
            <!-- Product Image -->
            <div class="product-img">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>"
                    loading="lazy" class="product-image">
            </div>

            <!-- Labels/Icons Section -->
            <div class="product-info">
                <div class="product-labels">
                    <?php if (!empty($product['grape'])): ?>
                        <div class="label-item">
                            <i class="fas fa-leaf"></i>
                            <span><?= htmlspecialchars($product['grape']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($product['type'])): ?>
                        <div class="label-item">
                            <i class="fas fa-wine-glass-alt"></i>
                            <span><?= htmlspecialchars($product['type']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($product['brand'])): ?>
                        <div class="label-item">
                            <i class="fas fa-tag"></i>
                            <span><?= htmlspecialchars($product['brand']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($product['country'])): ?>
                        <div class="label-item">
                            <i class="fas fa-globe"></i>
                            <span><?= htmlspecialchars($product['country']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($product['abv'])): ?>
                        <div class="label-item">
                            <i class="fas fa-percentage"></i>
                            <span><?= htmlspecialchars($product['abv']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Product Details Section -->
        <div class="product-details">
            <!-- Product Name -->
            <h3 class="product-name">
                <a href="index.php?page=product-detail&id=<?= htmlspecialchars($product['id']) ?>" class="product-title">
                    <?= htmlspecialchars($product['name']) ?>
                </a>
            </h3>

            <!-- Product Description -->
            <?php if (!empty($product['description'])): ?>
                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Price and Action Section -->
        <div class="price-and-action">
            <!-- Product Price -->
            <div class="product-price">
                <?= htmlspecialchars($product['display_price']) ?>
                <?php if (!empty($product['display_old_price'])): ?>
                    <span class="old-price"><?= htmlspecialchars($product['display_old_price']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Product Actions -->
            <div class="product-actions">
                <button class="buy-now-btn" data-product-id="<?= htmlspecialchars($product['id']) ?>">Mua Ngay</button>
            </div>
        </div>
    </article>
<?php
    return ob_get_clean();
}
