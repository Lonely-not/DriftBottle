<?php
// 认证相关函数

// 检查用户是否登录
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// 检查用户是否是管理员
function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// 获取管理员级别
function get_admin_level() {
    return $_SESSION['admin_level'] ?? null;
}

// 检查是否是超级管理员
function is_super_admin() {
    return get_admin_level() === 'super';
}

// 检查是否是高级管理员
function is_senior_admin() {
    return get_admin_level() === 'senior';
}

// 检查是否是初级管理员
function is_junior_admin() {
    return get_admin_level() === 'junior';
}

// 获取上级管理员ID
function get_superior_id() {
    return $_SESSION['superior_id'] ?? null;
}

// 需要登录的页面保护
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit;
    }
}

// 需要管理员权限的保护
function require_admin() {
    require_login();
    
    if (!is_admin()) {
        http_response_code(403);
        echo "权限不足，需要管理员权限";
        exit;
    }
}

// 需要超级管理员权限的保护
function require_super_admin() {
    require_admin();
    
    if (!is_super_admin()) {
        http_response_code(403);
        echo "权限不足，需要超级管理员权限";
        exit;
    }
}

// 密码哈希
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// 验证密码
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// 生成CSRF令牌
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 验证CSRF令牌
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// 用户登录
function login_user($user_id, $username, $is_admin = false, $admin_level = null, $superior_id = null) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['is_admin'] = $is_admin;
    $_SESSION['admin_level'] = $admin_level;
    $_SESSION['superior_id'] = $superior_id;
    $_SESSION['login_time'] = time();
    
    // 更新最后登录时间
    global $pdo;
    $stmt = $pdo->prepare("UPDATE pk_user SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user_id]);
}

// 用户退出
function logout_user() {
    // 清除所有会话变量
    $_SESSION = array();
    
    // 删除会话cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // 销毁会话
    session_destroy();
}

// 获取当前用户ID
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// 获取当前用户名
function get_current_username() {
    return $_SESSION['username'] ?? '游客';
}

// 检查用户权限
function has_permission($permission) {
    if (!is_logged_in()) {
        return false;
    }
    
    // 管理员拥有所有权限
    if (is_admin()) {
        return true;
    }
    
    // 根据权限名称检查
    switch ($permission) {
        case 'send_bottle':
            return true; // 所有登录用户都可以发送
        case 'pick_bottle':
            return true; // 所有登录用户都可以拾取
        case 'view_bottles':
            return true; // 所有登录用户都可以查看
        default:
            return false;
    }
}
