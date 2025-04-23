<?php
define('APP_START', true);
require_once '../includes/db_connect.php';
require_once '../includes/session_start.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in'])) {
    header('Location: ../index.php?page=login&redirect=checkout');
    exit;
}

// Kiểm tra dữ liệu đầu vào
if (!isset($_POST['selected_items']) || !isset($_POST['total_amount'])) {
    header('Location: ../index.php?page=cart&error=' . urlencode('Dữ liệu thanh toán không hợp lệ'));
    exit;
}

// Gọi zalopay_payment.php
require_once 'zalopay_payment.php';
