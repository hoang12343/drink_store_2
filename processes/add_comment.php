<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    $response['message'] = 'Vui lòng đăng nhập để gửi bình luận';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Phương thức không hợp lệ';
    echo json_encode($response);
    exit;
}

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
$comment_text = filter_input(INPUT_POST, 'comment_text', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$product_id || !$comment_text || trim($comment_text) === '') {
    $response['message'] = 'Bình luận không được để trống';
    echo json_encode($response);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO product_comments (product_id, user_id, comment_text, created_at)
        VALUES (:product_id, :user_id, :comment_text, NOW())
    ");
    $stmt->execute([
        'product_id' => $product_id,
        'user_id' => $_SESSION['user_id'],
        'comment_text' => $comment_text
    ]);

    $response['success'] = true;
    $response['message'] = 'Bình luận đã được gửi thành công';
    $response['comment'] = [
        'id' => $pdo->lastInsertId(), // Added for JavaScript tracking
        'full_name' => $_SESSION['full_name'],
        'comment_text' => $comment_text,
        'created_at' => date('Y-m-d H:i:s')
    ];
} catch (PDOException $e) {
    $response['message'] = 'Lỗi khi gửi bình luận: ' . $e->getMessage();
}

echo json_encode($response);
