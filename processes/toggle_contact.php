<?php
if (!defined('APP_START')) exit('No direct access');
require_once '../includes/db_connect.php';

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$value = filter_input(INPUT_GET, 'value', FILTER_VALIDATE_INT);
$csrf_token = filter_input(INPUT_GET, 'csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$response = ['success' => false, 'message' => ''];

if (!$csrf_token || $csrf_token !== $_SESSION['csrf_token']) {
    $response['message'] = 'CSRF token không hợp lệ';
} elseif ($id && in_array($action, ['read', 'important']) && in_array($value, [0, 1])) {
    try {
        $column = $action === 'read' ? 'is_read' : 'is_important';
        $stmt = $pdo->prepare("UPDATE contacts SET $column = ? WHERE id = ?");
        $stmt->execute([$value, $id]);

        // Xóa cache
        array_map('unlink', glob(ROOT_PATH . '/cache/contacts_*.cache'));

        $response['success'] = true;
    } catch (PDOException $e) {
        $response['message'] = 'Lỗi: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Dữ liệu không hợp lệ';
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
