<link rel="stylesheet" href="assets/css/register.css">
<div class="content" id="registerPage">
    <div class="form-container">
        <h2>Đăng ký</h2>
        <?php if (isset($_GET['error'])): ?>
        <div class="form-message error">
            <?php
                echo match ($_GET['error']) {
                    'validation' => 'Thông tin không hợp lệ, vui lòng kiểm tra lại.',
                    'username_exists' => 'Tên đăng nhập đã tồn tại.',
                    'email_exists' => 'Email đã được sử dụng.',
                    'system' => 'Lỗi hệ thống, vui lòng thử lại sau.',
                    default => 'Lỗi không xác định.'
                };
                ?>
        </div>
        <?php endif; ?>
        <form method="POST" action="processes/process_register.php">
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="full_name"
                    value="<?= htmlspecialchars($_SESSION['register_input']['full_name'] ?? '') ?>" required>
                <span class="error"><?= $_SESSION['register_errors']['full_name'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username"
                    value="<?= htmlspecialchars($_SESSION['register_input']['username'] ?? '') ?>" required>
                <span class="error"><?= $_SESSION['register_errors']['username'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email"
                    value="<?= htmlspecialchars($_SESSION['register_input']['email'] ?? '') ?>" required>
                <span class="error"><?= $_SESSION['register_errors']['email'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="tel" name="phone"
                    value="<?= htmlspecialchars($_SESSION['register_input']['phone'] ?? '') ?>" required>
                <span class="error"><?= $_SESSION['register_errors']['phone'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label>Địa chỉ</label>
                <input type="text" name="address"
                    value="<?= htmlspecialchars($_SESSION['register_input']['address'] ?? '') ?>" required>
                <span class="error"><?= $_SESSION['register_errors']['address'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" required>
                <span class="error"><?= $_SESSION['register_errors']['password'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label>Xác nhận mật khẩu</label>
                <input type="password" name="confirm_password" required>
                <span class="error"><?= $_SESSION['register_errors']['confirm_password'] ?? '' ?></span>
            </div>
            <button type="submit" class="form-submit">Đăng ký</button>
        </form>
        <div class="form-link">
            <p>Đã có tài khoản? <a href="?page=login">Đăng nhập</a></p>
        </div>
    </div>
</div>
<?php unset($_SESSION['register_errors'], $_SESSION['register_input']); ?>