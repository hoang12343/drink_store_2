<?php
session_start();
define('APP_START', true);

require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=register');
    exit;
}

$full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING) ?? '';
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING) ?? '';
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING) ?? '';
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING) ?? '';
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING) ?? '';
$confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING) ?? '';

if (empty($full_name) || empty($username) || empty($email) || empty($phone) || empty($address) || empty($password) || empty($confirm_password)) {
    $_SESSION['register_error'] = 'empty';
    header('Location: ../index.php?page=register');
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['register_error'] = 'password_mismatch';
    header('Location: ../index.php?page=register');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $_SESSION['register_error'] = 'exists';
        header('Location: ../index.php?page=register');
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $is_admin = 0; // Default to non-admin; admin can set manually in DB

    $stmt = $pdo->prepare('
        INSERT INTO users (full_name, username, email, phone, address, password, is_admin, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ');
    $stmt->execute([$full_name, $username, $email, $phone, $address, $hashed_password, $is_admin]);

    header('Location: ../index.php?page=login&success=registered');
    exit;
} catch (PDOException $e) {
    error_log('Register error: ' . $e->getMessage());
    $_SESSION['register_error'] = 'system';
    header('Location: ../index.php?page=register');
    exit;
}
