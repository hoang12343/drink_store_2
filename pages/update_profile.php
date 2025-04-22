<?php
if (!defined('APP_START')) exit('No direct access');

// Đảm bảo người dùng đã đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php?page=login&redirect=update_profile');
    exit;
}

// Lấy thông tin người dùng hiện tại từ cơ sở dữ liệu
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, email, phone, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy người dùng, chuyển hướng về trang chủ
if (!$user) {
    header('Location: index.php?page=home');
    exit;
}

// Gán thông tin người dùng vào session để hiển thị lại nếu có lỗi
$_SESSION['update_input'] = $_SESSION['update_input'] ?? $user;
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="assets/css/register.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="content" id="updateProfilePage">
        <div class="form-container">
            <h2>Chỉnh sửa thông tin</h2>
            <?php if (isset($_GET['error'])): ?>
                <div class="form-message error">
                    <?php
                    echo match ($_GET['error']) {
                        'validation' => 'Thông tin không hợp lệ, vui lòng kiểm tra lại.',
                        'email_exists' => 'Email đã được sử dụng bởi người khác.',
                        'system' => 'Lỗi hệ thống, vui lòng thử lại sau.',
                        default => 'Lỗi không xác định.'
                    };
                    ?>
                </div>
            <?php elseif (isset($_GET['success'])): ?>
                <div class="form-message success">
                    Cập nhật thông tin thành công!
                </div>
            <?php endif; ?>
            <form method="POST" action="processes/process_update_profile.php">
                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="full_name"
                        value="<?= htmlspecialchars($_SESSION['update_input']['full_name'] ?? $user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                        required>
                    <span
                        class="error"><?= htmlspecialchars($_SESSION['update_errors']['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email"
                        value="<?= htmlspecialchars($_SESSION['update_input']['email'] ?? $user['email'], ENT_QUOTES, 'UTF-8') ?>"
                        required>
                    <span
                        class="error"><?= htmlspecialchars($_SESSION['update_errors']['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" name="phone"
                        value="<?= htmlspecialchars($_SESSION['update_input']['phone'] ?? $user['phone'], ENT_QUOTES, 'UTF-8') ?>"
                        required>
                    <span
                        class="error"><?= htmlspecialchars($_SESSION['update_errors']['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="address"
                        value="<?= htmlspecialchars($_SESSION['update_input']['address'] ?? $user['address'], ENT_QUOTES, 'UTF-8') ?>"
                        required>
                    <span
                        class="error"><?= htmlspecialchars($_SESSION['update_errors']['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="form-group">
                    <label>Mật khẩu mới (để trống nếu không thay đổi)</label>
                    <input type="password" name="password">
                    <span
                        class="error"><?= htmlspecialchars($_SESSION['update_errors']['password'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="form-group">
                    <label>Xác nhận mật khẩu mới</label>
                    <input type="password" name="confirm_password">
                    <span
                        class="error"><?= htmlspecialchars($_SESSION['update_errors']['confirm_password'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <button type="submit" class="form-submit">Cập nhật</button>
            </form>
            <div class="form-link">
                <p>Quay lại <a href="?page=home">Trang chủ</a></p>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['update_errors'], $_SESSION['update_input']); ?>
</body>

</html>