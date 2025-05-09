<?php
define('APP_START', true);
define('ROOT_PATH', __DIR__);

// Dynamically define BASE_URL to adapt to server directory
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');

// Kết nối database và khởi tạo session
require_once ROOT_PATH . '/includes/db_connect.php';
require_once ROOT_PATH . '/includes/session_start.php';

// Xử lý session timeout (30 phút)
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: index.php?page=login&timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

function route_request($default = 'home'): string
{
    $valid_pages = [
        'home',
        'products',
        'product-detail',
        'cart',
        'contact',
        'contact_process',
        'about',
        'login',
        'register',
        'logout',
        'knowledge',
        'gift',
        'promotion',
        'profile',
        'orders',
        'admin',
        'update_profile',
        'checkout'
    ];
    $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? $default;

    // Handle admin subpages
    if ($page === 'admin') {
        $admin_subpage = filter_input(INPUT_GET, 'subpage', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'dashboard';
        $valid_admin_subpages = [
            'dashboard',
            'admin-products',
            'admin-inventory',
            'admin-orders',
            'admin-users',
            'admin-contacts',
            'admin-reports',
            'admin-settings',
            'reply_contact'
        ];
        return in_array($admin_subpage, $valid_admin_subpages) ? "admin/$admin_subpage" : 'admin/dashboard';
    }

    return in_array($page, $valid_pages) ? $page : $default;
}

$page = route_request();
$current_page = $page;

// Yêu cầu đăng nhập cho trang người dùng
$protected_pages = ['cart', 'profile', 'orders', 'update_profile', 'checkout'];
if (in_array($page, $protected_pages) && !isset($_SESSION['logged_in'])) {
    header('Location: index.php?page=login&redirect=' . urlencode($page));
    exit;
}

// Yêu cầu quyền admin
if (str_starts_with($page, 'admin/') && (!isset($_SESSION['logged_in']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1)) {
    header('Location: index.php?page=login&redirect=' . urlencode('admin'));
    exit;
}

// Xử lý xuất file trước khi bao gồm giao diện
if ($page === 'admin/admin-reports' && isset($_GET['export']) && $_GET['export'] === 'xlsx') {
    $page_file = ROOT_PATH . "/pages/$page.php";
    if (file_exists($page_file)) {
        include_once $page_file;
        exit;
    }
    http_response_code(404);
    include ROOT_PATH . '/pages/404.php';
    exit;
}

// Xử lý đăng xuất
if ($page === 'logout') {
    require_once ROOT_PATH . '/processes/logout.php';
    exit;
}

// Xử lý trang contact_process
if ($page === 'contact_process') {
    require_once ROOT_PATH . '/processes/contact_process.php';
    exit;
}

// Bao gồm header hoặc admin-header và sidebar cho admin
$header_file = str_starts_with($current_page, 'admin/')
    ? ROOT_PATH . '/includes/admin/admin-header.php'
    : ROOT_PATH . '/includes/header.php';

if (!file_exists($header_file)) {
    error_log('Missing header file at ' . $header_file);
    die('Lỗi: Không tìm thấy file header tại ' . $header_file);
}
include $header_file;

if (str_starts_with($current_page, 'admin/')) {
    $sidebar_file = ROOT_PATH . '/includes/admin/management-sidebar.php';
    if (file_exists($sidebar_file)) {
        include $sidebar_file;
    } else {
        error_log('Missing management-sidebar.php at ' . $sidebar_file);
        echo '<p>Lỗi: Không tìm thấy file sidebar tại ' . $sidebar_file . '</p>';
    }
}
?>

<div class="container">
    <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
        <div class="form-message success">Đăng ký thành công! Vui lòng đăng nhập.</div>
    <?php elseif (isset($_GET['timeout']) && $_GET['timeout'] === '1'): ?>
        <div class="form-message error">Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="form-message error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php
    $page_file = ROOT_PATH . "/pages/$page.php";
    if (file_exists($page_file)) {
        include_once $page_file;
    } else {
        http_response_code(404);
        include ROOT_PATH . '/pages/404.php';
    }
    ?>

    <!-- Include Chatbox (tạm thời bỏ điều kiện ẩn trên trang admin để debug) -->
    <?php
    $chatbox_file = ROOT_PATH . './pages/components/chatbox.php';
    if (file_exists($chatbox_file)) {
        include $chatbox_file;
    } else {
        echo '<p style="color: red;">Lỗi: Không tìm thấy file chatbox.php tại ' . htmlspecialchars($chatbox_file) . '</p>';
        error_log('Missing chatbox.php at ' . $chatbox_file);
    }
    ?>
</div>

<?php
// Bao gồm footer cho non-admin pages
if (!str_starts_with($current_page, 'admin/')) {
    $footer_file = ROOT_PATH . '/includes/footer.php';
    if (file_exists($footer_file)) {
        include $footer_file;
    } else {
        error_log('Missing footer.php at ' . $footer_file);
        echo '<p>Lỗi: Không tìm thấy file footer tại ' . $footer_file . '</p>';
    }
}

// Liên kết JavaScript với kiểm tra file tồn tại
$js_files = [];
if ($page === 'cart') {
    $js_files[] = ROOT_PATH . '/assets/js/cart.js';
} elseif (str_starts_with($page, 'admin/')) {
    $js_files[] = ROOT_PATH . '/assets/js/admin.js';
    if ($page === 'admin/admin-reports') {
        $js_files[] = ROOT_PATH . '/assets/js/admin/admin-reports.js';
    } elseif ($page === 'admin/admin-products') {
        $js_files[] = ROOT_PATH . '/assets/js/admin/admin-products.js';
    }
}

foreach ($js_files as $js_file) {
    if (file_exists($js_file)) {
        $relative_path = str_replace(ROOT_PATH, '', $js_file);
        echo "<script src='" . BASE_URL . ltrim($relative_path, '/') . "' defer></script>";
    } else {
        error_log('Missing JavaScript file: ' . $js_file);
    }
}
?>
</body>

</html>