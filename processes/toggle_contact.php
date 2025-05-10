<?php
session_start();
define('APP_START', true);
require_once '../includes/db_connect.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Lỗi không xác định'];

// Chuyển từ GET sang POST
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$value = filter_input(INPUT_POST, 'value', FILTER_VALIDATE_INT);

// Loại bỏ kiểm tra CSRF token
// $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
// if (!$csrf_token || $csrf_token !== $_SESSION['csrf_token']) {
//     $response['message'] = 'CSRF token không hợp lệ';
// } else

if ($id && in_array($action, ['read', 'important']) && in_array($value, [0, 1])) {
    try {
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
        $response['message'] = 'Lỗi: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Dữ liệu không hợp lệ';
}

echo json_encode($response);
exit;
