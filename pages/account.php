<?php
if (!defined('APP_START')) exit('No direct access');

// Đảm bảo người dùng đã đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php?page=login&redirect=account');
    exit;
}

// Lấy thông tin người dùng từ cơ sở dữ liệu
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, username, email, phone, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy người dùng, chuyển hướng về trang chủ
if (!$user) {
    header('Location: index.php?page=home');
    exit;
}

// Xác định section (profile hoặc password)
$section = filter_input(INPUT_GET, 'section', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'profile';

// Gán thông tin người dùng vào session để hiển thị lại nếu có lỗi
$_SESSION['account_input'] = $_SESSION['account_input'] ?? $user;
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="../assets/css/account.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <div class="content" id="accountPage">
        <div class="account-container">
            <h2>Tài khoản</h2>
            <div class="account-nav">
                <a href="?page=account§ion=profile" class="<?= $section === 'profile' ? 'active' : '' ?>">
                    <i class="fas fa-user-circle"></i> Hồ sơ
                </a>
                <a href="?page=account§ion=password" class="<?= $section === 'password' ? 'active' : '' ?>">
                    <i class="fas fa-key"></i> Đổi mật khẩu
                </a>
            </div>
            <div class="account-content">
                <?php if (isset($_GET['error'])): ?>
                <div class="form-message error">
                    <?php
                        echo match ($_GET['error']) {
                            'validation' => 'Thông tin không hợp lệ, vui lòng kiểm tra lại.',
                            'email_exists' => 'Email đã được sử dụng bởi người khác.',
                            'current_password' => 'Mật khẩu hiện tại không đúng.',
                            'system' => 'Lỗi hệ thống, vui lòng thử lại sau.',
                            default => 'Lỗi không xác định.'
                        };
                        ?>
                </div>
                <?php elseif (isset($_GET['success'])): ?>
                <div class="form-message success">
                    <?php echo $section === 'profile' ? 'Cập nhật hồ sơ thành công!' : 'Đổi mật khẩu thành công!'; ?>
                </div>
                <?php endif; ?>

                <?php if ($section === 'profile'): ?>
                <form method="POST" action="../processes/process_account.php?section=profile" id="updateProfileForm">
                    <div class="form-group">
                        <label for="full_name">Họ và tên</label>
                        <input type="text" name="full_name" id="full_name"
                            value="<?= htmlspecialchars($_SESSION['account_input']['full_name'] ?? $user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                            required>
                        <span class="error"
                            id="fullNameError"><?= htmlspecialchars($_SESSION['account_errors']['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="form-group">
                        <label for="username">Tên đăng nhập</label>
                        <input type="text" name="username" id="username"
                            value="<?= htmlspecialchars($_SESSION['account_input']['username'] ?? $user['username'], ENT_QUOTES, 'UTF-8') ?>"
                            required readonly>
                        <span class="error"
                            id="usernameError"><?= htmlspecialchars($_SESSION['account_errors']['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email"
                            value="<?= htmlspecialchars($_SESSION['account_input']['email'] ?? $user['email'], ENT_QUOTES, 'UTF-8') ?>"
                            required>
                        <span class="error"
                            id="emailError"><?= htmlspecialchars($_SESSION['account_errors']['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="tel" name="phone" id="phone"
                            value="<?= htmlspecialchars($_SESSION['account_input']['phone'] ?? $user['phone'], ENT_QUOTES, 'UTF-8') ?>"
                            required>
                        <span class="error"
                            id="phoneError"><?= htmlspecialchars($_SESSION['account_errors']['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="form-group">
                        <label for="address">Địa chỉ</label>
                        <input type="text" name="address" id="address"
                            value="<?= htmlspecialchars($_SESSION['account_input']['address'] ?? $user['address'], ENT_QUOTES, 'UTF-8') ?>"
                            required>
                        <span class="error"
                            id="addressError"><?= htmlspecialchars($_SESSION['account_errors']['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <button type="submit" class="form-submit">Cập nhật</button>
                </form>
                <?php elseif ($section === 'password'): ?>
                <form method="POST" action="../processes/process_account.php?section=password" id="changePasswordForm">
                    <div class="form-group">
                        <label for="current_password">Mật khẩu hiện tại</label>
                        <div class="password-wrapper">
                            <input type="password" name="current_password" id="current_password" required>
                            <button type="button" class="toggle-password" data-target="current_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span class="error"
                            id="currentPasswordError"><?= htmlspecialchars($_SESSION['account_errors']['current_password'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Mật khẩu mới</label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" id="new_password"
                                value="<?= htmlspecialchars($_SESSION['account_input']['new_password'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                required>
                            <button type="button" class="toggle-password" data-target="new_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span class="error"
                            id="newPasswordError"><?= htmlspecialchars($_SESSION['account_errors']['new_password'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu mới</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password"
                                value="<?= htmlspecialchars($_SESSION['account_input']['confirm_password'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                required>
                            <button type="button" class="toggle-password" data-target="confirm_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span class="error"
                            id="confirmPasswordError"><?= htmlspecialchars($_SESSION['account_errors']['confirm_password'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <button type="submit" class="form-submit">Cập nhật</button>
                </form>
                <?php endif; ?>
                <div class="form-link">
                    <p>Quay lại <a href="?page=home">Trang chủ</a></p>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['account_errors'], $_SESSION['account_input']); ?>
    <script src="../assets/js/account.js" defer></script>
</body>

</html>