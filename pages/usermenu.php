<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    exit('Bạn cần đăng nhập để xem menu này.');
}
?>

<div class="user-menu">
    <div class="user-info">
        <span class="username">
            <i class="fas fa-user-circle"></i>
            Xin chào, <?= htmlspecialchars($_SESSION['username'] ?? 'Khách') ?>
        </span>
        <button class="dropdown-toggle" id="userMenuToggle" aria-label="Toggle User Menu">
            <i class="fas fa-caret-down"></i>
        </button>
    </div>
    <div class="dropdown-menu" id="userDropdown">
        <ul>
            <li><a href="?page=account">Tài khoản của tôi</a></li>
            <li><a href="processes/logout.php">Đăng xuất</a></li>
        </ul>
    </div>
</div>

<!-- Liên kết CSS và JS -->
<link rel="stylesheet" href="assets/css/usermenu.css">
<script src="assets/js/usermenu.js"></script>