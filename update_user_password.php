<?php
define('APP_START', true);

require_once 'includes/db_connect.php';

try {
    $username = 'leehoang';
    $new_password = '123456'; // Change this if you want a different password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE username = ?');
    $stmt->execute([$hashed_password, $username]);

    echo "Password for $username updated successfully! Hashed password: $hashed_password";
} catch (PDOException $e) {
    error_log('Update password error: ' . $e->getMessage());
    echo "Error updating password: " . $e->getMessage();
}
