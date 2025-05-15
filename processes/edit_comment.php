<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    $response['message'] = 'Vui lòng đăng nhập để sửa bình luận';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Phương thức không hợp lệ';
    echo json_encode($response);
    exit;
}

$comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT);
$comment_text = filter_input(INPUT_POST, 'comment_text', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$comment_id || !$comment_text || trim($comment_text) === '') {
    $response['message'] = 'Bình luận không được để trống';
    echo json_encode($response);
    exit;
}

try {
    // Kiểm tra quyền sở hữu bình luận
    $stmt = $pdo->prepare("SELECT user_id FROM product_comments WHERE id = :comment_id");
    $stmt->execute(['comment_id' => $comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        $response['message'] = 'Bình luận không tồn tại';
        echo json_encode($response);
        exit;
    }

    if ($comment['user_id'] != $_SESSION['user_id']) {
        $response['message'] = 'Bạn không có quyền sửa bình luận này';
        echo json_encode($response);
        exit;
    }

    // Cập nhật bình luận
    $stmt = $pdo->prepare("
        UPDATE product_comments 
        SET comment_text = :comment_text, updated_at = NOW()
        WHERE id = :comment_id
    ");
    $stmt->execute([
        'comment_id' => $comment_id,
        'comment_text' => $comment_text
    ]);

    $response['success'] = true;
    $response['message'] = 'Bình luận đã được cập nhật thành công';
} catch (PDOException $e) {
    $response['message'] = 'Lỗi khi sửa bình luận: ' . $e->getMessage();
}

echo json_encode($response);
