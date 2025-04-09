<?php
session_start();
define('APP_START', true);

require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=login');
    exit;
}

$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING) ?? '';
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING) ?? '';

if (!$username || !$password) {
    header('Location: ../index.php?page=login&error=empty');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, username, password, is_admin FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        header('Location: ../index.php?page=home');
    } else {
        header('Location: ../index.php?page=login&error=invalid');
    }
    exit;
} catch (PDOException $e) {
    error_log('Login error: ' . $e->getMessage());
    header('Location: ../index.php?page=login&error=system');
    exit;
}
