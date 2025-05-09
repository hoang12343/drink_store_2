<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

require_once ROOT_PATH . '/includes/db_connect.php';
require_once ROOT_PATH . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Khởi tạo biến
$success_message = '';
$error_message = '';
$start_date = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: date('Y-m-01');
$end_date = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: date('Y-m-t');
$filter_type = filter_input(INPUT_GET, 'filter_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'month';
$total_revenue = 0;
$revenue_data = [];
$yearly_revenue = 0;
$daily_revenue = 0;
$current_year = date('Y');
$current_date = date('Y-m-d');

// Xử lý xuất Excel
if (isset($_GET['export']) && $_GET['export'] === 'xlsx') {
    if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
        exit('Định dạng ngày không hợp lệ.');
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        exit('Ngày kết thúc phải sau ngày bắt đầu.');
    }

    try {
        $stmt = $pdo->prepare("
            SELECT o.id, u.full_name, o.total_amount, o.created_at
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.status = 'completed' AND o.created_at BETWEEN :start_date AND :end_date
            ORDER BY o.created_at DESC
        ");
        $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date . ' 23:59:59']);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID Đơn hàng');
        $sheet->setCellValue('B1', 'Khách hàng');
        $sheet->setCellValue('C1', 'Tổng tiền (VNĐ)');
        $sheet->setCellValue('D1', 'Ngày đặt');

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setAutoSize(true);

        $sheet->getStyle('C2:C' . (count($orders) + 1))
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        $sheet->getStyle('D2:D' . (count($orders) + 1))
            ->getNumberFormat()
            ->setFormatCode('dd/mm/yyyy hh:mm:ss');

        $row = 2;
        foreach ($orders as $order) {
            $sheet->setCellValue('A' . $row, $order['id']);
            $sheet->setCellValue('B' . $row, $order['full_name']);
            $sheet->setCellValue('C' . $row, $order['total_amount']);
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $order['created_at']);
            $sheet->setCellValue('D' . $row, $date ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($date) : 'N/A');
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="revenue_report_' . $start_date . '_to_' . $end_date . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    } catch (PDOException $e) {
        error_log("Export error: " . $e->getMessage());
        exit('Lỗi khi xuất dữ liệu. Vui lòng thử lại sau.');
    }
}

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

// Lấy thống kê
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
    $total_products = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT SUM(stock) as total_stock FROM products");
    $total_stock = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query("SELECT COUNT(*) as low_stock FROM products WHERE stock < 10");
    $low_stock = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
    $total_orders = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT SUM(total_amount) as total_revenue
        FROM orders
        WHERE status = 'completed' AND created_at BETWEEN :start_date AND :end_date
    ");
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date . ' 23:59:59']);
    $total_revenue = $stmt->fetchColumn() ?: 0;

    // Lấy doanh thu theo ngày/tháng/năm
    if ($filter_type === 'day') {
        $stmt = $pdo->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as period, SUM(total_amount) as revenue
            FROM orders
            WHERE status = 'completed' AND created_at BETWEEN :start_date AND :end_date
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
            ORDER BY period ASC
        ");
    } elseif ($filter_type === 'month') {
        $stmt = $pdo->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as period, SUM(total_amount) as revenue
            FROM orders
            WHERE status = 'completed' AND created_at BETWEEN :start_date AND :end_date
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY period ASC
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT DATE_FORMAT(created_at, '%Y') as period, SUM(total_amount) as revenue
            FROM orders
            WHERE status = 'completed' AND created_at BETWEEN :start_date AND :end_date
            GROUP BY DATE_FORMAT(created_at, '%Y')
            ORDER BY period ASC
        ");
    }
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date . ' 23:59:59']);
    $revenue_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT SUM(total_amount) as yearly_revenue
        FROM orders
        WHERE status = 'completed' AND YEAR(created_at) = :year
    ");
    $stmt->execute(['year' => $current_year]);
    $yearly_revenue = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->prepare("
        SELECT SUM(total_amount) as daily_revenue
        FROM orders
        WHERE status = 'completed' AND DATE(created_at) = :current_date
    ");
    $stmt->execute(['current_date' => $current_date]);
    $daily_revenue = $stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $error_message = 'Lỗi khi lấy dữ liệu: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển - Quản trị</title>
    <link rel="stylesheet" href="assets/css/admin/admin-variables.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-content.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin/admin-dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="assets/js/admin.js" defer></script>
    <script src="assets/js/admin/dashboard.js" defer></script>
</head>

<body>
    <section class="content admin-page">
        <h1>Tổng quan</h1>

        <?php if (isset($error_message)): ?>
            <div class="form-message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="admin-dashboard">
            <div class="dashboard-card">
                <i class="fas fa-boxes"></i>
                <h3>Tổng sản phẩm</h3>
                <p><?= $total_products ?? 'N/A' ?></p>
                <a href="?page=admin&subpage=admin-products" class="btn">Xem chi tiết</a>
            </div>
            <div class="dashboard-card">
                <i class="fas fa-shopping-cart"></i>
                <h3>Tổng đơn hàng</h3>
                <p><?= $total_orders ?? 'N/A' ?></p>
                <a href="?page=admin&subpage=admin-orders" class="btn">Xem chi tiết</a>
            </div>
            <div class="dashboard-card">
                <i class="fas fa-users"></i>
                <h3>Tổng người dùng</h3>
                <p><?= $total_users ?? 'N/A' ?></p>
                <a href="?page=admin&subpage=admin-users" class="btn">Xem chi tiết</a>
            </div>
            <div class="dashboard-card">
                <i class="fas fa-warehouse"></i>
                <h3>Kho hàng</h3>
                <p>
                    Tổng tồn: <?= $total_stock ?><br>
                    Gần hết: <?= $low_stock ?? 0 ?>
                </p>
                <a href="?page=admin&subpage=admin-inventory" class="btn">Quản lý kho</a>
            </div>
            <div class="dashboard-card">
                <i class="fas fa-money-bill-wave"></i>
                <h3>Tổng doanh thu năm <?php echo $current_year; ?></h3>
                <p><?= number_format($yearly_revenue, 0, ',', '.') ?> VNĐ</p>
                <a href="?page=admin&subpage=admin-orders&status=completed" class="btn">Xem chi tiết</a>
            </div>
            <div class="dashboard-card">
                <i class="fas fa-calendar-day"></i>
                <h3>Doanh thu ngày <?php echo date('d/m/Y'); ?></h3>
                <p><?= number_format($daily_revenue, 0, ',', '.') ?> VNĐ</p>
                <a href="?page=admin&subpage=admin-orders&status=completed" class="btn">Xem chi tiết</a>
            </div>
        </div>

        <div class="admin-dashboard revenue-section">
            <h2>Tổng doanh thu</h2>
            <form action="?page=admin&subpage=dashboard" method="get" class="report-filter-form">
                <input type="hidden" name="page" value="admin">
                <input type="hidden" name="subpage" value="dashboard">
                <div class="form-group">
                    <label for="filter_type">Kiểu hiển thị</label>
                    <select id="filter_type" name="filter_type" required>
                        <option value="day" <?php echo $filter_type === 'day' ? 'selected' : ''; ?>>Theo ngày</option>
                        <option value="month" <?php echo $filter_type === 'month' ? 'selected' : ''; ?>>Theo tháng
                        </option>
                        <option value="year" <?php echo $filter_type === 'year' ? 'selected' : ''; ?>>Theo năm</option>
                    </select>
                </div>
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
                <a href="?page=admin&subpage=dashboard&start_date=<?= htmlspecialchars($start_date) ?>&end_date=<?= htmlspecialchars($end_date) ?>&filter_type=<?= htmlspecialchars($filter_type) ?>&export=xlsx"
                    class="btn btn-export">
                    <i class="fas fa-download"></i> Xuất Excel
                </a>
            </form>

            <div class="dashboard-card">
                <i class="fas fa-money-bill-wave"></i>
                <h3>Tổng doanh thu</h3>
                <p><?= number_format($total_revenue, 0, ',', '.') ?> VNĐ</p>
                <a href="?page=admin&subpage=admin-orders&status=completed" class="btn">Xem chi tiết</a>
            </div>

            <div class="revenue-chart">
                <canvas id="revenueChart"></canvas>
                <script>
                    const revenueData = <?php echo json_encode($revenue_data); ?>;
                    const filterType = '<?php echo $filter_type; ?>';
                    const start_date = '<?php echo $start_date; ?>';
                    const end_date = '<?php echo $end_date; ?>';
                </script>
            </div>
        </div>
    </section>
</body>

</html>