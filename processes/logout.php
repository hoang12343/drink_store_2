<?php
// processes/logout.php
session_start();

// Xóa tất cả biến session trước khi hủy
session_unset();
// Hủy session hoàn toàn
session_destroy();

// Đảm bảo không có đầu ra trước header
if (ob_get_length()) {
    ob_end_clean(); // Xóa buffer đầu ra nếu có
}

header('Location: ../index.php?page=home');
exit;