<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) ?: 1;
$product_id = filter_input(INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT);
$comments_per_page = 10;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
    exit;
}

try {
    // Đếm tổng số bình luận
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_comments WHERE product_id = :product_id");
    $stmt->execute(['product_id' => $product_id]);
    $total_comments = $stmt->fetchColumn();

    // Tính tổng số trang
    $total_pages = ceil($total_comments / $comments_per_page);

    // Lấy bình luận cho trang hiện tại
    $offset = ($page - 1) * $comments_per_page;
    $stmt = $pdo->prepare("
        SELECT pc.id, pc.comment_text, pc.created_at, pc.rating, u.full_name, u.id as user_id
        FROM product_comments pc
        JOIN users u ON pc.user_id = u.id
        WHERE pc.product_id = :product_id
        ORDER BY pc.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $comments_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tính điểm đánh giá trung bình
    $stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as rating_count 
        FROM product_comments 
        WHERE product_id = :product_id AND rating > 0
    ");
    $stmt->execute(['product_id' => $product_id]);
    $rating_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
    $rating_count = $rating_data['rating_count'] ? intval($rating_data['rating_count']) : 0;

    echo json_encode([
        'success' => true,
        'comments' => $comments,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'avg_rating' => $avg_rating,
        'rating_count' => $rating_count
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi lấy bình luận: ' . $e->getMessage()]);
}
