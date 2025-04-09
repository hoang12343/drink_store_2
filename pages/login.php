<link rel="stylesheet" href="assets/css/login.css">
<div class="content" id="loginPage" style="display: none;">
    <div class="form-container">
        <h2>Đăng nhập</h2>
        <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
            <div class="form-message success">Đăng ký thành công! Vui lòng đăng nhập.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="form-message error">
                <?php
                echo match ($_GET['error']) {
                    'empty' => 'Vui lòng nhập đầy đủ thông tin.',
                    'invalid' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
                    'system' => 'Lỗi hệ thống, vui lòng thử lại sau.',
                    default => 'Lỗi không xác định.'
                };
                ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="processes/process_login.php">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Đăng nhập</button>
        </form>
        <p>Chưa có tài khoản? <a href="?page=register" onclick="switchTo('register'); return false;">Đăng ký</a></p>
    </div>
</div>