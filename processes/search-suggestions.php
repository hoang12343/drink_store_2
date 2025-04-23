<?php
define('APP_START', true);
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$results = [];

if (strlen($search) >= 2) {
    try {
        // Cấu trúc truy vấn SQL:
        // - Chọn id, name, image từ bảng products
        // - Tìm kiếm sản phẩm có name hoặc code chứa từ khóa
        // - Sắp xếp theo tên tăng dần
        // - Giới hạn 10 kết quả
        $query = "
            SELECT 
                id,
                name,
                image
            FROM 
                products
            WHERE 
                name LIKE :search 
                OR code LIKE :search
            ORDER BY 
                name ASC
            LIMIT 
                10
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching suggestions: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Server error']);
        exit;
    }
}

echo json_encode($results);