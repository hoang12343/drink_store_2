<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
define('APP_START', true);
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/includes/db_connect.php';

// Kiểm tra quyền quản trị
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Lỗi không xác định'];

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$value = filter_input(INPUT_POST, 'value', FILTER_VALIDATE_INT);

if ($id && in_array($action, ['read', 'important']) && in_array($value, [0, 1])) {
    try {
        // Kiểm tra bảng contacts
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'contacts'");
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $response['message'] = 'Bảng contacts không tồn tại.';
            echo json_encode($response);
            exit;
        }

        $column = $action === 'read' ? 'is_read' : 'is_important';
        $stmt = $pdo->prepare("UPDATE contacts SET $column = ? WHERE id = ?");
        $stmt->execute([$value, $id]);

        // Xóa cache nếu có
        if (function_exists('glob')) {
            array_map('unlink', glob(ROOT_PATH . '/cache/contacts_*.cache'));
        }

        $response['success'] = true;
        $response['message'] = 'Cập nhật thành công';
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $response['message'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Dữ liệu không hợp lệ';
}

echo json_encode($response);
exit;
