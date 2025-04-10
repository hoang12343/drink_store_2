<link rel="stylesheet" href="assets/css/login.css">
<div class="content" id="loginPage">
    <div class="form-container">
        <h2>Đăng nhập</h2>
        <?php if (isset($_SESSION['login_error'])): ?>
        <div class="form-message error">
            <?php
                $message = match ($_SESSION['login_error']) {
                    'empty' => 'Vui lòng nhập đầy đủ thông tin',
                    'invalid' => 'Tên đăng nhập hoặc mật khẩu không đúng',
                    'system' => 'Lỗi hệ thống, vui lòng thử lại sau',
                    default => 'Đã xảy ra lỗi không xác định'
                };
                echo $message;
                unset($_SESSION['login_error']);
                ?>
        </div>
        <?php elseif (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
        <div class="form-message success">Đăng ký thành công! Vui lòng đăng nhập.</div>
        <?php elseif (isset($_GET['timeout']) && $_GET['timeout'] === '1'): ?>
        <div class="form-message error">Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.</div>
        <?php endif; ?>

        <form method="POST" action="processes/process_login.php" id="loginForm">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" required
                    value="<?= htmlspecialchars($_SESSION['last_username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="form-submit">Đăng nhập</button>
        </form>
        <div class="form-link">
            <p>Chưa có tài khoản? <a href="?page=register">Đăng ký ngay</a></p>
            <p><a href="?page=forgot-password">Quên mật khẩu?</a></p>
        </div>
    </div>
</div>