<?php
ob_start(); // Bắt đầu bộ đệm đầu ra
if (!defined('APP_START')) {
    exit('No direct access');
}

require_once ROOT_PATH . '/includes/db_connect.php';
require_once ROOT_PATH . '/vendor/autoload.php'; // Nạp autoload của Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Handle export
if (isset($_GET['export']) && $_GET['export'] === 'xlsx') {
    $start_date = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: date('Y-m-01');
    $end_date = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: date('Y-m-t');

    if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
        ob_end_clean();
        exit('Định dạng ngày không hợp lệ.');
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        ob_end_clean();
        exit('Ngày kết thúc phải sau ngày bắt đầu.');
    }

    try {
        $stmt = $pdo->prepare("
            SELECT id, full_name, username, email, phone, is_admin, created_at 
            FROM users 
            WHERE created_at BETWEEN :start_date AND :end_date 
            ORDER BY created_at DESC
        ");
        $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date . ' 23:59:59']);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        ob_clean();

        // Tạo spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Định nghĩa tiêu đề
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Họ và tên');
        $sheet->setCellValue('C1', 'Tên đăng nhập');
        $sheet->setCellValue('D1', 'Email');
        $sheet->setCellValue('E1', 'Số điện thoại');
        $sheet->setCellValue('F1', 'Quyền');
        $sheet->setCellValue('G1', 'Ngày đăng ký');

        // Định dạng cột và độ rộng
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(20);

        // Định dạng ngày tháng
        $sheet->getStyle('G2:G' . (count($users) + 1))
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME);

        // Ghi dữ liệu
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $user['id']);
            $sheet->setCellValue('B' . $row, $user['full_name']);
            $sheet->setCellValue('C' . $row, $user['username']);
            $sheet->setCellValue('D' . $row, $user['email']);
            $sheet->setCellValue('E' . $row, $user['phone']);
            $sheet->setCellValue('F' . $row, $user['is_admin'] ? 'Admin' : 'User');
            $sheet->setCellValue('G' . $row, DateTime::createFromFormat('Y-m-d H:i:s', $user['created_at'])->getTimestamp());
            $row++;
        }

        // Xuất file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="user_report_' . $start_date . '_to_' . $end_date . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    } catch (PDOException $e) {
        ob_end_clean();
        error_log("Export error: " . $e->getMessage());
        exit('Lỗi khi xuất dữ liệu. Vui lòng thử lại sau.');
    }
}

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
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users WHERE created_at BETWEEN :start_date AND :end_date");
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date . ' 23:59:59']);
    $total_users = $stmt->fetchColumn();

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

// Define CSS files and check existence
$css_files = [
    'assets/css/admin/admin-variables.css',
    'assets/css/admin/admin-header.css',
    'assets/css/admin/admin-sidebar.css',
    'assets/css/admin/admin-content.css',
    'assets/css/admin/admin-reports.css'
];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo Người dùng</title>
    <?php
    foreach ($css_files as $css_file) {
        $full_path = ROOT_PATH . '/' . $css_file;
        if (file_exists($full_path)) {
            echo "<link rel='stylesheet' href='" . BASE_URL . $css_file . "'>";
        } else {
            error_log("Missing CSS file: $full_path");
        }
    }
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                <a href="?page=admin&subpage=admin-reports&start_date=<?= htmlspecialchars($start_date) ?>&end_date=<?= htmlspecialchars($end_date) ?>&export=xlsx"
                    class="btn btn-export">
                    <i class="fas fa-download"></i> Xuất Excel
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
                    <p><?= htmlspecialchars($total_users) ?></p>
                </div>
                <div class="summary-card">
                    <i class="fas fa-user-shield"></i>
                    <h3>Quản trị viên</h3>
                    <p><?= htmlspecialchars($admin_count) ?></p>
                </div>
                <div class="summary-card">
                    <i class="fas fa-user"></i>
                    <h3>Người dùng thường</h3>
                    <p><?= htmlspecialchars($user_count) ?></p>
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