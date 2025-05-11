<?php
define('APP_START', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    // Cấu hình SMTP từ SMTP_CONFIG
    $mail->isSMTP();
    $mail->Host = SMTP_CONFIG['host'];
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_CONFIG['username'];
    $mail->Password = SMTP_CONFIG['password']; // App Password mới
    $mail->SMTPSecure = SMTP_CONFIG['secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = SMTP_CONFIG['port'];

    // Bật debug SMTP
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function ($str, $level) {
        echo "Debug [$level]: $str<br>";
    };

    $mail->setFrom(SMTP_CONFIG['username'], SMTP_CONFIG['from_name']);
    $mail->addAddress('your-email@gmail.com'); // Thay bằng email của bạn để test
    $mail->Subject = 'Test Email from Drink Store';
    $mail->Body = 'This is a test email to verify SMTP configuration.';
    $mail->isHTML(true);

    $mail->send();
    echo "Email sent successfully!";
} catch (Exception $e) {
    echo "Error: " . $mail->ErrorInfo;
}