<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để gửi bình luận']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
$comment_text = filter_input(INPUT_POST, 'comment_text', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$rating = filter_input(INPUT_POST, 'rating', FILTER_SANITIZE_NUMBER_INT);

// Validate input
if (!$product_id || !$comment_text) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Validate rating (1-5)
if ($rating && ($rating < 1 || $rating > 5)) {
    echo json_encode(['success' => false, 'message' => 'Đánh giá không hợp lệ']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO product_comments (user_id, product_id, comment_text, rating, created_at) 
        VALUES (:user_id, :product_id, :comment_text, :rating, NOW())
    ");

    $stmt->execute([
        'user_id' => $user_id,
        'product_id' => $product_id,
        'comment_text' => $comment_text,
        'rating' => $rating ?: null
    ]);

    $comment_id = $pdo->lastInsertId();

    // Lấy thông tin người dùng
    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Trả về thông tin bình luận mới
    echo json_encode([
        'success' => true,
        'message' => 'Bình luận đã được gửi thành công',
        'comment' => [
            'id' => $comment_id,
            'user_id' => $user_id,
            'full_name' => $user['full_name'],
            'comment_text' => $comment_text,
            'rating' => $rating,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu bình luận: ' . $e->getMessage()]);
}
