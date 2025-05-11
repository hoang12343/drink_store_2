<?php
// Khởi tạo session và định nghĩa các hằng số
session_start();
define('APP_START', true);
define('ROOT_PATH', __DIR__);

// Định nghĩa BASE_URL động
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');

// Kết nối database
require_once ROOT_PATH . '/includes/db_connect.php';

// Xử lý session timeout (30 phút)
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'index.php?page=login&timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

/**
 * Định tuyến yêu cầu đến trang phù hợp
 * 
 * @param string $default Trang mặc định nếu không có tham số page
 * @return string Tên trang hoặc đường dẫn admin/subpage
 */
function route_request($default = 'home'): string
{
    // Danh sách các trang hợp lệ
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

    // Lấy tham số page
    $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? $default;

    // Xử lý các trang quản trị
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

        // Xử lý request AJAX cho reply_contact
        if ($admin_subpage === 'reply_contact') {
            require_once ROOT_PATH . '/processes/reply_contact.php';
            exit;
        }

        // Trả về subpage hợp lệ hoặc dashboard
        return in_array($admin_subpage, $valid_admin_subpages) ? "admin/$admin_subpage" : 'admin/dashboard';
    }

    // Trả về trang hợp lệ hoặc trang mặc định
    return in_array($page, $valid_pages) ? $page : $default;
}

// Định tuyến trang
$page = route_request();
$current_page = $page;

// Yêu cầu đăng nhập cho trang người dùng
$protected_pages = ['cart', 'profile', 'orders', 'update_profile', 'checkout'];
if (in_array($page, $protected_pages) && !isset($_SESSION['logged_in'])) {
    header('Location: ' . BASE_URL . 'index.php?page=login&redirect=' . urlencode($page));
    exit;
}

// Yêu cầu quyền admin
if (str_starts_with($page, 'admin/') && (!isset($_SESSION['logged_in']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1)) {
    header('Location: ' . BASE_URL . 'index.php?page=login&redirect=' . urlencode('admin'));
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

// Xử lý liên hệ
if ($page === 'contact_process') {
    // Đảm bảo session và APP_START đã được khởi tạo
    if (!defined('APP_START')) {
        define('APP_START', true);
    }
    require_once ROOT_PATH . '/processes/contact_process.php';
    exit;
}

// Bao gồm header
$header_file = str_starts_with($current_page, 'admin/')
    ? ROOT_PATH . '/includes/admin/admin-header.php'
    : ROOT_PATH . '/includes/header.php';
if (file_exists($header_file)) {
    include $header_file;
} else {
    error_log('Missing header file at ' . $header_file);
    die('Lỗi: Không tìm thấy file header.');
}

// Bao gồm sidebar cho admin
if (str_starts_with($current_page, 'admin/')) {
    $sidebar_file = ROOT_PATH . '/includes/admin/management-sidebar.php';
    if (file_exists($sidebar_file)) {
        include $sidebar_file;
    } else {
        error_log('Missing management-sidebar.php at ' . $sidebar_file);
        echo '<p>Lỗi: Không tìm thấy file sidebar.</p>';
    }
}
?>

<div class="container">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="form-message <?php echo $_SESSION['flash_message']['type']; ?>">
            <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
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

    <?php
    $chatbox_file = ROOT_PATH . '/pages/components/chatbox.php';
    if (file_exists($chatbox_file)) {
        include $chatbox_file;
    } else {
        error_log('Missing chatbox.php at ' . $chatbox_file);
        echo '<p>Lỗi: Không tìm thấy file chatbox.</p>';
    }
    ?>
</div>

<?php
// Bao gồm footer cho các trang không phải admin
if (!str_starts_with($current_page, 'admin/')) {
    $footer_file = ROOT_PATH . '/includes/footer.php';
    if (file_exists($footer_file)) {
        include $footer_file;
    } else {
        error_log('Missing footer.php at ' . $footer_file);
        echo '<p>Lỗi: Không tìm thấy file footer.</p>';
    }
}

// Bao gồm JavaScript
$js_files = [];
if ($page === 'cart') {
    $js_files[] = ROOT_PATH . '/assets/js/cart.js';
} elseif (str_starts_with($page, 'admin/')) {
    $js_files[] = ROOT_PATH . '/assets/js/admin.js';
    if ($page === 'admin/admin-reports') {
        $js_files[] = ROOT_PATH . '/assets/js/admin/admin-reports.js';
    } elseif ($page === 'admin/admin-products') {
        $js_files[] = ROOT_PATH . '/assets/js/admin/admin-products.js';
    } elseif ($page === 'admin/admin-contacts') {
        $js_files[] = ROOT_PATH . '/assets/js/admin/admin-contacts.js';
    }
}
// Add JavaScript for product-detail page
if ($page === 'product-detail') {
    $js_files[] = ROOT_PATH . '/assets/js/product-detail.js';
}

foreach ($js_files as $js_file) {
    if (file_exists($js_file)) {
        $relative_path = str_replace(ROOT_PATH, '', $js_file);
        echo "<script src='" . BASE_URL . ltrim($relative_path, '/') . "?v=" . time() . "' defer></script>";
    } else {
        error_log('Missing JavaScript file: ' . $js_file);
    }
}
?>
</body>

</html>