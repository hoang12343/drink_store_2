<?php
if (!defined('APP_START')) {
    exit('No direct access');
}
?>
<link rel="stylesheet" href="assets/css/products.css">

<?php
// Database-driven approach - In real implementation, you would fetch from database
function get_products($category = 'all', $search = '', $sort = 'default', $limit = 12)
{
    // Placeholder for database query
    // In real implementation, this would be a database query with filtering

    $all_products = [
        ['id' => 1, 'name' => 'Rượu vang đỏ Pháp', 'code' => 'RV001', 'price' => '1200000', 'display_price' => '1.200.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'stock' => 25, 'rating' => 4.5],
        ['id' => 2, 'name' => 'Whisky Scotland 12 năm', 'code' => 'WS012', 'price' => '1850000', 'display_price' => '1.850.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'whisky', 'stock' => 18, 'rating' => 4.8],
        ['id' => 3, 'name' => 'Vodka Nga', 'code' => 'VD003', 'price' => '950000', 'display_price' => '950.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'vodka', 'stock' => 30, 'rating' => 4.3],
        ['id' => 4, 'name' => 'Cognac Pháp', 'code' => 'CG001', 'price' => '2300000', 'display_price' => '2.300.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'stock' => 12, 'rating' => 4.9],
        ['id' => 5, 'name' => 'Rum Jamaica', 'code' => 'RM002', 'price' => '780000', 'display_price' => '780.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'other', 'stock' => 22, 'rating' => 4.4],
        ['id' => 6, 'name' => 'Rượu vang trắng Ý', 'code' => 'RV045', 'price' => '950000', 'display_price' => '950.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'stock' => 15, 'rating' => 4.2],
        ['id' => 7, 'name' => 'Champagne Pháp', 'code' => 'CH001', 'price' => '2100000', 'display_price' => '2.100.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'stock' => 10, 'rating' => 4.7],
        ['id' => 8, 'name' => 'Whisky Ireland', 'code' => 'WI007', 'price' => '1400000', 'display_price' => '1.400.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'whisky', 'stock' => 20, 'rating' => 4.6],
        ['id' => 9, 'name' => 'Gin London', 'code' => 'GL004', 'price' => '950000', 'display_price' => '950.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'gin', 'stock' => 25, 'rating' => 4.4],
        ['id' => 10, 'name' => 'Sake Nhật Bản', 'code' => 'SK001', 'price' => '1100000', 'display_price' => '1.100.000 ₫', 'image' => '/api/placeholder/220/180', 'category' => 'other', 'stock' => 15, 'rating' => 4.3],
        ['id' => 11, 'name' => 'Rượu vang Chile', 'code' => 'RV023', 'price' => '640000', 'display_price' => '640.000 ₫', 'old_price' => '800.000 ₫', 'discount' => '-20%', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'stock' => 28, 'rating' => 4.1, 'promotion' => true],
        ['id' => 12, 'name' => 'Tequila Mexico', 'code' => 'TQ005', 'price' => '750000', 'display_price' => '750.000 ₫', 'old_price' => '1.000.000 ₫', 'discount' => '-25%', 'image' => '/api/placeholder/220/180', 'category' => 'other', 'stock' => 18, 'rating' => 4.2, 'promotion' => true],
        ['id' => 13, 'name' => 'Whisky Mỹ', 'code' => 'WM010', 'price' => '880000', 'display_price' => '880.000 ₫', 'old_price' => '1.100.000 ₫', 'discount' => '-20%', 'image' => '/api/placeholder/220/180', 'category' => 'whisky', 'stock' => 22, 'rating' => 4.5, 'promotion' => true],
        ['id' => 14, 'name' => 'Brandy Tây Ban Nha', 'code' => 'BR003', 'price' => '600000', 'display_price' => '600.000 ₫', 'old_price' => '750.000 ₫', 'discount' => '-20%', 'image' => '/api/placeholder/220/180', 'category' => 'other', 'stock' => 14, 'rating' => 4.0, 'promotion' => true],
        ['id' => 15, 'name' => 'Rượu vang Úc', 'code' => 'RV056', 'price' => '550000', 'display_price' => '550.000 ₫', 'old_price' => '690.000 ₫', 'discount' => '-20%', 'image' => '/api/placeholder/220/180', 'category' => 'wine', 'stock' => 20, 'rating' => 4.2, 'promotion' => true],
    ];

    // Filter by category
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

    // Filter by search term
    if (!empty($search)) {
        $filtered_products = array_filter($filtered_products, function ($product) use ($search) {
            return stripos($product['name'], $search) !== false ||
                stripos($product['code'], $search) !== false;
        });
    }

    // Sort products
    switch ($sort) {
        case 'price_asc':
            usort($filtered_products, function ($a, $b) {
                return $a['price'] - $b['price'];
            });
            break;
        case 'price_desc':
            usort($filtered_products, function ($a, $b) {
                return $b['price'] - $a['price'];
            });
            break;
        case 'name_asc':
            usort($filtered_products, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            break;
        case 'name_desc':
            usort($filtered_products, function ($a, $b) {
                return strcmp($b['name'], $a['name']);
            });
            break;
        case 'rating':
            usort($filtered_products, function ($a, $b) {
                return $b['rating'] - $a['rating'];
            });
            break;
        default:
            // Default sorting (newest first - by ID)
            usort($filtered_products, function ($a, $b) {
                return $b['id'] - $a['id'];
            });
    }

    // Limit products
    return array_slice($filtered_products, 0, $limit);
}

// Display a single product card
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

// Get filter parameters
$category = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'all';
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'default';

// Fetch products
$products = get_products($category, $search, $sort);
$total_products = count($products);

// Get categories for filter
$categories = [
    'all' => 'Tất cả sản phẩm',
    'wine' => 'Rượu vang',
    'whisky' => 'Whisky',
    'vodka' => 'Vodka',
    'gin' => 'Gin',
    'beer' => 'Bia',
    'other' => 'Khác',
    'promotion' => 'Khuyến mãi'
];

// Sort options
$sort_options = [
    'default' => 'Mặc định',
    'price_asc' => 'Giá tăng dần',
    'price_desc' => 'Giá giảm dần',
    'name_asc' => 'Tên A-Z',
    'name_desc' => 'Tên Z-A',
    'rating' => 'Đánh giá cao nhất'
];
?>

<section class="content products-page">
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
                        <option value="<?= $key ?>" <?= $sort === $key ? 'selected' : '' ?>><?= $label ?></option>
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
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productCode = this.getAttribute('data-product-code');

            // Send AJAX request to add item to cart
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
                        // Update cart count
                        document.getElementById('cartCount').textContent = data.cart_count;

                        // Show success message
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