<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!defined('APP_START')) {
    define('APP_START', true);
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Gửi trả lời thất bại. Vui lòng thử lại!'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Yêu cầu không hợp lệ. Vui lòng sử dụng đúng phương thức!');
    }

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Lỗi kết nối cơ sở dữ liệu. Vui lòng liên hệ quản trị viên!');
    }

    $contact_id = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$contact_id || !$email || !$subject || !$message) {
        throw new Exception('Dữ liệu không hợp lệ. Vui lòng kiểm tra lại thông tin!');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email không hợp lệ!');
    }

    error_log("Attempting to reply to contact ID: $contact_id, Email: $email");

    // Cấu hình PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_CONFIG['host'];
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_CONFIG['username'];
        $mail->Password = SMTP_CONFIG['password'];
        $mail->SMTPSecure = SMTP_CONFIG['secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = SMTP_CONFIG['port'];
        $mail->setFrom(SMTP_CONFIG['username'], SMTP_CONFIG['from_name']);
        $mail->addAddress($email);
        $mail->Subject = $subject;
        $mail->Body = nl2br(htmlspecialchars_decode($message));
        $mail->isHTML(true);

        $mail->send();
        error_log("Email sent to: $email, Subject: $subject");
    } catch (Exception $e) {
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        throw new Exception('Không thể gửi email: ' . $mail->ErrorInfo);
    }

    // Kiểm tra bảng contact_replies
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'contact_replies'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        throw new Exception('Bảng contact_replies không tồn tại.');
    }

    // Lưu lịch sử trả lời
    $stmt = $pdo->prepare("INSERT INTO contact_replies (contact_id, subject, message, created_at) VALUES (?, ?, ?, NOW())");
    $result = $stmt->execute([$contact_id, $subject, $message]);

    if (!$result) {
        error_log("SQL Error (INSERT): " . print_r($stmt->errorInfo(), true));
        throw new Exception('Không thể lưu lịch sử trả lời. Vui lòng thử lại!');
    }

    // Xóa cache
    if (function_exists('glob')) {
        array_map('unlink', glob(ROOT_PATH . '/cache/contacts_*.cache'));
    }

    $response['success'] = true;
    $response['message'] = 'Đã gửi trả lời thành công!';
} catch (Exception $e) {
    error_log("Reply contact error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
