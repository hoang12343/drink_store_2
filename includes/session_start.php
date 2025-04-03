<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('APP_START')) {
    define('APP_START', true);
}