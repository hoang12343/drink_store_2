<?php
session_start();
if (!defined('APP_START')) {
    define('APP_START', true);
}

// Ngăn đầu ra không mong muốn
ob_start();

require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Có lỗi xảy ra'];

try {
    // Kiểm tra phương thức POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Phương thức không hợp lệ');
    }

    // Lấy và lọc dữ liệu
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    error_log("Contact process data: name=$name, email=$email, phone=$phone, subject=$subject");

    // Kiểm tra các trường bắt buộc
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        throw new Exception('Vui lòng điền đầy đủ các trường bắt buộc');
    }

    // Kiểm tra định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Định dạng email không hợp lệ');
    }

    // Kiểm tra kết nối cơ sở dữ liệu
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Lỗi kết nối cơ sở dữ liệu');
    }

    // Lưu vào cơ sở dữ liệu
    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $result = $stmt->execute([$name, $email, $phone, $subject, $message]);

    if (!$result) {
        throw new Exception('Không thể lưu dữ liệu liên hệ');
    }

    // Lưu flash message
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'message' => 'Gửi liên hệ thành công'
    ];

    $response['success'] = true;
    $response['message'] = 'Gửi liên hệ thành công';
} catch (Exception $e) {
    error_log("Contact process error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => $e->getMessage()
    ];
}

// Xóa đầu ra không mong muốn và trả về JSON
ob_end_clean();
echo json_encode($response);
exit;