<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

// Prevent multiple inclusions of the filter bar
if (defined('FILTER_BAR_RENDERED')) {
    return;
}
define('FILTER_BAR_RENDERED', true);

global $pdo;

// Fetch unique filter options from the database
try {
    // Countries
    $stmt = $pdo->query("SELECT DISTINCT country FROM products WHERE country IS NOT NULL ORDER BY country");
    $countries = ['all' => 'Tất cả'] + array_column($stmt->fetchAll(), 'country', 'country');

    // Types
    $stmt = $pdo->query("SELECT DISTINCT type FROM products WHERE type IS NOT NULL ORDER BY type");
    $wine_types = ['all' => 'Tất cả'] + array_column($stmt->fetchAll(), 'type', 'type');

    // Volumes
    $stmt = $pdo->query("SELECT DISTINCT volume FROM products WHERE volume IS NOT NULL ORDER BY volume");
    $volumes = ['all' => 'Tất cả'] + array_column($stmt->fetchAll(), 'volume', 'volume');

    // Grapes
    $stmt = $pdo->query("SELECT DISTINCT grape FROM products WHERE grape IS NOT NULL ORDER BY grape");
    $grapes = ['all' => 'Tất cả'] + array_column($stmt->fetchAll(), 'grape', 'grape');
} catch (PDOException $e) {
    error_log("Error fetching filter options: " . $e->getMessage());
    $countries = $wine_types = $volumes = $grapes = ['all' => 'Tất cả'];
}

// Preserve all existing query parameters
$query_params = $_GET;
unset($query_params['page']); // Remove 'page' as it will be added explicitly
?>

<aside class="left-bar">
    <h2>Lọc sản phẩm</h2>

    <!-- Filter by Price Range -->
    <div class="filter-section">
        <h3>Khoảng giá</h3>
        <form action="index.php" method="get" class="filter-form">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category ?? 'all') ?>">
            <?php foreach ($query_params as $key => $value): ?>
            <?php if ($key !== 'price_min' && $key !== 'price_max' && $value): ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
            <?php endif; ?>
            <?php endforeach; ?>
            <label for="price_min">Từ:</label>
            <input type="number" name="price_min" id="price_min" min="0" step="10000"
                value="<?= htmlspecialchars($filters['price_min'] ?? '') ?>" placeholder="500000">
            <label for="price_max">Đến:</label>
            <input type="number" name="price_max" id="price_max" min="0" step="10000"
                value="<?= htmlspecialchars($filters['price_max'] ?? '') ?>" placeholder="2000000">
            <button type="submit" class="filter-btn">Lọc</button>
        </form>
    </div>

    <!-- Custom Price Input -->
    <div class="filter-section">
        <h3>Nhập giá bạn muốn tìm</h3>
        <form action="index.php" method="get" class="filter-form">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category ?? 'all') ?>">
            <?php foreach ($query_params as $key => $value): ?>
            <?php if ($key !== 'custom_price' && $value): ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
            <?php endif; ?>
            <?php endforeach; ?>
            <input type="number" name="custom_price" id="custom_price" min="0" step="10000"
                value="<?= htmlspecialchars($filters['custom_price'] ?? '') ?>" placeholder="1000000">
            <button type="submit" class="filter-btn">Tìm</button>
        </form>
    </div>

    <!-- Filter by Country -->
    <div class="filter-section">
        <h3>Quốc gia</h3>
        <form action="index.php" method="get" class="filter-form">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category ?? 'all') ?>">
            <?php foreach ($query_params as $key => $value): ?>
            <?php if ($key !== 'country' && $value): ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
            <?php endif; ?>
            <?php endforeach; ?>
            <select name="country" onchange="this.form.submit()">
                <?php foreach ($countries as $key => $label): ?>
                <option value="<?= htmlspecialchars($key) ?>"
                    <?= ($filters['country'] ?? 'all') === $key ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Filter by Wine Type -->
    <div class="filter-section">
        <h3>Loại đồ uống</h3>
        <form action="index.php" method="get" class="filter-form">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category ?? 'all') ?>">
            <?php foreach ($query_params as $key => $value): ?>
            <?php if ($key !== 'type' && $value): ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
            <?php endif; ?>
            <?php endforeach; ?>
            <select name="type" onchange="this.form.submit()">
                <?php foreach ($wine_types as $key => $label): ?>
                <option value="<?= htmlspecialchars($key) ?>"
                    <?= ($filters['type'] ?? 'all') === $key ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Filter by Volume -->
    <div class="filter-section">
        <h3>Dung tích</h3>
        <form action="index.php" method="get" class="filter-form">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category ?? 'all') ?>">
            <?php foreach ($query_params as $key => $value): ?>
            <?php if ($key !== 'volume' && $value): ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
            <?php endif; ?>
            <?php endforeach; ?>
            <select name="volume" onchange="this.form.submit()">
                <?php foreach ($volumes as $key => $label): ?>
                <option value="<?= htmlspecialchars($key) ?>"
                    <?= ($filters['volume'] ?? 'all') === $key ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Filter by Grape Variety -->
    <div class="filter-section">
        <h3>Giống nho</h3>
        <form action="index.php" method="get" class="filter-form">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category ?? 'all') ?>">
            <?php foreach ($query_params as $key => $value): ?>
            <?php if ($key !== 'grape' && $value): ?>
            <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
            <?php endif; ?>
            <?php endforeach; ?>
            <select name="grape" onchange="this.form.submit()">
                <?php foreach ($grapes as $key => $label): ?>
                <option value="<?= htmlspecialchars($key) ?>"
                    <?= ($filters['grape'] ?? 'all') === $key ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Reset Filters -->
    <div class="filter-section">
        <a href="index.php?page=products&category=<?= htmlspecialchars($category ?? 'all') ?>"
            class="filter-btn reset-btn">Xóa bộ lọc</a>
    </div>
</aside>