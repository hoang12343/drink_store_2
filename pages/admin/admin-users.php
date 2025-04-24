<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

require_once ROOT_PATH . '/includes/db_connect.php';

// Xử lý các hành động (thêm, sửa, xóa)
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$success_message = '';
$error_message = '';

if ($action) {
    if ($action === 'add' || $action === 'edit') {
        $user_data = [
            'full_name' => htmlspecialchars(trim($_POST['full_name'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'username' => htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => htmlspecialchars(trim($_POST['address'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'is_admin' => filter_input(INPUT_POST, 'is_admin', FILTER_VALIDATE_INT) ?: 0,
            'password' => trim($_POST['password'] ?? '')
        ];

        // Validate email
        if (!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Email không hợp lệ.';
        } else {
            if ($action === 'add') {
                try {
                    // Check for duplicate username
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
                    $stmt->execute(['username' => $user_data['username']]);
                    if ($stmt->fetchColumn() > 0) {
                        $error_message = 'Tên đăng nhập "' . htmlspecialchars($user_data['username']) . '" đã tồn tại. Vui lòng chọn tên khác.';
                    } else {
                        // Hash password
                        $user_data['password'] = password_hash($user_data['password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("
                            INSERT INTO users (full_name, username, email, phone, address, password, is_admin, created_at)
                            VALUES (:full_name, :username, :email, :phone, :address, :password, :is_admin, NOW())
                        ");
                        $stmt->execute($user_data);
                        $success_message = 'Thêm người dùng thành công!';
                    }
                } catch (PDOException $e) {
                    $error_message = 'Lỗi khi thêm người dùng: ' . $e->getMessage();
                }
            } elseif ($action === 'edit') {
                $user_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                if ($user_id) {
                    try {
                        // Check for duplicate username (excluding current user)
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :id");
                        $stmt->execute(['username' => $user_data['username'], 'id' => $user_id]);
                        if ($stmt->fetchColumn() > 0) {
                            $error_message = 'Tên đăng nhập "' . htmlspecialchars($user_data['username']) . '" đã tồn tại. Vui lòng chọn tên khác.';
                        } else {
                            // Tạo mảng tham số cho câu lệnh SQL
                            $params = [
                                'full_name' => $user_data['full_name'],
                                'username' => $user_data['username'],
                                'email' => $user_data['email'],
                                'phone' => $user_data['phone'],
                                'address' => $user_data['address'],
                                'is_admin' => $user_data['is_admin'],
                                'id' => $user_id
                            ];

                            $query = "
                                UPDATE users 
                                SET full_name = :full_name, username = :username, email = :email, 
                                    phone = :phone, address = :address, is_admin = :is_admin
                            ";
                            if (!empty($user_data['password'])) {
                                $params['password'] = password_hash($user_data['password'], PASSWORD_DEFAULT);
                                $query .= ", password = :password";
                            }
                            $query .= " WHERE id = :id";

                            $stmt = $pdo->prepare($query);
                            $stmt->execute($params);
                            $success_message = 'Cập nhật người dùng thành công!';
                        }
                    } catch (PDOException $e) {
                        $error_message = 'Lỗi khi cập nhật người dùng: ' . $e->getMessage();
                    }
                }
            }
        }
    } elseif ($action === 'delete') {
        $user_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($user_id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $success_message = 'Xóa người dùng thành công!';
            } catch (PDOException $e) {
                $error_message = 'Lỗi khi xóa người dùng: ' . $e->getMessage();
            }
        }
    }
}

// Lấy danh sách người dùng với phân trang
$page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT) ?? 1;
$page = max(1, (int)$page);
$users_per_page = 10;

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    $total_users = $stmt->fetchColumn();

    $offset = ($page - 1) * $users_per_page;
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $users_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();

    $total_pages = ceil($total_users / $users_per_page);
} catch (PDOException $e) {
    $error_message = 'Lỗi khi lấy danh sách người dùng: ' . $e->getMessage();
    $users = [];
    $total_users = 0;
    $total_pages = 1;
}

// Lấy dữ liệu người dùng để sửa (nếu có)
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindValue(':id', $edit_id, PDO::PARAM_INT);
        $stmt->execute();
        $edit_user = $stmt->fetch();
    } catch (PDOException $e) {
        $error_message = 'Lỗi khi lấy thông tin người dùng: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người dùng</title>
    <link rel="stylesheet" href="assets/css/admin/admin-variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-content.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-users.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="assets/js/admin/admin-users.js" defer></script>
</head>

<body>
    <section class="content admin-page">
        <h1>Quản lý Người dùng</h1>

        <?php if ($success_message): ?>
            <div class="form-message success"><?= htmlspecialchars($success_message) ?></div>
        <?php elseif ($error_message): ?>
            <div class="form-message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Form thêm/sửa người dùng -->
        <div class="admin-users">
            <div class="form-header">
                <h2><?= $edit_user ? 'Sửa người dùng' : 'Thêm người dùng mới' ?></h2>
                <?php if ($edit_user): ?>
                    <a href="?page=admin&subpage=admin-users" class="btn btn-add-user">
                        <i class="fas fa-plus"></i> Thêm người dùng
                    </a>
                <?php endif; ?>
            </div>
            <form action="?page=admin&subpage=admin-users" method="post" class="user-form">
                <input type="hidden" name="action" value="<?= $edit_user ? 'edit' : 'add' ?>">
                <?php if ($edit_user): ?>
                    <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="full_name">Họ và tên</label>
                    <input type="text" id="full_name" name="full_name" value="<?= $edit_user['full_name'] ?? '' ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" value="<?= $edit_user['username'] ?? '' ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= $edit_user['email'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="text" id="phone" name="phone" value="<?= $edit_user['phone'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Địa chỉ</label>
                    <textarea id="address" name="address"><?= $edit_user['address'] ?? '' ?></textarea>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu <?= $edit_user ? '(Để trống nếu không đổi)' : '' ?></label>
                    <input type="password" id="password" name="password" <?= $edit_user ? '' : 'required' ?>>
                </div>
                <div class="form-group">
                    <label for="is_admin">Quyền</label>
                    <select id="is_admin" name="is_admin" required>
                        <option value="0" <?= ($edit_user && $edit_user['is_admin'] == 0) ? 'selected' : '' ?>>User
                        </option>
                        <option value="1" <?= ($edit_user && $edit_user['is_admin'] == 1) ? 'selected' : '' ?>>Admin
                        </option>
                    </select>
                </div>
                <button type="submit" class="btn"><?= $edit_user ? 'Cập nhật' : 'Thêm người dùng' ?></button>
            </form>
        </div>

        <!-- Tìm kiếm người dùng -->
        <div class="admin-users">
            <h2>Danh sách người dùng</h2>
            <div class="search-container">
                <input type="text" id="user-search" placeholder="Tìm kiếm người dùng theo tên hoặc email..."
                    class="search-input">
            </div>
            <?php if (empty($users)): ?>
                <p>Không có người dùng nào.</p>
            <?php else: ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ và tên</th>
                            <th>Tên đăng nhập</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Quyền</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['phone']) ?></td>
                                <td><?= $user['is_admin'] ? 'Admin' : 'User' ?></td>
                                <td>
                                    <a href="?page=admin&subpage=admin-users&edit=<?= $user['id'] ?>" class="btn small">Sửa</a>
                                    <form action="?page=admin&subpage=admin-users" method="post" class="delete-form"
                                        style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
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