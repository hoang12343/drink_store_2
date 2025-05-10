<?php
// Kiểm tra nếu session và APP_START chưa được khởi tạo
if (!defined('APP_START')) {
    session_start();
    define('APP_START', true);
}

// Sử dụng đường dẫn tuyệt đối thay vì tương đối
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Lỗi không xác định'];

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
    // Trong môi trường phát triển, giả lập việc gửi email thành công
    // Trong môi trường thực tế, bạn sẽ cần cấu hình SMTP hoặc sử dụng mail()

    // Ghi log email để kiểm tra
    $log_message = "Email gửi đến: $to\nTiêu đề: $subject\nNội dung: $message\n";
    error_log($log_message);

    // Trả về true để giả lập việc gửi email thành công
    // Trong môi trường thực tế, bạn sẽ cần kiểm tra kết quả thực tế
    return true;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Phương thức không hợp lệ');
    }

    $contact_id = filter_input(INPUT_POST, 'contact_id', FILTER_VALIDATE_INT);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Loại bỏ kiểm tra CSRF token
    // $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    // if (!$csrf_token || $csrf_token !== $_SESSION['csrf_token']) {
    //     throw new Exception('CSRF token không hợp lệ');
    // }

    if (!$contact_id || !$email || !$subject || !$message) {
        throw new Exception('Dữ liệu không hợp lệ');
    }

    // Gửi email
    $mail_sent = send_email($email, $subject, $message);

    if (!$mail_sent) {
        throw new Exception('Không thể gửi email');
    }

    // Cập nhật trạng thái đã trả lời
    $stmt = $pdo->prepare("UPDATE contacts SET is_replied = 1, replied_at = NOW() WHERE id = ?");
    $stmt->execute([$contact_id]);

    // Lưu lịch sử trả lời
    $stmt = $pdo->prepare("INSERT INTO contact_replies (contact_id, subject, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$contact_id, $subject, $message]);

    $response['success'] = true;
    $response['message'] = 'Đã gửi trả lời thành công';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Reply contact error: " . $e->getMessage());
}

echo json_encode($response);
exit;
