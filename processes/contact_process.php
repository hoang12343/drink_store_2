<?php
// Bắt đầu session nếu chưa được khởi tạo
session_start();

// Định nghĩa APP_START nếu chưa được định nghĩa
if (!defined('APP_START')) {
    define('APP_START', true);
}

// Ghi log để debug
error_log("Contact process started");
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

// Sử dụng đường dẫn tuyệt đối
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

// Thiết lập header cho phản hồi
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Có lỗi xảy ra'];

// Kiểm tra phương thức request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    $response['message'] = 'Phương thức không hợp lệ';
    echo json_encode($response);
    exit;
}

// Lấy dữ liệu từ form
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

// Ghi log dữ liệu đã lọc
error_log("Filtered data: name=$name, email=$email, phone=$phone, subject=$subject, csrf_token=$csrf_token");

// Kiểm tra CSRF token
if (!isset($_SESSION['csrf_token'])) {
    // Nếu không có CSRF token trong session, tạo mới
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    error_log("CSRF token không tồn tại trong session, đã tạo mới: " . $_SESSION['csrf_token']);

    $response['message'] = 'Phiên làm việc đã hết hạn, vui lòng tải lại trang và thử lại';
    echo json_encode($response);
    exit;
} else if (empty($csrf_token)) {
    // Nếu không có CSRF token trong request
    error_log("CSRF token không tồn tại trong request");
    $response['message'] = 'Thiếu token bảo mật, vui lòng tải lại trang và thử lại';
    echo json_encode($response);
    exit;
} else if ($csrf_token !== $_SESSION['csrf_token']) {
    // Nếu CSRF token không khớp
    error_log("CSRF token không khớp: " . $csrf_token . " vs " . $_SESSION['csrf_token']);
    $response['message'] = 'Token bảo mật không hợp lệ, vui lòng tải lại trang và thử lại';
    echo json_encode($response);
    exit;
}

// Kiểm tra dữ liệu đầu vào
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    error_log("Missing required fields");
    $response['message'] = 'Vui lòng điền đầy đủ thông tin';
    echo json_encode($response);
    exit;
}

// Kiểm tra định dạng email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Invalid email format: $email");
    $response['message'] = 'Định dạng email không hợp lệ';
    echo json_encode($response);
    exit;
}

try {
    // Kiểm tra kết nối cơ sở dữ liệu
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        error_log("Invalid database connection");
        throw new Exception('Kết nối cơ sở dữ liệu không hợp lệ');
    }

    // Lưu vào cơ sở dữ liệu
    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $result = $stmt->execute([$name, $email, $phone, $subject, $message]);

    if (!$result) {
        error_log("Database error: " . print_r($stmt->errorInfo(), true));
        throw new Exception('Không thể lưu dữ liệu liên hệ');
    }

    // Tạo CSRF token mới sau khi xử lý form thành công
    $old_token = $_SESSION['csrf_token'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    error_log("CSRF token updated from $old_token to " . $_SESSION['csrf_token']);

    $response['success'] = true;
    $response['message'] = 'Gửi liên hệ thành công';
    error_log("Contact submitted successfully");
} catch (PDOException $e) {
    error_log("PDO Error in contact_process: " . $e->getMessage());
    $response['message'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
} catch (Exception $e) {
    error_log("General Error in contact_process: " . $e->getMessage());
    $response['message'] = 'Lỗi khi gửi liên hệ: ' . $e->getMessage();
}

echo json_encode($response);
exit;
