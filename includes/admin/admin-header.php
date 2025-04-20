<?php
if (!defined('APP_START')) {
    exit('No direct access');
}
?>

<div class="admin-header">
    <button id="sidebar-toggle" title="Toggle Sidebar">
        <i class="fas fa-bars"></i>
    </button>
    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
        <span class="admin-username">
            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
        </span>
        <a href="?page=logout" class="admin-logout">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    <?php else: ?>
        <a href="?page=login" class="admin-login">
            <i class="fas fa-user"></i> Đăng nhập
        </a>
    <?php endif; ?>
</div>