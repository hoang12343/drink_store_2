<?php
session_start();
require_once '../includes/db_connect.php';
define('APP_START', true);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: ../index.php?page=register');
    exit;
}

$full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];
if (empty($full_name)) $errors['full_name'] = 'Vui lòng nhập họ và tên';
if (empty($username)) $errors['username'] = 'Vui lòng nhập tên đăng nhập';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email không hợp lệ';
if (!preg_match('/^[0-9]{10,11}$/', $phone)) $errors['phone'] = 'Số điện thoại không hợp lệ';
if (empty($address)) $errors['address'] = 'Vui lòng nhập địa chỉ';
if (strlen($password) < 6) $errors['password'] = 'Mật khẩu phải từ 6 ký tự trở lên';
if ($password !== $confirm_password) $errors['confirm_password'] = 'Mật khẩu không khớp';

if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_input'] = [
        'full_name' => $full_name,
        'username' => $username,
        'email' => $email,
        'phone' => $phone,
        'address' => $address
    ];
    header('Location: ../index.php?page=register&error=validation');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);

    if ($stmt->fetchColumn() > 0) {
        header('Location: ../index.php?page=register&error=exists');
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, phone, address, password, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$full_name, $username, $email, $phone, $address, $password_hash]);

    header('Location: ../index.php?page=register&success=1');
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    header('Location: ../index.php?page=register&error=system');
}
exit;