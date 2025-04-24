<?php
if (!defined('APP_START')) exit('No direct access');
?>

<div class="user-menu" id="userMenu">
    <button class="user-toggle">
        <i class="fas fa-user"></i>
        <span class="username"><?= htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></span>
        <i class="fas fa-caret-down"></i>
    </button>
    <div class="dropdown-menu">
        <a href="?page=update_profile"><i class="fas fa-user-circle"></i> Tài khoản</a>
        <a href="?page=orders"><i class="fas fa-shopping-bag"></i> Đơn hàng</a>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <a href="?page=admin&subpage=dashboard"><i class="fas fa-cog"></i> Quản trị</a>
        <?php endif; ?>
        <a href="?page=logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
    </div>
</div>