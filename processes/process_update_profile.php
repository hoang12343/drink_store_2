<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php?page=login&redirect=update_profile');
    exit;
}

$errors = [];
$input = [];
$user_id = $_SESSION['user_id'];

// Lấy dữ liệu từ form
$input['full_name'] = trim(filter_input(INPUT_POST, 'full_name', FILTER_UNSAFE_RAW));
$input['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$input['phone'] = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$input['address'] = trim(filter_input(INPUT_POST, 'address', FILTER_UNSAFE_RAW));
$password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
$confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_UNSAFE_RAW);

// Kiểm tra dữ liệu
if (empty($input['full_name']) || !preg_match('/^[\p{L}\s]+$/u', $input['full_name'])) {
    $errors['full_name'] = 'Vui lòng nhập họ và tên hợp lệ (chỉ chữ và khoảng trắng).';
}
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Vui lòng nhập email hợp lệ.';
} else {
    // Kiểm tra email đã tồn tại (không tính email của người dùng hiện tại)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$input['email'], $user_id]);
    if ($stmt->fetch()) {
        $errors['email'] = 'Email đã được sử dụng bởi người khác.';
    }
}
if (!preg_match('/^[0-9]{10,11}$/', $input['phone'])) {
    $errors['phone'] = 'Số điện thoại phải có 10-11 số.';
}
if (empty($input['address']) || !preg_match('/^[\p{L}\p{N}\s,.-]+$/u', $input['address'])) {
    $errors['address'] = 'Vui lòng nhập địa chỉ hợp lệ (chữ, số, khoảng trắng, dấu phẩy, dấu chấm, dấu gạch ngang).';
}
if (!empty($password)) {
    if (strlen($password) < 6) {
        $errors['password'] = 'Mật khẩu phải từ 6 ký tự trở lên.';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Mật khẩu không khớp.';
    }
} elseif (!empty($confirm_password)) {
    $errors['confirm_password'] = 'Vui lòng nhập mật khẩu để xác nhận.';
}

// Nếu có lỗi, lưu vào session và chuyển hướng
if (!empty($errors)) {
    $_SESSION['update_errors'] = $errors;
    $_SESSION['update_input'] = $input;
    header('Location: ../index.php?page=update_profile&error=validation');
    exit;
}

// Cập nhật thông tin vào cơ sở dữ liệu
try {
    $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?";
    $params = [$input['full_name'], $input['email'], $input['phone'], $input['address']];
    if (!empty($password)) {
        $sql .= ", password = ?";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }
    $sql .= " WHERE id = ?";
    $params[] = $user_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Cập nhật session username
    $_SESSION['username'] = $input['full_name'];
    header('Location: ../index.php?page=update_profile&success=updated');
    exit;
} catch (PDOException $e) {
    $_SESSION['update_input'] = $input;
    error_log('Update profile error: ' . $e->getMessage());
    header('Location: ../index.php?page=update_profile&error=system');
    exit;
}
