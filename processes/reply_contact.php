<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!defined('APP_START')) {
    define('APP_START', true);
}

// Định nghĩa ROOT_PATH
define('ROOT_PATH', __DIR__ . '/..');

require_once ROOT_PATH . '/includes/config.php';
require_once ROOT_PATH . '/includes/db_connect.php';
require_once ROOT_PATH . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ngăn đầu ra không mong muốn
ob_start();

header('Content-Type: application/json; charset=UTF-8');
$response = ['success' => false, 'message' => 'Gửi trả lời thất bại. Vui lòng thử lại!'];

try {
    error_log("Bắt đầu xử lý reply_contact.php");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Yêu cầu không hợp lệ. Vui lòng sử dụng đúng phương thức!');
    }

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Lỗi kết nối cơ sở dữ liệu. Vui lòng liên hệ quản trị viên!');
    }

    // Lấy và xử lý dữ liệu với UTF-8
    $contact_id = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_UNSAFE_RAW);
    $message = filter_input(INPUT_POST, 'message', FILTER_UNSAFE_RAW);

    // Sanitize thủ công để giữ UTF-8
    $subject = htmlspecialchars($subject, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    if (!$contact_id || !$email || !$subject || !$message) {
        throw new Exception('Dữ liệu không hợp lệ. Vui lòng kiểm tra lại thông tin!');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email không hợp lệ!');
    }

    error_log("Dữ liệu đầu vào: contact_id=$contact_id, email=$email, subject=$subject");

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
        $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $mail->Body = nl2br($message);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function ($str, $level) {
            error_log("SMTP Debug [$level]: $str");
        };

        // Chỉ gửi mail một lần
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
        error_log("Bảng contact_replies không tồn tại");
        throw new Exception('Bảng contact_replies không tồn tại.');
    }

    // Lưu lịch sử trả lời
    try {
        $stmt = $pdo->prepare("INSERT INTO contact_replies (contact_id, subject, message, created_at) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$contact_id, $subject, $message]);
        if (!$result) {
            error_log("SQL Error (INSERT): " . print_r($stmt->errorInfo(), true));
            // Không throw exception, chỉ ghi log
        } else {
            error_log("Lưu lịch sử trả lời thành công cho contact_id=$contact_id");
        }
    } catch (PDOException $e) {
        error_log("PDO Error (INSERT): " . $e->getMessage());
        // Không throw exception, chỉ ghi log
    }

    // Email đã được gửi thành công, tiếp tục xử lý
    $response['success'] = true;
    $response['message'] = 'Đã gửi trả lời thành công!';
} catch (Exception $e) {
    error_log("Reply contact error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
    http_response_code(500); // Đặt mã trạng thái 500 nếu có lỗi
}

// Xóa bộ đệm đầu ra
ob_end_clean();

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
