<?php
if (!defined('APP_START')) {
    exit('No direct access');
}

// Thêm định nghĩa cho DEVELOPMENT_MODE
define('DEVELOPMENT_MODE', true); // Đặt thành false khi triển khai sản phẩm

define('ZALOPAY_CONFIG', [
    'app_id' => '553',
    'key1' => '9phuAOYhan4urywHTh0ndEXiV3pKHr5Q',
    'key2' => 'Iyz2habzyr7AG8SgvoBCbKwKi3UzlLi3',
    'endpoint' => 'https://sb-openapi.zalopay.vn/v2/create'
]);

// Cấu hình email cho thông báo
define('ADMIN_EMAIL', 'admin@yourstore.com');
define('SMTP_CONFIG', [
    'host' => 'smtp.gmail.com',
    'username' => 'hoanggg322004@gmail.com', // Thay đổi thành email thực của bạn
    'password' => 'thaj pooj yqse rpjh', // Thay đổi thành mật khẩu ứng dụng (không phải mật khẩu Gmail)
    'port' => 587,
    'secure' => 'tls'
]);
