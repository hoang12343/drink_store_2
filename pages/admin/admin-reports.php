<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

// Initialize output buffering
ob_start();

// Include database connection
require_once ROOT_PATH . '/includes/db_connect.php';

// Verify and include Composer autoloader
$autoload_path = ROOT_PATH . '/vendor/autoload.php';
if (!file_exists($autoload_path)) {
    ob_end_clean();
    exit('Composer autoloader not found at ' . htmlspecialchars($autoload_path) . '. Run "composer install".');
}
require_once $autoload_path;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;
// Uncomment the following line if using Dompdf
// use Dompdf\Dompdf;

// Debug Mpdf class availability
if (!class_exists('Mpdf\Mpdf')) {
    ob_end_clean();
    exit('Mpdf class not found. Run "composer require mpdf/mpdf" or check vendor/mpdf/mpdf directory.');
}

// Handle export requests
if (isset($_GET['export'])) {
    $start_date = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: date('Y-m-01');
    $end_date = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: date('Y-m-t');

    // Validate dates
    if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
        ob_end_clean();
        exit('Invalid date format.');
    }
    if (strtotime($end_date) < strtotime($start_date)) {
        ob_end_clean();
        exit('End date must be after start date.');
    }

    try {
        // Fetch user data
        $stmt = $pdo->prepare("
            SELECT id, full_name, username, email, phone, is_admin, created_at 
            FROM users 
            WHERE created_at BETWEEN :start_date AND :end_date 
            ORDER BY created_at DESC
        ");
        $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date . ' 23:59:59']);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Clear all output buffers for export
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if ($_GET['export'] === 'xlsx') {
            // Excel Export
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = ['ID', 'Họ và tên', 'Tên đăng nhập', 'Email', 'Số điện thoại', 'Quyền', 'Ngày đăng ký'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getColumnDimension($col)->setWidth($col === 'D' ? 25 : ($col === 'G' ? 20 : 15));
                $col++;
            }
            $sheet->getStyle('G2:G' . (count($users) + 1))->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm:ss');

            $row = 2;
            foreach ($users as $user) {
                $sheet->setCellValue('A' . $row, $user['id']);
                $sheet->setCellValue('B' . $row, $user['full_name']);
                $sheet->setCellValue('C' . $row, $user['username']);
                $sheet->setCellValue('D' . $row, $user['email']);
                $sheet->setCellValue('E' . $row, $user['phone']);
                $sheet->setCellValue('F' . $row, $user['is_admin'] ? 'Admin' : 'User');
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $user['created_at']);
                $sheet->setCellValue('G' . $row, $date ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($date) : 'N/A');
                $row++;
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="user_report_' . $start_date . '_to_' . $end_date . '.xlsx"');
            header('Cache-Control: max-age=0');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } elseif ($_GET['export'] === 'pdf') {
            // mPDF PDF Export
            $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            $html .= '<style>body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid black; padding: 5px; } th { text-align: center; font-weight: bold; } td.center { text-align: center; }</style>';
            $html .= '</head><body>';
            $html .= '<h1 style="text-align:center;">Báo cáo Người dùng (' . htmlspecialchars($start_date) . ' đến ' . htmlspecialchars($end_date) . ')</h1>';
            $html .= '<table>';
            $html .= '<tr><th>ID</th><th>Họ và tên</th><th>Tên đăng nhập</th><th>Email</th><th>Số điện thoại</th><th>Quyền</th><th>Ngày đăng ký</th></tr>';
            foreach ($users as $user) {
                $html .= '<tr>';
                $html .= '<td class="center">' . htmlspecialchars($user['id']) . '</td>';
                $html .= '<td>' . htmlspecialchars($user['full_name']) . '</td>';
                $html .= '<td>' . htmlspecialchars($user['username']) . '</td>';
                $html .= '<td>' . htmlspecialchars($user['email']) . '</td>';
                $html .= '<td class="center">' . htmlspecialchars($user['phone']) . '</td>';
                $html .= '<td class="center">' . ($user['is_admin'] ? 'Admin' : 'User') . '</td>';
                $html .= '<td class="center">' . htmlspecialchars($user['created_at']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table></body></html>';
            $mpdf->WriteHTML($html);
            $mpdf->Output('user_report_' . $start_date . '_to_' . $end_date . '.pdf', 'D');
            exit;

            /* Alternative Dompdf PDF Export (uncomment to use)
            $dompdf = new Dompdf(['enable_remote' => false, 'isHtml5ParserEnabled' => true]);
            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            $html .= '<style>body { font-family: times, serif; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid black; padding: 5px; } th { text-align: center; } td.center { text-align: center; }</style>';
            $html .= '</head><body>';
            $html .= '<h1 style="text-align:center;">Báo cáo Người dùng (' . htmlspecialchars($start_date) . ' đến ' . htmlspecialchars($end_date) . ')</h1>';
            $html .= '<table>';
            $html .= '<tr><th>ID</th><th>Họ và tên</th><th>Tên đăng nhập</th><th>Email</th><th>Số điện thoại</th><th>Quyền</th><th>Ngày đăng ký</th></tr>';
            foreach ($users as $user) {
                $html .= '<tr>';
                $html .= '<td class="center">' . htmlspecialchars($user['id']) . '</td>';
                $html .= '<td>' . htmlspecialchars($user['full_name']) . '</td>';
                $html .= '<td>' . htmlspecialchars($user['username']) . '</td>';
                $html .= '<td>' . htmlspecialchars($user['email']) . '</td>';
                $html .= '<td class="center">' . htmlspecialchars($user['phone']) . '</td>';
                $html .= '<td class="center">' . ($user['is_admin'] ? 'Admin' : 'User') . '</td>';
                $html .= '<td class="center">' . htmlspecialchars($user['created_at']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table></body></html>';
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="user_report_' . $start_date . '_to_' . $end_date . '.pdf"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            $dompdf->stream('user_report_' . $start_date . '_to_' . $end_date . '.pdf', ['Attachment' => true]);
            exit;
            */
        }
    } catch (Exception $e) {
        ob_end_clean();
        error_log('Export error: ' . $e->getMessage());
        exit('Error exporting data: ' . htmlspecialchars($e->getMessage()));
    }
}

// Initialize variables
$start_date = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: date('Y-m-01');
$end_date = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: date('Y-m-t');
$users = [];
$total_users = $admin_count = $user_count = 0;
$error_message = '';

// Validate dates
if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
    $error_message = 'Invalid date format.';
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
} elseif (strtotime($end_date) < strtotime($start_date)) {
    $error_message = 'End date must be after start date.';
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
}

// Fetch report data
try {
    // Total users
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE created_at BETWEEN :start_date AND :end_date");
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date . ' 23:59:59']);
    $total_users = $stmt->fetchColumn();

    // Admin and regular user counts
    $stmt = $pdo->prepare("SELECT is_admin, COUNT(*) as count FROM users WHERE created_at BETWEEN :start_date AND :end_date GROUP BY is_admin");
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date . ' 23:59:59']);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $row['is_admin'] ? $admin_count = $row['count'] : $user_count = $row['count'];
    }

    // Recent users (limited to 50)
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
    $error_message = 'Error fetching report data: ' . $e->getMessage();
    error_log('Database error: ' . $e->getMessage());
}

// Define CSS files
$css_files = [
    'assets/css/admin/admin-variables.css',
    'assets/css/admin/admin-header.css',
    'assets/css/admin/admin-sidebar.css',
    'assets/css/admin/admin-content.css',
    'assets/css/admin/admin-reports.css'
];

// Clear output buffer before rendering HTML
ob_end_clean();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo Người dùng</title>
    <?php foreach ($css_files as $css_file) {
        $full_path = ROOT_PATH . '/' . $css_file;
        if (file_exists($full_path)) {
            echo "<link rel='stylesheet' href='" . BASE_URL . $css_file . "'>";
        } else {
            error_log("Missing CSS file: $full_path");
        }
    } ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <section class="content admin-page">
        <h1>Báo cáo Người dùng</h1>
        <?php if ($error_message): ?>
            <div class="form-message error"><i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

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
                <button type="submit" class="btn"><i class="fas fa-filter"></i> Lọc</button>
                <a href="?page=admin&subpage=admin-reports&start_date=<?= htmlspecialchars($start_date) ?>&end_date=<?= htmlspecialchars($end_date) ?>&export=xlsx"
                    class="btn btn-export"><i class="fas fa-file-excel"></i> Xuất Excel</a>
                <a href="?page=admin&subpage=admin-reports&start_date=<?= htmlspecialchars($start_date) ?>&end_date=<?= htmlspecialchars($end_date) ?>&export=pdf"
                    class="btn btn-export"><i class="fas fa-file-pdf"></i> Xuất PDF</a>
            </form>
        </div>

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