<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// 检查管理员权限
require_admin();

// 验证CSRF令牌
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die("无效的CSRF令牌");
}

// 获取表单数据
$username = sanitize_input($_POST['username'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$admin_level = sanitize_input($_POST['admin_level'] ?? 'junior');

// 验证输入
if (empty($username) || empty($email) || empty($password)) {
    header('Location: yunlvindex.php?error=' . urlencode('所有字段都是必填的'));
    exit;
}

// 验证用户名格式
if (!validate_username($username)) {
    header('Location: yunlvindex.php?error=' . urlencode('用户名必须是3-20个字符，只能包含字母、数字和下划线'));
    exit;
}

// 验证邮箱格式
if (!validate_email($email)) {
    header('Location: yunlvindex.php?error=' . urlencode('无效的邮箱地址'));
    exit;
}

// 检查用户名是否可用
if (!is_username_available($username)) {
    header('Location: yunlvindex.php?error=' . urlencode('用户名已被使用'));
    exit;
}

// 验证管理员级别
$allowed_levels = ['junior', 'senior'];
if (is_super_admin()) {
    $allowed_levels[] = 'super';
}

if (!in_array($admin_level, $allowed_levels)) {
    header('Location: yunlvindex.php?error=' . urlencode('无效的管理员级别'));
    exit;
}

// 添加管理员
$superior_id = $_SESSION['user_id'];
if (add_admin($username, $password, $email, $admin_level, $superior_id)) {
    log_action('add_admin', "添加了管理员: $username ($admin_level)");
    header('Location: yunlvindex.php?success=' . urlencode('管理员添加成功'));
} else {
    header('Location: yunlvindex.php?error=' . urlencode('添加管理员失败'));
}
exit;
