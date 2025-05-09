<?php
// Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!defined('APP_START')) {
    exit('No direct access');
}

require_once ROOT_PATH . '/includes/db_connect.php';
require_once ROOT_PATH . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_id = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Debugging
    if (!$contact_id || !$email || !$subject || !$message || !$csrf_token) {
        error_log("Invalid form data: " . print_r($_POST, true));
    }

    if (!$csrf_token || $csrf_token !== $_SESSION['csrf_token']) {
        header('Location: index.php?page=admin&subpage=admin-contacts&error=' . urlencode('CSRF token không hợp lệ'));
        exit;
    }

    if ($contact_id && $email && $subject && $message) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@gmail.com'; // Thay bằng email của bạn
            $mail->Password = 'your_app_password'; // Thay bằng mật khẩu ứng dụng
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('your_email@gmail.com', 'Your Store');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = nl2br(htmlspecialchars($message));

            $mail->send();

            // Cập nhật trạng thái đã đọc
            $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?");
            $stmt->execute([$contact_id]);

            // Xóa cache
            array_map('unlink', glob(ROOT_PATH . '/cache/contacts_*.cache'));

            header('Location: index.php?page=admin&subpage=admin-contacts&success=' . urlencode('Gửi email thành công'));
            exit;
        } catch (Exception $e) {
            error_log("PHPMailer error: " . $e->getMessage());
            header('Location: index.php?page=admin&subpage=admin-contacts&error=' . urlencode('Lỗi gửi email: ' . $e->getMessage()));
            exit;
        }
    } else {
        header('Location: index.php?page=admin&subpage=admin-contacts&error=' . urlencode('Dữ liệu không hợp lệ'));
        exit;
    }
} else {
    header('Location: index.php?page=admin&subpage=admin-contacts');
    exit;
}
