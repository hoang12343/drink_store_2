<?php
if (!defined('APP_START')) exit('No direct access');
require_once ROOT_PATH . '/includes/db_connect.php';

// Xử lý các hành động (thêm, sửa, xóa)
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$success_message = '';
$error_message = '';

if ($action) {
    if ($action === 'add' || $action === 'edit') {
        $promo_data = [
            'code' => trim($_POST['code'] ?? ''),
            'discount_percentage' => filter_input(INPUT_POST, 'discount_percentage', FILTER_VALIDATE_FLOAT),
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'min_order_value' => filter_input(INPUT_POST, 'min_order_value', FILTER_VALIDATE_FLOAT) ?: 0,
            'max_discount_value' => filter_input(INPUT_POST, 'max_discount_value', FILTER_VALIDATE_FLOAT) ?: null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Validate dates
        $start = DateTime::createFromFormat('Y-m-d\TH:i', $promo_data['start_date']);
        $end = DateTime::createFromFormat('Y-m-d\TH:i', $promo_data['end_date']);
        if (!$start || !$end) {
            $error_message = 'Định dạng ngày không hợp lệ.';
        } elseif ($end <= $start) {
            $error_message = 'Ngày kết thúc phải sau ngày bắt đầu.';
        } else {
            if ($action === 'add') {
                try {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM promo_codes WHERE code = ?");
                    $stmt->execute([$promo_data['code']]);
                    if ($stmt->fetchColumn() > 0) {
                        $error_message = "Mã giảm giá '{$promo_data['code']}' đã tồn tại.";
                    } else {
                        $stmt = $pdo->prepare("
                            INSERT INTO promo_codes (code, discount_percentage, start_date, end_date, min_order_value, max_discount_value, is_active)
                            VALUES (:code, :discount_percentage, :start_date, :end_date, :min_order_value, :max_discount_value, :is_active)
                        ");
                        $stmt->execute($promo_data);
                        $success_message = 'Thêm mã giảm giá thành công!';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Lỗi khi thêm mã giảm giá: ' . $e->getMessage();
                    error_log("Error adding promo code: " . $e->getMessage());
                }
            } elseif ($action === 'edit') {
                $promo_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                if ($promo_id) {
                    try {
                        $promo_data['id'] = $promo_id;
                        $stmt = $pdo->prepare("
                            UPDATE promo_codes 
                            SET code = :code, discount_percentage = :discount_percentage, start_date = :start_date, 
                                end_date = :end_date, min_order_value = :min_order_value, max_discount_value = :max_discount_value, 
                                is_active = :is_active
                            WHERE id = :id
                        ");
                        $stmt->execute($promo_data);
                        $success_message = 'Cập nhật mã giảm giá thành công!';
                    } catch (PDOException $e) {
                        $error_message = 'Lỗi khi cập nhật mã giảm giá: ' . $e->getMessage();
                        error_log("Error updating promo code: " . $e->getMessage());
                    }
                }
            }
        }
    } elseif ($action === 'delete') {
        $promo_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($promo_id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM promo_codes WHERE id = ?");
                $stmt->execute([$promo_id]);
                $success_message = 'Xóa mã giảm giá thành công!';
            } catch (PDOException $e) {
                $error_message = 'Lỗi khi xóa mã giảm giá: ' . $e->getMessage();
                error_log("Error deleting promo code: " . $e->getMessage());
            }
        }
    }
}

// Lấy danh sách mã giảm giá với phân trang
$page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT) ?? 1;
$page = max(1, (int)$page);
$promos_per_page = 10;

try {
    $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
    $where = $search ? "WHERE code LIKE ?" : "";
    $params = $search ? ["%$search%"] : [];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM promo_codes $where");
    $stmt->execute($params);
    $total_promos = $stmt->fetchColumn();
    $total_pages = ceil($total_promos / $promos_per_page);

    $offset = ($page - 1) * $promos_per_page;
    $stmt = $pdo->prepare("SELECT * FROM promo_codes $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute(array_merge($params, [$promos_per_page, $offset]));
    $promo_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Lỗi khi lấy danh sách mã giảm giá: ' . $e->getMessage();
    $promo_codes = [];
    error_log("Error fetching promo codes: " . $e->getMessage());
}

// Lấy dữ liệu mã giảm giá để sửa (nếu có)
$edit_promo = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    try {
        $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_promo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = 'Lỗi khi lấy thông tin mã giảm giá: ' . $e->getMessage();
        error_log("Error fetching promo code for edit: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Mã giảm giá</title>
    <link rel="stylesheet" href="assets/css/admin/admin-variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-content.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-products.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/admin/admin-promo-codes.js" defer></script>
</head>

<body>
    <section class="content admin-page">
        <h1>Quản lý Mã giảm giá</h1>

        <?php if ($success_message): ?>
            <div class="form-message success"><?= htmlspecialchars($success_message) ?></div>
        <?php elseif ($error_message): ?>
            <div class="form-message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Form thêm/sửa mã giảm giá -->
        <div class="admin-products">
            <div class="form-header">
                <h2><?= $edit_promo ? 'Sửa mã giảm giá' : 'Thêm mã giảm giá mới' ?></h2>
                <?php if ($edit_promo): ?>
                    <a href="?page=admin&subpage=admin-promo-codes" class="btn btn-add-product">
                        <i class="fas fa-plus"></i> Thêm mã giảm giá
                    </a>
                <?php endif; ?>
            </div>
            <form action="?page=admin&subpage=admin-promo-codes" method="post" class="product-form">
                <input type="hidden" name="action" value="<?= $edit_promo ? 'edit' : 'add' ?>">
                <?php if ($edit_promo): ?>
                    <input type="hidden" name="id" value="<?= $edit_promo['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="code">Mã giảm giá</label>
                    <input type="text" id="code" name="code" value="<?= $edit_promo['code'] ?? '' ?>" required
                        maxlength="20">
                </div>
                <div class="form-group">
                    <label for="discount_percentage">Phần trăm giảm (%)</label>
                    <input type="number" id="discount_percentage" name="discount_percentage" step="0.01" min="0"
                        max="100" value="<?= $edit_promo['discount_percentage'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="start_date">Ngày bắt đầu</label>
                    <input type="datetime-local" id="start_date" name="start_date"
                        value="<?= $edit_promo ? str_replace(' ', 'T', $edit_promo['start_date']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Ngày kết thúc</label>
                    <input type="datetime-local" id="end_date" name="end_date"
                        value="<?= $edit_promo ? str_replace(' ', 'T', $edit_promo['end_date']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="min_order_value">Giá trị đơn tối thiểu</label>
                    <input type="number" id="min_order_value" name="min_order_value" step="0.01" min="0"
                        value="<?= $edit_promo['min_order_value'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label for="max_discount_value">Giảm tối đa</label>
                    <input type="number" id="max_discount_value" name="max_discount_value" step="0.01" min="0"
                        value="<?= $edit_promo['max_discount_value'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label for="is_active">Trạng thái hoạt động</label>
                    <input type="checkbox" id="is_active" name="is_active"
                        <?= ($edit_promo && $edit_promo['is_active']) || !$edit_promo ? 'checked' : '' ?>>
                </div>
                <button type="submit" class="btn"><?= $edit_promo ? 'Cập nhật' : 'Thêm mã giảm giá' ?></button>
            </form>
        </div>

        <!-- Tìm kiếm mã giảm giá -->
        <div class="admin-products">
            <h2>Danh sách mã giảm giá</h2>
            <div class="search-container">
                <input type="text" id="promo-search" placeholder="Tìm kiếm mã giảm giá theo mã..." class="search-input">
            </div>
            <?php if (empty($promo_codes)): ?>
                <p>Không có mã giảm giá nào.</p>
            <?php else: ?>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Phần trăm giảm</th>
                            <th>Giảm tối đa</th>
                            <th>Ngày bắt đầu</th>
                            <th>Ngày kết thúc</th>
                            <th>Giá trị đơn tối thiểu</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($promo_codes as $promo): ?>
                            <tr>
                                <td><?= htmlspecialchars($promo['code']) ?></td>
                                <td><?= number_format($promo['discount_percentage'], 2) ?>%</td>
                                <td><?= $promo['max_discount_value'] ? number_format($promo['max_discount_value'], 0, ',', '.') . ' VNĐ' : 'Không giới hạn' ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($promo['start_date'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($promo['end_date'])) ?></td>
                                <td><?= number_format($promo['min_order_value'], 0, ',', '.') . ' VNĐ' ?></td>
                                <td><?= $promo['is_active'] ? 'Hoạt động' : 'Không hoạt động' ?></td>
                                <td>
                                    <a href="?page=admin&subpage=admin-promo-codes&edit=<?= $promo['id'] ?>"
                                        class="btn small">Sửa</a>
                                    <button type="button" class="btn small danger delete-promo-btn"
                                        data-id="<?= $promo['id'] ?>"
                                        data-code="<?= htmlspecialchars($promo['code']) ?>">Xóa</button>
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

        <!-- Modal xác nhận xóa -->
        <div class="delete-modal" id="delete-promo-modal" style="display: none;">
            <div class="delete-modal-content">
                <h3>Xác nhận xóa mã giảm giá</h3>
                <p>Bạn có chắc muốn xóa mã giảm giá "<span id="delete-promo-code"></span>"?</p>
                <form action="?page=admin&subpage=admin-promo-codes" method="post" id="delete-promo-form">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete-promo-id">
                    <button type="submit" class="confirm-btn">Xóa</button>
                    <button type="button" class="cancel-btn" onclick="$('#delete-promo-modal').hide();">Hủy</button>
                </form>
            </div>
        </div>
    </section>
</body>

</html>