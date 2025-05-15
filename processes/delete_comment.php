<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    $response['message'] = 'Vui lòng đăng nhập để xóa bình luận';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Phương thức không hợp lệ';
    echo json_encode($response);
    exit;
}

$comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT);

if (!$comment_id) {
    $response['message'] = 'ID bình luận không hợp lệ';
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
        $response['message'] = 'Bạn không có quyền xóa bình luận này';
        echo json_encode($response);
        exit;
    }

    // Xóa bình luận
    $stmt = $pdo->prepare("DELETE FROM product_comments WHERE id = :comment_id");
    $stmt->execute(['comment_id' => $comment_id]);

    $response['success'] = true;
    $response['message'] = 'Bình luận đã được xóa thành công';
} catch (PDOException $e) {
    $response['message'] = 'Lỗi khi xóa bình luận: ' . $e->getMessage();
}

echo json_encode($response);
