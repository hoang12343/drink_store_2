<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

function display_product(array $product): string
{
    $product = array_merge(['old_price' => '', 'discount' => ''], $product);
    ob_start();
?>
<article class="product-card">
    <?php if (!empty($product['discount'])): ?>
    <span class="discount-badge"><?= htmlspecialchars($product['discount']) ?></span>
    <?php endif; ?>
    <div class="product-img">
        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>"
            loading="lazy">
    </div>
    <div class="product-info">
        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
        <div class="product-meta">
            <span class="product-code">Mã: <?= htmlspecialchars($product['code']) ?></span>
            <?php if (isset($product['rating'])): ?>
            <div class="product-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php if ($i <= floor($product['rating'])): ?>
                <i class="fas fa-star"></i>
                <?php elseif ($i - 0.5 <= $product['rating']): ?>
                <i class="fas fa-star-half-alt"></i>
                <?php else: ?>
                <i class="far fa-star"></i>
                <?php endif; ?>
                <?php endfor; ?>
                <span>(<?= $product['rating'] ?>)</span>
            </div>
            <?php endif; ?>
        </div>
        <div class="product-price">
            <?= htmlspecialchars($product['display_price']) ?>
            <?php if (!empty($product['old_price'])): ?>
            <span class="old-price"><?= htmlspecialchars($product['old_price']) ?></span>
            <?php endif; ?>
        </div>
        <div class="product-actions">
            <a href="?page=product_detail&code=<?= htmlspecialchars($product['code']) ?>" class="view-btn">Chi tiết</a>
            <button class="add-to-cart-btn" data-product-code="<?= htmlspecialchars($product['code']) ?>">
                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
            </button>
        </div>
    </div>
</article>
<?php
    return ob_get_clean();
}
?>