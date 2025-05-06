<?php
ob_start(); // Bắt đầu bộ đệm đầu ra
if (!defined('APP_START')) {
    exit('No direct access');
}

require_once ROOT_PATH . '/includes/db_connect.php';

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $start_date = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: date('Y-m-01');
    $end_date = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: date('Y-m-t');

    // Validate date range
    if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
        ob_end_clean();
        exit('Định dạng ngày không hợp lệ.');
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        ob_end_clean();
        exit('Ngày kết thúc phải sau ngày bắt đầu.');
    }

    try {
        // Fetch users for export
        $stmt = $pdo->prepare("
            SELECT id, full_name, username, email, phone, is_admin, created_at 
            FROM users 
            WHERE created_at BETWEEN :start_date AND :end_date 
            ORDER BY created_at DESC
        ");
        $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date . ' 23:59:59']);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Xóa mọi đầu ra trước đó
        ob_clean();

        // Set headers for CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="user_report_' . $start_date . '_to_' . $end_date . '.csv"');

        // Output CSV
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel compatibility

        // Định nghĩa tiêu đề với định dạng rõ ràng
        fputcsv($output, [
            'ID',
            'Họ và tên',
            'Tên đăng nhập',
            'Email',
            'Số điện thoại',
            'Quyền',
            'Ngày đăng ký'
        ]);

        // Ghi dữ liệu, định dạng số điện thoại và ngày tháng
        foreach ($users as $user) {
            fputcsv($output, [
                $user['id'],
                $user['full_name'],
                $user['username'],
                $user['email'],
                '"' . $user['phone'] . '"', // Thêm dấu ngoặc kép để Excel nhận là văn bản
                $user['is_admin'] ? 'Admin' : 'User',
                DateTime::createFromFormat('Y-m-d H:i:s', $user['created_at'])->format('d/m/Y H:i:s') // Định dạng ngày tháng
            ]);
        }

        fclose($output);
        ob_end_flush(); // Gửi đầu ra và dừng bộ đệm
        exit;
    } catch (PDOException $e) {
        ob_end_clean();
        error_log("CSV export error: " . $e->getMessage());
        exit('Lỗi khi xuất dữ liệu CSV. Vui lòng thử lại sau.');
    }
}

// Xóa bộ đệm nếu không xuất CSV
ob_end_clean();

// Initialize variables
$success_message = '';
$error_message = '';
$start_date = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: date('Y-m-01');
$end_date = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: date('Y-m-t');
$users = [];
$total_users = 0;
$admin_count = 0;
$user_count = 0;

// Validate date range
if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
    $error_message = 'Định dạng ngày không hợp lệ.';
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
} elseif (strtotime($end_date) < strtotime($start_date)) {
    $error_message = 'Ngày kết thúc phải sau ngày bắt đầu.';
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
}

// Fetch report data
try {
    // Total users in date range
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users WHERE created_at BETWEEN :start_date AND :end_date");
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date . ' 23:59:59']);
    $total_users = $stmt->fetchColumn();

    // Admin vs User count
    $stmt = $pdo->prepare("
        SELECT is_admin, COUNT(*) as count 
        FROM users 
        WHERE created_at BETWEEN :start_date AND :end_date 
        GROUP BY is_admin
    ");
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date . ' 23:59:59']);
    $role_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($role_counts as $row) {
        if ($row['is_admin'] == 1) {
            $admin_count = $row['count'];
        } else {
            $user_count = $row['count'];
        }
    }

    // Recent users (limited to 50 for display)
    $stmt = $pdo->prepare("
        SELECT id, full_name, username, email, phone, is_admin, created_at 
        FROM users 
        WHERE created_at BETWEEN :start_date AND :end_date 
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date . ' 23:59:59']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Lỗi khi lấy dữ liệu báo cáo: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo Người dùng</title>
    <link rel="stylesheet" href="assets/css/admin/admin-variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-content.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-reports.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="assets/js/admin/admin-reports.js" defer></script>
</head>

<body>
    <section class="content admin-page">
        <h1>Báo cáo Người dùng</h1>

        <?php if ($success_message): ?>
            <div class="form-message success"><?= htmlspecialchars($success_message) ?></div>
        <?php elseif ($error_message): ?>
            <div class="form-message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Filter Form -->
        <div class="admin-users">
            <h2>Lọc Báo cáo</h2>
            <form action="?page=admin&subpage=admin-reports" method="get" class="report-filter-form">
                <input type="hidden" name="page" value="admin">
                <input type="hidden" name="subpage" value="admin-reports">
                <div class="form-group">
                    <label for="start_date">Từ ngày</label>
                    <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"
                        required>
                </div>
                <div class="form-group">
                    <label for="end_date">Đến ngày</label>
                    <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"
                        required>
                </div>
                <button type="submit" class="btn">Lọc</button>
                <a href="?page=admin&subpage=admin-reports&start_date=<?= htmlspecialchars($start_date) ?>&end_date=<?= htmlspecialchars($end_date) ?>&export=csv"
                    class="btn btn-export">
                    <i class="fas fa-download"></i> Xuất CSV
                </a>
            </form>
        </div>

        <!-- Report Summary -->
        <div class="admin-users">
            <h2>Tóm tắt Báo cáo</h2>
            <div class="report-summary">
                <div class="summary-card">
                    <i class="fas fa-users"></i>
                    <h3>Tổng người dùng</h3>
                    <p><?= $total_users ?></p>
                </div>
                <div class="summary-card">
                    <i class="fas fa-user-shield"></i>
                    <h3>Quản trị viên</h3>
                    <p><?= $admin_count ?></p>
                </div>
                <div class="summary-card">
                    <i class="fas fa-user"></i>
                    <h3>Người dùng thường</h3>
                    <p><?= $user_count ?></p>
                </div>
            </div>
        </div>

        <!-- Recent Users Table -->
        <div class="admin-users">
            <h2>Người dùng gần đây</h2>
            <?php if (empty($users)): ?>
                <p>Không có người dùng nào trong khoảng thời gian này.</p>
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
                            <th>Ngày đăng ký</th>
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
                                <td><?= htmlspecialchars($user['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>