<?php
if (!defined('APP_START')) {
    exit('No direct access');
}
?>

<aside class="management-sidebar test-sidebar">
    <div class="sidebar-header">
        <h2>Quản trị</h2>
        <button id="management-sidebar-close" title="Đóng menu">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="sidebar-menu">
        <div class="menu-category">
            <h3>Quản lý</h3>
            <ul>
                <li><a href="?page=admin&subpage=dashboard"
                        class="<?php echo $admin_subpage === 'dashboard' ? 'active' : ''; ?>"><i
                            class="fas fa-tachometer-alt"></i> Tổng quan</a></li>
                <li><a href="?page=admin&subpage=admin-products"
                        class="<?php echo $admin_subpage === 'admin-products' ? 'active' : ''; ?>"><i
                            class="fas fa-box-open"></i> Sản phẩm</a></li>
                <li><a href="?page=admin&subpage=admin-inventory"
                        class="<?php echo $admin_subpage === 'admin-inventory' ? 'active' : ''; ?>"><i
                            class="fas fa-warehouse"></i> Kho hàng</a></li>
                <li><a href="?page=admin&subpage=admin-orders"
                        class="<?php echo $admin_subpage === 'admin-orders' ? 'active' : ''; ?>"><i
                            class="fas fa-shopping-cart"></i> Đơn hàng</a></li>
                <li><a href="?page=admin&subpage=admin-users"
                        class="<?php echo $admin_subpage === 'admin-users' ? 'active' : ''; ?>"><i
                            class="fas fa-users"></i> Người dùng</a></li>
                <li><a href="?page=admin&subpage=admin-contacts"
                        class="<?php echo $admin_subpage === 'admin-contacts' ? 'active' : ''; ?>"><i
                            class="fas fa-envelope"></i> Liên hệ</a></li>
                <li><a href="?page=admin&subpage=admin-reports"
                        class="<?php echo $admin_subpage === 'admin-reports' ? 'active' : ''; ?>"><i
                            class="fas fa-chart-bar"></i> Báo cáo</a></li>
                <li>
                    <a href="?page=admin&subpage=admin-comments"
                        class="<?php echo $admin_subpage === 'admin-comments' ? 'active' : ''; ?>">
                        <i class="fas fa-comment"></i> Quản lý bình luận
                    </a>
                </li>
                <li><a href="?page=admin&subpage=admin-settings"
                        class="<?php echo $admin_subpage === 'admin-settings' ? 'active' : ''; ?>"><i
                            class="fas fa-cog"></i> Cài đặt</a></li>
                <li><a href="?page=admin&subpage=admin-promo-codes"
                        class="<?php echo $admin_subpage === 'admin-promo-codes' ? 'active' : ''; ?>"><i
                            class="fas fa-ticket-alt"></i> Mã giảm giá</a></li>
            </ul>
        </div>
    </div>
</aside>