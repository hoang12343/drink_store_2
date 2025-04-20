<?php
define('APP_START', true);
define('ROOT_PATH', __DIR__);

// Kết nối database và khởi tạo session
require_once 'includes/db_connect.php';
require_once 'includes/session_start.php';

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
    $valid_pages = ['home', 'products', 'product-detail', 'cart', 'contact', 'about', 'login', 'register', 'logout', 'knowledge', 'gift', 'promotion', 'profile', 'orders', 'admin'];
    $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? $default;

    // Handle admin subpages
    if ($page === 'admin') {
        $admin_subpage = filter_input(INPUT_GET, 'subpage', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'dashboard';
        $valid_admin_subpages = ['dashboard', 'admin-products'];
        if (!in_array($admin_subpage, $valid_admin_subpages)) {
            $admin_subpage = 'dashboard';
        }
        return "admin/$admin_subpage";
    }

    return in_array($page, $valid_pages) ? $page : $default;
}

$page = route_request();
$current_page = $page;

// Yêu cầu đăng nhập cho trang người dùng
if (in_array($page, ['cart', 'profile', 'orders']) && !isset($_SESSION['logged_in'])) {
    header('Location: index.php?page=login&redirect=' . urlencode($page));
    exit;
}

// Yêu cầu quyền admin
if (str_starts_with($page, 'admin/') && (!isset($_SESSION['logged_in']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1)) {
    header('Location: index.php?page=login&redirect=' . urlencode('admin'));
    exit;
}

// Xử lý đăng xuất
if ($page === 'logout') {
    require_once 'processes/logout.php';
    exit;
}

// Bao gồm header hoặc usermenu cho admin
if (str_starts_with($current_page, 'admin/') && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    $usermenu_file = ROOT_PATH . '/includes/usermenu.php';
    if (file_exists($usermenu_file)) {
        include $usermenu_file;
    } else {
        error_log('Missing usermenu.php at ' . $usermenu_file);
        echo '<div style="margin: 10px; background: #8B1E3F; color: white; padding: 8px 15px; border-radius: 4px; display: inline-block;"><a href="?page=logout" style="color: white; text-decoration: none;">Đăng xuất</a></div>';
    }
} else {
    $header_file = ROOT_PATH . '/includes/header.php';
    if (file_exists($header_file)) {
        include $header_file;
    } else {
        error_log('Missing header.php at ' . $header_file);
        die('Lỗi: Không tìm thấy file header tại ' . $header_file);
    }
}
?>

<div class="container">
    <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
        <div class="form-message success">Đăng ký thành công! Vui lòng đăng nhập.</div>
    <?php elseif (isset($_GET['timeout']) && $_GET['timeout'] === '1'): ?>
        <div class="form-message error">Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.</div>
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
</div>

<?php
// Bao gồm footer
$footer_file = ROOT_PATH . '/includes/footer.php';
if (file_exists($footer_file)) {
    include $footer_file;
} else {
    error_log('Missing footer.php at ' . $footer_file);
    echo '<p>Lỗi: Không tìm thấy file footer tại ' . $footer_file . '</p>';
}

// Liên kết JavaScript
if ($page === 'cart'): ?>
    <script src="assets/js/cart.js" defer></script>
<?php elseif (str_starts_with($page, 'admin/')): ?>
    <script src="assets/js/admin.js" defer></script>
<?php endif; ?>