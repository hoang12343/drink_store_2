<?php
session_start();
define('APP_START', true);

require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=register');
    exit;
}

// Lấy và lọc dữ liệu từ form
$full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING) ?? '';
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING) ?? '';
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING) ?? '';
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING) ?? '';
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING) ?? '';
$confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING) ?? '';

// Lưu dữ liệu tạm vào session
$_SESSION['register_input'] = [
    'full_name' => $full_name,
    'username' => $username,
    'email' => $email,
    'phone' => $phone,
    'address' => $address
];

// Xác thực dữ liệu
$errors = [];
if (!$full_name) $errors['full_name'] = 'Họ và tên không được để trống';
if (!$username || strlen($username) < 3) $errors['username'] = 'Tên đăng nhập phải từ 3 ký tự';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email không hợp lệ';
if (!preg_match('/^[0-9]{10,11}$/', $phone)) $errors['phone'] = 'Số điện thoại phải từ 10-11 số';
if (!$address) $errors['address'] = 'Địa chỉ không được để trống';
if (!$password || strlen($password) < 6) $errors['password'] = 'Mật khẩu phải từ 6 ký tự';
if ($password !== $confirm_password) $errors['confirm_password'] = 'Mật khẩu không khớp';

if ($errors) {
    $_SESSION['register_errors'] = $errors;
    header('Location: ../index.php?page=register&error=validation');
    exit;
}

try {
    // Kiểm tra trùng username và email
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $stmt = $pdo->prepare('SELECT username, email FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing['username'] === $username) {
            $_SESSION['register_errors'] = ['username' => 'Tên đăng nhập đã tồn tại'];
            header('Location: ../index.php?page=register&error=username_exists');
        } elseif ($existing['email'] === $email) {
            $_SESSION['register_errors'] = ['email' => 'Email đã được sử dụng'];
            header('Location: ../index.php?page=register&error=email_exists');
        }
        exit;
    }

    // Lưu vào database
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (full_name, username, email, phone, address, password, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$full_name, $username, $email, $phone, $address, $password_hash]);

    unset($_SESSION['register_input'], $_SESSION['register_errors']);
    header('Location: ../index.php?page=login&success=registered');
    exit;
} catch (PDOException $e) {
    error_log('Registration error: ' . $e->getMessage());
    header('Location: ../index.php?page=register&error=system');
    exit;
}
