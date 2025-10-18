<?php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'drift_bottle_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// 安全设置
define('SITE_KEY', 'driftbottle_' . bin2hex(random_bytes(16)));

// 会话设置
session_start();

// 错误报告设置
error_reporting(E_ALL);
ini_set('display_errors', 0); // 生产环境设置为0

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 自动加载类
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// 安全头
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
