<?php
if (!defined('APP_START')) {
    define('APP_START', true);
}

$db_config = [
    'host' => 'localhost',
    'port' => '3306',
    'name' => 'drink_store',
    'user' => 'root',
    'password' => '@Lehoang03022004',
    'charset' => 'utf8mb4'
];

try {
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die('Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.');
}
