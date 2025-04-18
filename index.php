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
    $valid_pages = ['home', 'products', 'product-detail', 'cart', 'contact', 'about', 'login', 'register', 'logout'];
    $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? $default;
    return in_array($page, $valid_pages) ? $page : $default;
}

$page = route_request();
$current_page = $page;

// Yêu cầu đăng nhập để truy cập trang giỏ hàng
if ($page === 'cart' && !isset($_SESSION['logged_in'])) {
    header('Location: index.php?page=login&redirect=' . urlencode($page));
    exit;
}

// Xử lý đăng xuất
if ($page === 'logout') {
    require_once 'processes/logout.php';
    exit;
}

// Bao gồm header
$header_file = 'includes/header.php';
if (file_exists($header_file)) {
    include $header_file;
} else {
    die('Lỗi: Không tìm thấy file header tại ' . $header_file);
}
?>

<div class="container">
    <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
        <div class="form-message success">Đăng ký thành công! Vui lòng đăng nhập.</div>
    <?php elseif (isset($_GET['timeout']) && $_GET['timeout'] === '1'): ?>
        <div class="form-message error">Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.</div>
    <?php endif; ?>

    <?php
    $page_file = "pages/$page.php";
    if (file_exists($page_file)) {
        include_once $page_file;
    } else {
        http_response_code(404);
        include 'pages/404.php';
    }
    ?>
</div>

<?php
// Bao gồm footer
$footer_file = 'includes/footer.php';
if (file_exists($footer_file)) {
    include $footer_file;
} else {
    echo '<p>Lỗi: Không tìm thấy file footer tại ' . $footer_file . '</p>';
}

// Liên kết JavaScript cho trang giỏ hàng
if ($page === 'cart'): ?>
    <script src="assets/js/cart.js" defer></script>
<?php endif; ?>