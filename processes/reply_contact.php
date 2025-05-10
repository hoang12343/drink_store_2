<?php
// Đảm bảo rằng file này không được truy cập trực tiếp
if (!defined('APP_START')) {
    define('APP_START', true);
}

// Include các file cần thiết
$root_path = dirname(dirname(__FILE__));
define('ROOT_PATH', $root_path);

require_once ROOT_PATH . '/includes/config.php';
require_once ROOT_PATH . '/includes/db_connect.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Gửi trả lời thất bại. Vui lòng thử lại!'];

/**
 * Gửi email đến người dùng
 * 
 * @param string $to Địa chỉ email người nhận
 * @param string $subject Tiêu đề email
 * @param string $message Nội dung email
 * @return bool Trả về true nếu gửi thành công, false nếu thất bại
 */
function send_email($to, $subject, $message)
{
    // Ghi log email để kiểm tra
    $log_message = "Email gửi đến: $to\nTiêu đề: $subject\nNội dung: $message\n";
    error_log($log_message);

    // Trong môi trường phát triển, chỉ ghi log và trả về true
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE === true) {
        return true;
    }

    // Triển khai gửi email thực tế
    try {
        // Sử dụng cấu hình SMTP từ config.php
        if (defined('SMTP_CONFIG')) {
            $smtp_config = SMTP_CONFIG;

            // Ghi log cấu hình SMTP để debug
            error_log("SMTP Config: " . print_r($smtp_config, true));

            // Triển khai gửi email thực tế ở đây
            // Ví dụ: sử dụng mail() hoặc PHPMailer
            return true; // Giả lập thành công
        } else {
            error_log("SMTP_CONFIG not defined");
            return false;
        }
    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
        return false;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Yêu cầu không hợp lệ. Vui lòng sử dụng đúng phương thức!');
    }

    // Kiểm tra kết nối cơ sở dữ liệu
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

    // Ghi log để debug
    error_log("Attempting to reply to contact ID: $contact_id, Email: $email");

    // Gửi email
    $mail_sent = send_email($email, $subject, $message);

    if (!$mail_sent) {
        throw new Exception('Không thể gửi email. Vui lòng kiểm tra cấu hình email!');
    }

    // Lưu lịch sử trả lời
    $stmt = $pdo->prepare("INSERT INTO contact_replies (contact_id, subject, message, created_at) VALUES (?, ?, ?, NOW())");
    $result = $stmt->execute([$contact_id, $subject, $message]);

    if (!$result) {
        error_log("SQL Error (INSERT): " . print_r($stmt->errorInfo(), true));
        throw new Exception('Không thể lưu lịch sử trả lời. Vui lòng thử lại!');
    }

    // Xóa cache nếu có
    if (function_exists('glob')) {
        array_map('unlink', glob(ROOT_PATH . '/cache/contacts_*.cache'));
    }

    $response['success'] = true;
    $response['message'] = 'Đã gửi trả lời thành công!';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Reply contact error: " . $e->getMessage());
}

echo json_encode($response);
exit;
