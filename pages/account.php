<link rel="stylesheet" href="assets/css/register.css?v=<?php echo time(); ?>">
<div class="content" id="accountPage">
    <div class="form-container">
        <h2>Cập nhật thông tin tài khoản</h2>
        <?php if (isset($_GET['error'])): ?>
        <div class="form-message error">
            <?php
                echo match ($_GET['error']) {
                    'validation' => 'Thông tin không hợp lệ, vui lòng kiểm tra lại.',
                    'email_exists' => 'Email đã được sử dụng.',
                    'system' => 'Lỗi hệ thống, vui lòng thử lại sau.',
                    default => 'Lỗi không xác định.'
                };
                ?>
        </div>
        <?php elseif (isset($_GET['success']) && $_GET['success'] === 'updated'): ?>
        <div class="form-message success">
            Cập nhật thông tin thành công!
        </div>
        <?php endif; ?>
        <form method="POST" action="processes/process_update_profile.php" id="updateProfileForm">
            <div class="form-group">
                <label for="full_name">Họ và tên</label>
                <input type="text" name="full_name" id="full_name"
                    value="<?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>" required>
                <span class="error" id="fullNameError"></span>
            </div>
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" name="username" id="username"
                    value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" required readonly>
                <span class="error" id="usernameError"></span>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>"
                    required>
                <span class="error" id="emailError"></span>
            </div>
            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>"
                    required>
                <span class="error" id="phoneError"></span>
            </div>
            <div class="form-group">
                <label for="address">Địa chỉ</label>
                <input type="text" name="address" id="address"
                    value="<?= htmlspecialchars($_SESSION['address'] ?? '') ?>" required>
                <span class="error" id="addressError"></span>
            </div>
            <button type="submit" class="form-submit">Cập nhật</button>
        </form>
        <div class="form-link">
            <p>Quay lại <a href="?page=home">Trang chủ</a></p>
        </div>
    </div>
</div>
<script src="assets/js/account.js" defer></script>