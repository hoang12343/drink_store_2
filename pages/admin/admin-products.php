<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

require_once ROOT_PATH . '/pages/utils/product-functions.php';

// Xử lý các hành động (thêm, sửa, xóa)
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$success_message = '';
$error_message = '';

if ($action) {
    if ($action === 'add' || $action === 'edit') {
        $product_data = [
            'code' => filter_input(INPUT_POST, 'code', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'category_id' => filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT),
            'price' => filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT),
            'old_price' => filter_input(INPUT_POST, 'old_price', FILTER_VALIDATE_FLOAT) ?: null,
            'discount' => filter_input(INPUT_POST, 'discount', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'stock' => filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT) ?: 0,
            'image' => filter_input(INPUT_POST, 'image', FILTER_SANITIZE_URL),
            'grape' => filter_input(INPUT_POST, 'grape', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'type' => filter_input(INPUT_POST, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'brand' => filter_input(INPUT_POST, 'brand', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'country' => filter_input(INPUT_POST, 'country', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'abv' => filter_input(INPUT_POST, 'abv', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'volume' => filter_input(INPUT_POST, 'volume', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '750ml',
            'description' => filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'rating' => filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT) ?: 0.0,
            'reviews' => filter_input(INPUT_POST, 'reviews', FILTER_VALIDATE_INT) ?: 0
        ];

        if ($action === 'add') {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO products (code, name, category_id, price, old_price, discount, stock, image, grape, type, brand, country, abv, volume, description, rating, reviews)
                    VALUES (:code, :name, :category_id, :price, :old_price, :discount, :stock, :image, :grape, :type, :brand, :country, :abv, :volume, :description, :rating, :reviews)
                ");
                $stmt->execute($product_data);
                $success_message = 'Thêm sản phẩm thành công!';
            } catch (PDOException $e) {
                $error_message = 'Lỗi khi thêm sản phẩm: ' . $e->getMessage();
            }
        } elseif ($action === 'edit') {
            $product_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($product_id) {
                try {
                    $product_data['id'] = $product_id;
                    $stmt = $pdo->prepare("
                        UPDATE products 
                        SET code = :code, name = :name, category_id = :category_id, price = :price, old_price = :old_price, 
                            discount = :discount, stock = :stock, image = :image, grape = :grape, type = :type, 
                            brand = :brand, country = :country, abv = :abv, volume = :volume, description = :description, 
                            rating = :rating, reviews = :reviews
                        WHERE id = :id
                    ");
                    $stmt->execute($product_data);
                    $success_message = 'Cập nhật sản phẩm thành công!';
                } catch (PDOException $e) {
                    $error_message = 'Lỗi khi cập nhật sản phẩm: ' . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'delete') {
        $product_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($product_id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
                $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
                $stmt->execute();
                $success_message = 'Xóa sản phẩm thành công!';
            } catch (PDOException $e) {
                $error_message = 'Lỗi khi xóa sản phẩm: ' . $e->getMessage();
            }
        }
    }
}

// Lấy danh sách danh mục
try {
    $stmt = $pdo->query("SELECT id, display_name FROM categories");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $error_message = 'Lỗi khi lấy danh mục: ' . $e->getMessage();
}

// Lấy danh sách sản phẩm với phân trang
$page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT) ?? 1;
$page = max(1, (int)$page);
$products_per_page = 10;
$products_data = get_products('all', '', 'default', $products_per_page, [], $page);
$products = $products_data['products'];
$total_products = $products_data['total_products'];
$total_pages = ceil($total_products / $products_per_page);

// Lấy dữ liệu sản phẩm để sửa (nếu có)
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    $edit_product = get_product_by_id($edit_id);
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm</title>
    <link rel="stylesheet" href="assets/css/admin/admin-variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-content.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-products.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="assets/js/admin/admin-products.js" defer></script>
</head>

<body>
    <section class="content admin-page">
        <h1>Quản lý Sản phẩm</h1>

        <?php if ($success_message): ?>
        <div class="form-message success"><?= htmlspecialchars($success_message) ?></div>
        <?php elseif ($error_message): ?>
        <div class="form-message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Form thêm/sửa sản phẩm -->
        <div class="admin-products">
            <div class="form-header">
                <h2><?= $edit_product ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới' ?></h2>
                <?php if ($edit_product): ?>
                <a href="?page=admin&subpage=admin-products" class="btn btn-add-product">
                    <i class="fas fa-plus"></i> Thêm sản phẩm
                </a>
                <?php endif; ?>
            </div>
            <form action="?page=admin&subpage=admin-products" method="post" class="product-form">
                <input type="hidden" name="action" value="<?= $edit_product ? 'edit' : 'add' ?>">
                <?php if ($edit_product): ?>
                <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="code">Mã sản phẩm</label>
                    <input type="text" id="code" name="code" value="<?= $edit_product['code'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="name">Tên sản phẩm</label>
                    <input type="text" id="name" name="name" value="<?= $edit_product['name'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="category_id">Danh mục</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Chọn danh mục</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"
                            <?= ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['display_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Giá</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?= $edit_product['price'] ?? '' ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="old_price">Giá cũ (nếu có)</label>
                    <input type="number" id="old_price" name="old_price" step="0.01"
                        value="<?= $edit_product['old_price'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label for="discount">Giảm giá</label>
                    <input type="text" id="discount" name="discount" value="<?= $edit_product['discount'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label for="stock">Tồn kho</label>
                    <input type="number" id="stock" name="stock" value="<?= $edit_product['stock'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label for="image">URL hình ảnh</label>
                    <input type="url" id="image" name="image" value="<?= $edit_product['image'] ?? '' ?>">
                    <div class="image-preview"></div>
                </div>
                <div class="form-group">
                    <label for="grape">Giống nho</label>
                    <input type="text" id="grape" name="grape" value="<?= $edit_product['grape'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label for="type">Loại</label>
                    <input type="text" id="type" name="type" value="<?= $edit_product['type'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label for="brand">Nhà sản xuất</label>
                    <input type="text" id="brand" name="brand" value="<?= $edit_product['brand'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label for="country">Quốc gia</label>
                    <input type="text" id="country" name="country" value="<?= $edit_product['country'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label for="abv">Nồng độ cồn</label>
                    <input type="text" id="abv" name="abv" value="<?= $edit_product['abv'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label for="volume">Dung tích</label>
                    <input type="text" id="volume" name="volume" value="<?= $edit_product['volume'] ?? '750ml' ?>">
                </div>
                <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea id="description" name="description"><?= $edit_product['description'] ?? '' ?></textarea>
                </div>
                <div class="form-group">
                    <label for="rating">Đánh giá</label>
                    <input type="number" id="rating" name="rating" step="0.1" min="0" max="5"
                        value="<?= $edit_product['rating'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label for="reviews">Số lượt đánh giá</label>
                    <input type="number" id="reviews" name="reviews" value="<?= $edit_product['reviews'] ?? 0 ?>">
                </div>
                <button type="submit" class="btn"><?= $edit_product ? 'Cập nhật' : 'Thêm sản phẩm' ?></button>
            </form>
        </div>

        <!-- Tìm kiếm sản phẩm -->
        <div class="admin-products">
            <h2>Danh sách sản phẩm</h2>
            <div class="search-container">
                <input type="text" id="product-search" placeholder="Tìm kiếm sản phẩm theo mã hoặc tên..."
                    class="search-input">
            </div>
            <?php if (empty($products)): ?>
            <p>Không có sản phẩm nào.</p>
            <?php else: ?>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Tên</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Loại</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['code']) ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['category_name']) ?></td>
                        <td><?= format_price($product['price']) ?></td>
                        <td><?= $product['stock'] ?></td>
                        <td><?= htmlspecialchars($product['type'] ?? 'N/A') ?></td>
                        <td>
                            <a href="?page=admin&subpage=admin-products&edit=<?= $product['id'] ?>"
                                class="btn small">Sửa</a>
                            <form action="?page=admin&subpage=admin-products" method="post" class="delete-form"
                                style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn small danger">Xóa</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Phân trang -->
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
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="<?= $base_url ?>&p=<?= $i ?>"
                    class="pagination-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                <a href="<?= $base_url ?>&p=<?= $page + 1 ?>" class="pagination-btn next">Sau</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>