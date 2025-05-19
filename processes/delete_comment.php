<?php
session_start();
require_once '../includes/db_connect.php';

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để xóa bình luận']);
    exit;
}

// Kiểm tra phương thức
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

// Lấy dữ liệu
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;

if (!$comment_id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    // Kiểm tra quyền sở hữu bình luận
    $check_sql = "SELECT user_id, product_id FROM product_comments WHERE id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$comment_id]);
    $comment = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        echo json_encode(['success' => false, 'message' => 'Bình luận không tồn tại']);
        exit;
    }

    if ((int)$comment['user_id'] !== $user_id) {
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa bình luận này']);
        exit;
    }

    $product_id = (int)$comment['product_id'];

    // Xóa bình luận
    $delete_sql = "DELETE FROM product_comments WHERE id = ?";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_result = $delete_stmt->execute([$comment_id]);

    if (!$delete_result) {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa bình luận này']);
        exit;
    }

    // Cập nhật đánh giá trung bình
    $update_sql = "UPDATE products SET 
                    rating = IFNULL((SELECT AVG(rating) FROM product_comments WHERE product_id = ?), 0),
                    reviews = (SELECT COUNT(*) FROM product_comments WHERE product_id = ?)
                  WHERE id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$product_id, $product_id, $product_id]);

    echo json_encode(['success' => true, 'message' => 'Bình luận đã được xóa thành công']);
} catch (PDOException $e) {
    error_log("Error deleting comment: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa bình luận: ' . $e->getMessage()]);
}
