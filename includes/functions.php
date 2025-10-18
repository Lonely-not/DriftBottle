<?php
// 常用工具函数

// 安全过滤输入
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $data;
}

// 验证邮箱格式
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// 验证用户名格式
function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

// 获取设置值
function get_setting($key, $default = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM pk_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        error_log("获取设置失败: " . $e->getMessage());
        return $default;
    }
}

// 设置值
function set_setting($key, $value) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO pk_settings (setting_key, setting_value) VALUES (?, ?) 
                              ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        return $stmt->execute([$key, $value]);
    } catch (PDOException $e) {
        error_log("设置保存失败: " . $e->getMessage());
        return false;
    }
}

// 记录日志
function log_action($action, $details = null) {
    global $pdo;
    
    try {
        $user_id = get_current_user_id();
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $pdo->prepare("INSERT INTO pk_logs (user_id, action, details, ip_address, user_agent) 
                              VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$user_id, $action, $details, $ip_address, $user_agent]);
    } catch (PDOException $e) {
        error_log("日志记录失败: " . $e->getMessage());
        return false;
    }
}

// 发送JSON响应
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// 格式化时间
function format_time($dateString) {
    $date = new DateTime($dateString);
    $now = new DateTime();
    $diff = $now->diff($date);
    
    if ($diff->y > 0) {
        return $diff->y . '年前';
    } elseif ($diff->m > 0) {
        return $diff->m . '个月前';
    } elseif ($diff->d > 0) {
        return $diff->d . '天前';
    } elseif ($diff->h > 0) {
        return $diff->h . '小时前';
    } elseif ($diff->i > 0) {
        return $diff->i . '分钟前';
    } else {
        return '刚刚';
    }
}

// 获取管理员层级树
function get_admin_hierarchy($admin_id) {
    global $pdo;
    
    $hierarchy = [];
    $current_id = $admin_id;
    
    while ($current_id) {
        $stmt = $pdo->prepare("SELECT id, username, admin_level, superior_id FROM pk_user WHERE id = ?");
        $stmt->execute([$current_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            $hierarchy[] = [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'level' => $admin['admin_level']
            ];
            $current_id = $admin['superior_id'];
        } else {
            $current_id = null;
        }
    }
    
    return array_reverse($hierarchy);
}

// 获取下级管理员
function get_subordinate_admins($admin_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, username, email, admin_level, reg_time 
                          FROM pk_user 
                          WHERE superior_id = ? AND is_admin = 1
                          ORDER BY admin_level DESC, reg_time DESC");
    $stmt->execute([$admin_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 添加管理员
function add_admin($username, $password, $email, $admin_level, $superior_id) {
    global $pdo;
    
    try {
        $hashed_password = hash_password($password);
        
        $stmt = $pdo->prepare("INSERT INTO pk_user (username, password, email, is_admin, admin_level, superior_id) 
                              VALUES (?, ?, ?, 1, ?, ?)");
        return $stmt->execute([$username, $hashed_password, $email, $admin_level, $superior_id]);
    } catch (PDOException $e) {
        error_log("添加管理员失败: " . $e->getMessage());
        return false;
    }
}

// 检查用户名是否可用
function is_username_available($username) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pk_user WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetchColumn() == 0;
}
