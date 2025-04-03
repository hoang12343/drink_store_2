<link rel="stylesheet" href="assets/css/login.css">
<div class="content" id="loginPage">
    <div class="form-container">
        <h2 class="form-title">Đăng nhập</h2>
        <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
            <div class="form-message error">Tên đăng nhập hoặc mật khẩu không chính xác!</div>
        <?php elseif (isset($_GET['logout']) && $_GET['logout'] == 1): ?>
            <div class="form-message success">Đăng xuất thành công!</div>
        <?php endif; ?>
        <form id="loginForm" method="POST" action="processes/process_login.php">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
                <div class="error" id="usernameError"></div>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                <div class="error" id="passwordError"></div>
            </div>
            <button type="submit" class="form-submit">Đăng nhập</button>
        </form>
        <p class="form-link">Chưa có tài khoản? <a href="?page=register">Đăng ký ngay</a></p>
    </div>
</div>