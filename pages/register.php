<link rel="stylesheet" href="assets/css/register.css">
<div class="content" id="registerPage">
    <div class="form-container">
        <h2 class="form-title">Đăng ký tài khoản</h2>
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="form-message success">Đăng ký thành công! Vui lòng đăng nhập.</div>
        <?php elseif (isset($_GET['error'])): ?>
        <div class="form-message error">Tên đăng nhập đã tồn tại!</div>
        <?php endif; ?>
        <form id="registerForm" method="POST" action="processes/process_register.php">
            <div class="form-group">
                <label for="full_name">Họ và tên</label>
                <input type="text" id="full_name" name="full_name" placeholder="Nhập họ và tên" required>
                <div class="error" id="fullNameError"></div>
            </div>
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
                <div class="error" id="usernameError"></div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Nhập email" required>
                <div class="error" id="emailError"></div>
            </div>
            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="tel" id="phone" name="phone" placeholder="Nhập số điện thoại" required>
                <div class="error" id="phoneError"></div>
            </div>
            <div class="form-group">
                <label for="address">Địa chỉ</label>
                <input type="text" id="address" name="address" placeholder="Nhập địa chỉ" required>
                <div class="error" id="addressError"></div>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                <div class="error" id="passwordError"></div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Nhập lại mật khẩu</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Xác nhận mật khẩu"
                    required>
                <div class="error" id="confirmPasswordError"></div>
            </div>
            <button type="submit" class="form-submit">Đăng ký</button>
        </form>
        <p class="form-link">Đã có tài khoản? <a href="?page=login">Đăng nhập ngay</a></p>
    </div>
</div>