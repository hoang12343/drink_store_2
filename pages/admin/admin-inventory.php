<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

require_once ROOT_PATH . '/pages/utils/inventory-functions.php';

// Xử lý các hành động (thêm, sửa, xóa)
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$success_message = '';
$error_message = '';

if ($action) {
    if ($action === 'add' || $action === 'edit') {
        $inventory_data = [
            'product_id' => filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT),
            'quantity' => filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?: 0,
            'location' => filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
        ];

        if ($action === 'add') {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO inventory (product_id, quantity, location)
                    VALUES (:product_id, :quantity, :location)
                ");
                $stmt->execute($inventory_data);
                $success_message = 'Thêm bản ghi kho thành công!';
            } catch (PDOException $e) {
                $error_message = 'Lỗi khi thêm bản ghi: ' . $e->getMessage();
            }
        } elseif ($action === 'edit') {
            $inventory_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($inventory_id) {
                try {
                    $inventory_data['id'] = $inventory_id;
                    $stmt = $pdo->prepare("
                        UPDATE inventory 
                        SET product_id = :product_id, quantity = :quantity, location = :location
                        WHERE id = :id
                    ");
                    $stmt->execute($inventory_data);
                    $success_message = 'Cập nhật bản ghi kho thành công!';
                } catch (PDOException $e) {
                    $error_message = 'Lỗi khi cập nhật bản ghi: ' . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'delete') {
        $inventory_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($inventory_id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = :id");
                $stmt->bindValue(':id', $inventory_id, PDO::PARAM_INT);
                $stmt->execute();
                $success_message = 'Xóa bản ghi kho thành công!';
            } catch (PDOException $e) {
                $error_message = 'Lỗi khi xóa bản ghi: ' . $e->getMessage();
            }
        }
    }
}

// Lấy danh sách sản phẩm để chọn trong form
try {
    $stmt = $pdo->query("SELECT id, code, name FROM products");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $error_message = 'Lỗi khi lấy danh sách sản phẩm: ' . $e->getMessage();
}

// Lấy danh sách bản ghi kho với phân trang
$page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT) ?? 1;
$page = max(1, (int)$page);
$records_per_page = 10;
$inventory_data = get_inventory($records_per_page, $page);
$inventory = $inventory_data['inventory'];
$total_records = $inventory_data['total_records'];
$total_pages = ceil($total_records / $records_per_page);

// Lấy dữ liệu bản ghi để sửa (nếu có)
$edit_inventory = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    $edit_inventory = get_inventory_by_id($edit_id);
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Kho hàng</title>
    <link rel="stylesheet" href="assets/css/admin/admin-variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-content.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-inventory.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="assets/js/admin/admin-inventory.js" defer></script>
</head>

<body>
    <section class="content admin-page">
        <h1>Quản lý Kho hàng</h1>

        <?php if ($success_message): ?>
        <div class="form-message success"><?= htmlspecialchars($success_message) ?></div>
        <?php elseif ($error_message): ?>
        <div class="form-message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Form thêm/sửa bản ghi kho -->
        <div class="admin-inventory">
            <div class="form-header">
                <h2><?= $edit_inventory ? 'Sửa bản ghi kho' : 'Thêm bản ghi kho mới' ?></h2>
                <?php if ($edit_inventory): ?>
                <a href="?page=admin&subpage=admin-inventory" class="btn btn-add-record">
                    <i class="fas fa-plus"></i> Thêm bản ghi
                </a>
                <?php endif; ?>
            </div>
            <form action="?page=admin&subpage=admin-inventory" method="post" class="inventory-form">
                <input type="hidden" name="action" value="<?= $edit_inventory ? 'edit' : 'add' ?>">
                <?php if ($edit_inventory): ?>
                <input type="hidden" name="id" value="<?= $edit_inventory['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="product_id">Sản phẩm</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">Chọn sản phẩm</option>
                        <?php foreach ($products as $product): ?>
                        <option value="<?= $product['id'] ?>"
                            <?= ($edit_inventory && $edit_inventory['product_id'] == $product['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($product['code'] . ' - ' . $product['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantity">Số lượng</label>
                    <input type="number" id="quantity" name="quantity" value="<?= $edit_inventory['quantity'] ?? 0 ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="location">Vị trí kho</label>
                    <input type="text" id="location" name="location" value="<?= $edit_inventory['location'] ?? '' ?>"
                        required>
                </div>
                <button type="submit" class="btn"><?= $edit_inventory ? 'Cập nhật' : 'Thêm bản ghi' ?></button>
            </form>
        </div>

        <!-- Tìm kiếm bản ghi kho -->
        <div class="admin-inventory">
            <h2>Danh sách bản ghi kho</h2>
            <div class="search-container">
                <input type="text" id="inventory-search" placeholder="Tìm kiếm theo mã hoặc tên sản phẩm..."
                    class="search-input">
            </div>
            <?php if (empty($inventory)): ?>
            <p>Không có bản ghi kho nào.</p>
            <?php else: ?>
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Mã sản phẩm</th>
                        <th>Tên sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Vị trí kho</th>
                        <th>Cập nhật lần cuối</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory as $record): ?>
                    <tr>
                        <td><?= htmlspecialchars($record['code']) ?></td>
                        <td><?= htmlspecialchars($record['name']) ?></td>
                        <td><?= $record['quantity'] ?></td>
                        <td><?= htmlspecialchars($record['location']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($record['last_updated'])) ?></td>
                        <td>
                            <a href="?page=admin&subpage=admin-inventory&edit=<?= $record['id'] ?>"
                                class="btn small">Sửa</a>
                            <form action="?page=admin&subpage=admin-inventory" method="post" class="delete-form"
                                style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $record['id'] ?>">
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