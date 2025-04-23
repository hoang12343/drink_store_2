<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

require_once ROOT_PATH . '/includes/db_connect.php';

// Initialize variables
$success_message = '';
$error_message = '';
$status_filter = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '';
$page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT) ?: 1;
$page = max(1, (int)$page);
$orders_per_page = 10;

// Handle actions (edit, delete)
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($action) {
    if ($action === 'edit') {
        $order_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($order_id && in_array($status, ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'])) {
            try {
                $stmt = $pdo->prepare("UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id");
                $stmt->execute(['status' => $status, 'id' => $order_id]);
                $success_message = 'Cập nhật trạng thái đơn hàng thành công!';
            } catch (PDOException $e) {
                $error_message = 'Lỗi khi cập nhật đơn hàng: ' . $e->getMessage();
            }
        } else {
            $error_message = 'Dữ liệu không hợp lệ.';
        }
    } elseif ($action === 'delete') {
        $order_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($order_id) {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = :id");
                $stmt->execute(['id' => $order_id]);
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = :id");
                $stmt->execute(['id' => $order_id]);
                $pdo->commit();
                $success_message = 'Xóa đơn hàng thành công!';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_message = 'Lỗi khi xóa đơn hàng: ' . $e->getMessage();
            }
        }
    }
}

// Fetch orders with pagination and filter
try {
    $query = "SELECT o.id, o.user_id, u.full_name, o.total_amount, o.status, o.created_at 
              FROM orders o 
              JOIN users u ON o.user_id = u.id";
    $params = [];
    if ($status_filter) {
        $query .= " WHERE o.status = :status";
        $params['status'] = $status_filter;
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_orders = count($all_orders);

    $offset = ($page - 1) * $orders_per_page;
    $query .= " ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    if ($status_filter) {
        $stmt->bindValue(':status', $status_filter, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $orders_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_pages = ceil($total_orders / $orders_per_page);
} catch (PDOException $e) {
    $error_message = 'Lỗi khi lấy danh sách đơn hàng: ' . $e->getMessage();
    $orders = [];
    $total_orders = 0;
    $total_pages = 1;
}

// Fetch order details for editing
$edit_order = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    try {
        $stmt = $pdo->prepare("
            SELECT o.id, o.user_id, u.full_name, o.total_amount, o.status, o.created_at 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = :id
        ");
        $stmt->execute(['id' => $edit_id]);
        $edit_order = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = 'Lỗi khi lấy thông tin đơn hàng: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đơn hàng</title>
    <link rel="stylesheet" href="assets/css/admin/admin-variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-content.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-orders.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="assets/js/admin/admin-orders.js" defer></script>
</head>

<body>
    <section class="content admin-page">
        <h1>Quản lý Đơn hàng</h1>

        <?php if ($success_message): ?>
            <div class="form-message success"><?= htmlspecialchars($success_message) ?></div>
        <?php elseif ($error_message): ?>
            <div class="form-message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Edit Order Form -->
        <?php if ($edit_order): ?>
            <div class="admin-orders">
                <div class="form-header">
                    <h2>Cập nhật Đơn hàng #<?= $edit_order['id'] ?></h2>
                    <a href="?page=admin&subpage=admin-orders" class="btn btn-cancel">Hủy</a>
                </div>
                <form action="?page=admin&subpage=admin-orders" method="post" class="order-form">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $edit_order['id'] ?>">
                    <div class="form-group">
                        <label for="status">Trạng thái</label>
                        <select id="status" name="status" required>
                            <option value="Pending" <?= $edit_order['status'] === 'Pending' ? 'selected' : '' ?>>Chờ xử lý
                            </option>
                            <option value="Processing" <?= $edit_order['status'] === 'Processing' ? 'selected' : '' ?>>Đang
                                xử lý</option>
                            <option value="Shipped" <?= $edit_order['status'] === 'Shipped' ? 'selected' : '' ?>>Đã giao
                            </option>
                            <option value="Delivered" <?= $edit_order['status'] === 'Delivered' ? 'selected' : '' ?>>Hoàn
                                thành</option>
                            <option value="Cancelled" <?= $edit_order['status'] === 'Cancelled' ? 'selected' : '' ?>>Hủy
                            </option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Cập nhật</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Filter Form -->
        <div class="admin-orders">
            <h2>Lọc Đơn hàng</h2>
            <form action="?page=admin&subpage=admin-orders" method="get" class="filter-form">
                <input type="hidden" name="page" value="admin">
                <input type="hidden" name="subpage" value="admin-orders">
                <div class="form-group">
                    <label for="status">Trạng thái</label>
                    <select id="status" name="status">
                        <option value="">Tất cả</option>
                        <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                        <option value="Processing" <?= $status_filter === 'Processing' ? 'selected' : '' ?>>Đang xử lý
                        </option>
                        <option value="Shipped" <?= $status_filter === 'Shipped' ? 'selected' : '' ?>>Đã giao</option>
                        <option value="Delivered" <?= $status_filter === 'Delivered' ? 'selected' : '' ?>>Hoàn thành
                        </option>
                        <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Hủy</option>
                    </select>
                </div>
                <button type="submit" class="btn">Lọc</button>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="admin-orders">
            <h2>Danh sách Đơn hàng</h2>
            <?php if (empty($orders)): ?>
                <p>Không có đơn hàng nào.</p>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['id']) ?></td>
                                <td><?= htmlspecialchars($order['full_name']) ?></td>
                                <td><?= number_format($order['total_amount'], 0, ',', '.') ?> VNĐ</td>
                                <td><?= htmlspecialchars($order['status']) ?></td>
                                <td><?= htmlspecialchars($order['created_at']) ?></td>
                                <td>
                                    <a href="?page=admin&subpage=admin-orders&edit=<?= $order['id'] ?>"
                                        class="btn small">Sửa</a>
                                    <form action="?page=admin&subpage=admin-orders" method="post" class="delete-form"
                                        style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $order['id'] ?>">
                                        <button type="submit" class="btn small danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
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