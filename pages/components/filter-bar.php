<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

// Prevent multiple inclusions of the filter bar
if (defined('FILTER_BAR_RENDERED')) {
    return;
}
define('FILTER_BAR_RENDERED', true);

// Filter options
$countries = ['all' => 'Tất cả', 'Pháp' => 'Pháp', 'Ý' => 'Ý', 'Chile' => 'Chile', 'Úc' => 'Úc'];
$wine_types = ['all' => 'Tất cả', 'Đỏ' => 'Đỏ', 'Trắng' => 'Trắng', 'Sủi' => 'Sủi'];
$volumes = ['all' => 'Tất cả', '750ml' => '750ml', '700ml' => '700ml'];
$grapes = ['all' => 'Tất cả', 'Cabernet Sauvignon' => 'Cabernet Sauvignon', 'Merlot' => 'Merlot', 'Chardonnay' => 'Chardonnay', 'Pinot Noir' => 'Pinot Noir', 'Syrah' => 'Syrah', 'Sauvignon Blanc' => 'Sauvignon Blanc'];
?>

<aside class="left-bar">
    <h2>Lọc sản phẩm</h2>

    <!-- Filter by Price Range -->
    <div class="filter-section">
        <h3>Khoảng giá</h3>
        <form action="index.php" method="get" class="filter-form">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <label for="price_min">Từ:</label>
            <input type="number" name="price_min" id="price_min"
                value="<?= htmlspecialchars($filters['price_min'] ?? '') ?>" placeholder="500.000">
            <label for="price_max">Đến:</label>
            <input type="number" name="price_max" id="price_max"
                value="<?= htmlspecialchars($filters['price_max'] ?? '') ?>" placeholder="2.000.000">
            <button type="submit" class="filter-btn">Lọc</button>
        </form>
    </div>

    <!-- Custom Price Input -->
    <div class="filter-section">
        <h3>Nhập giá bạn muốn tìm</h3>
        <form action="index.php" method="get" class="filter-form">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <input type="text" name="custom_price" id="custom_price"
                value="<?= htmlspecialchars($filters['custom_price'] ?? '') ?>" placeholder="1.000.000">
            <button type="submit" class="filter-btn">Tìm</button>
        </form>
    </div>

    <!-- Filter by Country -->
    <div class="filter-section">
        <h3>Quốc gia</h3>
        <form action="index.php" method="get" class="filter-form">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <select name="country" onchange="this.form.submit()">
                <?php foreach ($countries as $key => $label): ?>
                <option value="<?= $key ?>" <?= ($filters['country'] ?? 'all') === $key ? 'selected' : '' ?>>
                    <?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Filter by Wine Type -->
    <div class="filter-section">
        <h3>Loại vang</h3>
        <form action="index.php" method="get" class="filter-form">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <select name="type" onchange="this.form.submit()">
                <?php foreach ($wine_types as $key => $label): ?>
                <option value="<?= $key ?>" <?= ($filters['type'] ?? 'all') === $key ? 'selected' : '' ?>><?= $label ?>
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
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <select name="volume" onchange="this.form.submit()">
                <?php foreach ($volumes as $key => $label): ?>
                <option value="<?= $key ?>" <?= ($filters['volume'] ?? 'all') === $key ? 'selected' : '' ?>>
                    <?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Filter by Grape Variety -->
    <div class="filter-section">
        <h3>Giống nho</h3>
        <form action="index.php" method="get" class="filter-form">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <select name="grape" onchange="this.form.submit()">
                <?php foreach ($grapes as $key => $label): ?>
                <option value="<?= $key ?>" <?= ($filters['grape'] ?? 'all') === $key ? 'selected' : '' ?>><?= $label ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</aside>