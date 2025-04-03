<?php
if (!defined('APP_START')) {
    exit('No direct access');
}
?>
<link rel="stylesheet" href="assets/css/home.css">

<?php
// Hàm lấy danh sách sản phẩm
function get_products($category = 'all', $limit = 4)
{
    $all_products = [
        ['id' => 1, 'name' => 'Rượu vang đỏ Pháp', 'price' => '1200000', 'display_price' => '1.200.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'wine'],
        ['id' => 2, 'name' => 'Whisky Scotland 12 năm', 'price' => '1850000', 'display_price' => '1.850.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'whisky'],
        ['id' => 3, 'name' => 'Vodka Nga', 'price' => '950000', 'display_price' => '950.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'vodka'],
        ['id' => 4, 'name' => 'Cognac Pháp', 'price' => '2300000', 'display_price' => '2.300.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'wine'],
        ['id' => 5, 'name' => 'Rum Jamaica', 'price' => '780000', 'display_price' => '780.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'other'],
        ['id' => 6, 'name' => 'Rượu vang trắng Ý', 'price' => '950000', 'display_price' => '950.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'wine'],
        ['id' => 7, 'name' => 'Champagne Pháp', 'price' => '2100000', 'display_price' => '2.100.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'wine'],
        ['id' => 8, 'name' => 'Whisky Ireland', 'price' => '1400000', 'display_price' => '1.400.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'whisky'],
        ['id' => 9, 'name' => 'Gin London', 'price' => '950000', 'display_price' => '950.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'gin'],
        ['id' => 10, 'name' => 'Sake Nhật Bản', 'price' => '1100000', 'display_price' => '1.100.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'other'],
        ['id' => 11, 'name' => 'Rượu vang Chile', 'price' => '640000', 'display_price' => '640.000 ₫', 'old_price' => '800.000 ₫', 'discount' => '-20%', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'promotion' => true],
        ['id' => 12, 'name' => 'Tequila Mexico', 'price' => '750000', 'display_price' => '750.000 ₫', 'old_price' => '1.000.000 ₫', 'discount' => '-25%', 'image' => '/api/placeholder/220/180', 'category' => 'other', 'promotion' => true],
        ['id' => 13, 'name' => 'Whisky Mỹ', 'price' => '880000', 'display_price' => '880.000 ₫', 'old_price' => '1.100.000 ₫', 'discount' => '-20%', 'image' => '/api/placeholder/220/180', 'category' => 'whisky', 'promotion' => true],
        ['id' => 14, 'name' => 'Brandy Tây Ban Nha', 'price' => '600000', 'display_price' => '600.000 ₫', 'old_price' => '750.000 ₫', 'discount' => '-20%', 'image' => '/api/placeholder/220/180', 'category' => 'other', 'promotion' => true],
        ['id' => 15, 'name' => 'Rượu vang Úc', 'price' => '550000', 'display_price' => '550.000 ₫', 'old_price' => '690.000 ₫', 'discount' => '-20%', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'promotion' => true],
    ];

    if ($category !== 'all') {
        $filtered_products = array_filter($all_products, function ($product) use ($category) {
            if ($category === 'promotion' && isset($product['promotion']) && $product['promotion']) {
                return true;
            }
            return $product['category'] === $category;
        });
    } else {
        $filtered_products = $all_products;
    }

    return array_slice($filtered_products, 0, $limit);
}

// Hàm hiển thị sản phẩm
function display_product(array $product): string
{
    $product = array_merge(['old_price' => '', 'discount' => ''], $product);
    ob_start();
?>
<article class="product-card">
    <div class="product-img">
        <?php if (!empty($product['discount'])): ?>
        <span class="discount-badge"><?= htmlspecialchars($product['discount']) ?></span>
        <?php endif; ?>
        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>"
            loading="lazy">
    </div>
    <div class="product-info">
        <h3><?= htmlspecialchars($product['name']) ?></h3>
        <div class="product-price">
            <?= htmlspecialchars($product['display_price']) ?>
            <?php if (!empty($product['old_price'])): ?>
            <span class="old-price"><?= htmlspecialchars($product['old_price']) ?></span>
            <?php endif; ?>
        </div>
        <div class="product-actions">
            <a href="?page=product_detail&code=<?= htmlspecialchars($product['code'] ?? $product['id']) ?>"
                class="view-btn">Chi tiết</a>
            <button class="add-to-cart-btn"
                data-product-code="<?= htmlspecialchars($product['code'] ?? $product['id']) ?>">
                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
            </button>
        </div>
    </div>
</article>
<?php
    return ob_get_clean();
}

// Danh mục chính
$main_categories = [
    'promotion' => 'Sản phẩm khuyến mãi',
    'wine' => 'Rượu vang',
    'whisky' => 'Whisky',
    'vodka' => 'Vodka',
    'gin' => 'Gin',
    'other' => 'Sản phẩm khác'
];
?>

<!-- Banner chính -->
<section class="main-banner">
    <div class="banner-content">
        <h1>Chào mừng đến với Cửa hàng đồ uống</h1>
        <p>Khám phá các loại đồ uống cao cấp, đa dạng với giá tốt nhất</p>
        <a href="?page=products" class="btn-shop-now">Mua ngay</a>
    </div>
</section>

<!-- Các danh mục sản phẩm -->
<section class="home-content">
    <?php foreach ($main_categories as $category => $title): ?>
    <?php
        $category_products = get_products($category, 4); // Lấy 4 sản phẩm mỗi danh mục
        if (empty($category_products)) continue;
        ?>
    <div class="category-section">
        <div class="section-header">
            <h2><?= htmlspecialchars($title) ?></h2>
            <a href="?page=products&category=<?= $category ?>" class="view-all">Xem tất cả <i
                    class="fas fa-chevron-right"></i></a>
        </div>
        <div class="products-grid">
            <?php foreach ($category_products as $product): ?>
            <?= display_product($product) ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productCode = this.getAttribute('data-product-code');
            fetch('processes/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_code=' + encodeURIComponent(productCode) + '&quantity=1'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('cartCount').textContent = data.cart_count;
                        alert('Sản phẩm đã được thêm vào giỏ hàng!');
                    } else {
                        alert(data.message ||
                            'Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng.');
                });
        });
    });
});
</script>