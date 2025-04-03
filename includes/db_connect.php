<?php
// Thêm điều kiện này để bỏ qua việc kiểm tra APP_START khi cần
if (!defined('APP_START')) {
    define('APP_START', true);
}

// Database configuration
$db_config = [
    'host' => 'localhost',
    'port' => '3307 ',  // Thêm port 3307
    'name' => 'drink_store',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4'
];

// Bọc toàn bộ phần kết nối trong try-catch
try {
    // PDO connection options
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    // Create DSN với port
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset={$db_config['charset']}";

    // Create PDO instance
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], $options);
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    // Thay vì chỉ set $pdo = null, hiển thị lỗi và dừng chương trình
    die('Đã xảy ra lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.');
}