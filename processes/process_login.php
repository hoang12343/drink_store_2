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

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'empty';
    $_SESSION['last_username'] = $username;
    header('Location: ../index.php?page=login');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, username, password, is_admin FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        unset($_SESSION['last_username']);

        // Redirect admin to dashboard, others to redirect or home
        if ($user['is_admin'] == 1) {
            header('Location: ../index.php?page=admin&subpage=dashboard');
        } else {
            $redirect = filter_input(INPUT_GET, 'redirect', FILTER_SANITIZE_URL) ?? 'home';
            header("Location: ../index.php?page=$redirect");
        }
        exit;
    } else {
        $_SESSION['login_error'] = 'invalid';
        $_SESSION['last_username'] = $username;
        header('Location: ../index.php?page=login');
        exit;
    }
} catch (PDOException $e) {
    error_log('Login error: ' . $e->getMessage());
    $_SESSION['login_error'] = 'system';
    $_SESSION['last_username'] = $username;
    header('Location: ../index.php?page=login');
    exit;
}
