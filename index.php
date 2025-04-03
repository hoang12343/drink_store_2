<?php
define('APP_START', true);

require_once 'includes/db_connect.php';
require_once 'includes/session_start.php';

function route_request($default = 'home'): string
{
    $valid_pages = ['home', 'products', 'product_detail', 'cart', 'contact', 'about', 'login', 'register'];
    $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? $default;
    return in_array($page, $valid_pages) ? $page : $default;
}

function requires_authentication($page): bool
{
    $auth_required = ['cart'];
    return in_array($page, $auth_required);
}

$page = route_request();
if (requires_authentication($page) && !isset($_SESSION['logged_in'])) {
    header('Location: index.php?page=login&redirect=' . urlencode($page));
    exit;
}

include 'includes/header.php';
?>

<div class="container">
    <?php
    $page_file = "pages/$page.php";
    if (file_exists($page_file)) {
        include $page_file;
    } else {
        http_response_code(404);
        include 'pages/404.php'; // Giả định có file 404.php trong thư mục pages
    }
    ?>
</div>

<?php include 'includes/footer.php'; ?>