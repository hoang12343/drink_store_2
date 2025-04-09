<?php
define('APP_START', true);

require_once 'includes/db_connect.php';
require_once 'includes/session_start.php';

function route_request($default = 'home'): string
{
    $valid_pages = ['home', 'products', 'cart', 'contact', 'about', 'login', 'register'];
    $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? $default;
    return in_array($page, $valid_pages) ? $page : $default;
}

$page = route_request();
if ($page === 'cart' && !isset($_SESSION['logged_in'])) {
    header('Location: index.php?page=login&redirect=' . urlencode($page));
    exit;
}

include 'includes/header.php';
?>

<div class="container">
    <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
        <div class="form-message success">Đăng ký thành công! Vui lòng đăng nhập.</div>
    <?php endif; ?>
    <?php
    $page_file = "pages/$page.php";
    if (file_exists($page_file)) {
        include $page_file;
    } else {
        http_response_code(404);
        include 'pages/404.php';
    }
    ?>
</div>

<?php include 'includes/footer.php'; ?>