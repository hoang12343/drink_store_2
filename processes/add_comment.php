<?php
session_start();
require_once '../includes/db_connect.php';

// Đảm bảo xử lý UTF-8
header('Content-Type: application/json; charset=utf-8');

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để gửi bình luận']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
$comment_text = filter_input(INPUT_POST, 'comment_text', FILTER_UNSAFE_RAW);
$rating = filter_input(INPUT_POST, 'rating', FILTER_SANITIZE_NUMBER_INT);

// Xử lý UTF-8 cho comment_text
$comment_text = htmlspecialchars($comment_text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

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
    // Bắt đầu transaction
    $pdo->beginTransaction();

    // Thêm bình luận mới
    $stmt = $pdo->prepare("
        INSERT INTO product_comments (user_id, product_id, comment_text, rating, created_at) 
        VALUES (:user_id, :product_id, :comment_text, :rating, NOW())
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':product_id' => $product_id,
        ':comment_text' => $comment_text,
        ':rating' => $rating
    ]);

    // Cập nhật đánh giá trung bình và số lượng đánh giá cho sản phẩm
    $stmt = $pdo->prepare("
        UPDATE products p
        SET 
            p.rating = (
                SELECT AVG(pc.rating) 
                FROM product_comments pc 
                WHERE pc.product_id = p.id
            ),
            p.reviews = (
                SELECT COUNT(*) 
                FROM product_comments pc 
                WHERE pc.product_id = p.id
            )
        WHERE p.id = :product_id
    ");
    $stmt->execute([':product_id' => $product_id]);

    // Commit transaction
    $pdo->commit();

    // Lấy thông tin bình luận vừa thêm
    $comment_id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("
        SELECT pc.*, u.username 
        FROM product_comments pc
        JOIN users u ON pc.user_id = u.id
        WHERE pc.id = ?
    ");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Bình luận đã được gửi thành công!',
        'comment' => $comment
    ]);
} catch (PDOException $e) {
    // Rollback transaction nếu có lỗi
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error adding comment: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi khi gửi bình luận: ' . $e->getMessage()]);
}
