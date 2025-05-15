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
$selected_year = filter_input(INPUT_GET, 'selected_year', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'all';
$total_revenue = 0;
$revenue_data = [];
$yearly_revenue = 0;
$daily_revenue = 0;
$monthly_revenue = 0;
$current_year = date('Y');
$current_date = date('Y-m-d');
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');

// Lấy danh sách các năm có đơn hàng
try {
    $stmt = $pdo->query("SELECT DISTINCT YEAR(created_at) as year FROM orders WHERE status = 'completed' ORDER BY year DESC");
    $available_years = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $available_years = [];
    $error_message = 'Lỗi khi lấy danh sách năm: ' . $e->getMessage();
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

    $stmt = $pdo->query("SELECT COUNT(*) as total_contacts FROM contacts");
    $total_contacts = $stmt->fetchColumn();

    // Tổng doanh thu từ lúc bắt đầu đến hiện tại
    $stmt = $pdo->prepare("
        SELECT SUM(total_amount) as total_revenue, COUNT(id) as order_count
        FROM orders
        WHERE status = 'completed'
    ");
    $stmt->execute();
    $total_revenue_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_revenue = $total_revenue_data['total_revenue'] ?: 0;
    $total_order_count = $total_revenue_data['order_count'] ?: 0;

    // Lấy doanh thu theo ngày/tháng/năm cho biểu đồ
    if ($selected_year !== 'all') {
        $start_date = $selected_year . '-01-01';
        $end_date = $selected_year . '-12-31';
        $filter_type = 'month';
    }

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

    // Lấy doanh thu tháng hiện tại
    $stmt = $pdo->prepare("
        SELECT SUM(total_amount) as monthly_revenue
        FROM orders
        WHERE status = 'completed' AND created_at BETWEEN :month_start AND :month_end
    ");
    $stmt->execute(['month_start' => $current_month_start, 'month_end' => $current_month_end . ' 23:59:59']);
    $monthly_revenue = $stmt->fetchColumn() ?: 0;
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
                <i class="fas fa-envelope"></i>
                <h3>Tổng liên hệ</h3>
                <p><?= $total_contacts ?? 'N/A' ?></p>
                <a href="?page=admin&subpage=admin-contacts" class="btn">Xem chi tiết</a>
            </div>
        </div>

        <div class="admin-dashboard revenue-section">
            <h2>Tổng doanh thu</h2>
            <script>
                // Fallback for exportChart
                window.exportChart = window.exportChart || function() {
                    alert('Chức năng xuất biểu đồ chưa sẵn sàng. Vui lòng thử lại sau.');
                };
            </script>
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
                    <label for="selected_year">Năm</label>
                    <select id="selected_year" name="selected_year" required>
                        <option value="all" <?php echo $selected_year === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                        <?php foreach ($available_years as $year): ?>
                            <option value="<?= htmlspecialchars($year) ?>"
                                <?php echo $selected_year === $year ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($year) ?>
                            </option>
                        <?php endforeach; ?>
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
                <button type="button" class="btn btn-export-chart" onclick="exportChart()">
                    <i class="fas fa-chart-bar"></i> Xuất biểu đồ
                </button>
            </form>

            <div class="revenue-cards">
                <div class="dashboard-card"
                    title="Tổng doanh thu từ lúc bắt đầu kinh doanh đến hiện tại. Số đơn hàng: <?php echo $total_order_count; ?>">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>Tổng doanh thu từ đầu đến nay</h3>
                    <p><?= number_format($total_revenue, 0, ',', '.') ?> VNĐ</p>
                    <a href="?page=admin&subpage=admin-orders&status=completed" class="btn">Xem chi tiết</a>
                </div>
                <div class="dashboard-card" title="Doanh thu năm <?php echo $current_year; ?>">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>Doanh thu năm <?php echo $current_year; ?></h3>
                    <p><?= number_format($yearly_revenue, 0, ',', '.') ?> VNĐ</p>
                    <a href="?page=admin&subpage=admin-orders&status=completed" class="btn">Xem chi tiết</a>
                </div>
                <div class="dashboard-card" title="Doanh thu ngày <?php echo date('d/m/Y'); ?>">
                    <i class="fas fa-calendar-day"></i>
                    <h3>Doanh thu ngày <?php echo date('d/m/Y'); ?></h3>
                    <p><?= number_format($daily_revenue, 0, ',', '.') ?> VNĐ</p>
                    <a href="?page=admin&subpage=admin-orders&status=completed" class="btn">Xem chi tiết</a>
                </div>
                <div class="dashboard-card" title="Doanh thu tháng <?php echo date('m/Y'); ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Doanh thu tháng <?php echo date('m/Y'); ?></h3>
                    <p><?= number_format($monthly_revenue, 0, ',', '.') ?> VNĐ</p>
                    <a href="?page=admin&subpage=admin-orders&status=completed" class="btn">Xem chi tiết</a>
                </div>
            </div>

            <div class="revenue-chart">
                <canvas id="revenueChart"></canvas>
                <script>
                    const revenueData = <?php echo json_encode($revenue_data); ?>;
                    const filterType = '<?php echo $filter_type; ?>';
                    const start_date = '<?php echo $start_date; ?>';
                    const end_date = '<?php echo $end_date; ?>';
                    const selected_year = '<?php echo $selected_year; ?>';
                </script>
            </div>
        </div>
    </section>
</body>

</html>