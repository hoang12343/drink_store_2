<?php
if (!defined('APP_START')) exit('No direct access');

// Kết nối cơ sở dữ liệu
require_once 'includes/db_connect.php';
require_once 'includes/session_start.php';

// Hàm làm sạch dữ liệu đầu vào
function sanitize_input($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Xử lý form khi được gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    // Xác thực dữ liệu
    $errors = [];
    if (empty($name)) {
        $errors[] = 'Họ và tên là bắt buộc.';
    }
    if (empty($email)) {
        $errors[] = 'Email là bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ.';
    }
    if (empty($subject)) {
        $errors[] = 'Tiêu đề là bắt buộc.';
    }
    if (empty($message)) {
        $errors[] = 'Nội dung tin nhắn là bắt buộc.';
    }

    // Nếu không có lỗi, lưu vào cơ sở dữ liệu
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO contacts (name, email, phone, subject, message, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $email, $phone, $subject, $message]);

            // Chuyển hướng với thông báo thành công
            header('Location: ../index.php?page=contact&success=sent');
            exit;
        } catch (PDOException $e) {
            // Ghi log lỗi và chuyển hướng với thông báo lỗi
            error_log('Lỗi khi lưu liên hệ: ' . $e->getMessage());
            header('Location: ../index.php?page=contact&error=server');
            exit;
        }
    } else {
        // Chuyển hướng với thông báo lỗi
        $error_message = urlencode(implode(' ', $errors));
        header('Location: ../index.php?page=contact&error=' . $error_message);
        exit;
    }
} else {
    // Nếu không phải POST, chuyển hướng về trang liên hệ
    header('Location: ../index.php?page=contact');
    exit;
}
