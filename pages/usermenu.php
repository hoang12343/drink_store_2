<!-- includes/usermenu.php -->
<div class="user-menu" id="userMenu">
    <button class="user-toggle">
        <i class="fas fa-user"></i>
        <span class="username"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
        <i class="fas fa-caret-down"></i>
    </button>
    <div class="dropdown-menu">
        <a href="?page=profile"><i class="fas fa-user-circle"></i> Hồ sơ</a>
        <a href="?page=orders"><i class="fas fa-shopping-bag"></i> Đơn hàng</a>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
        <a href="?page=admin"><i class="fas fa-cogs"></i> Quản trị</a>
        <?php endif; ?>
        <a href="?page=logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
    </div>
</div>