<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// 检查管理员权限
require_admin();

$error = '';
$success = '';
$settings = [];

// 获取当前设置
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value, description FROM pk_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("获取设置错误: " . $e->getMessage());
    $error = '获取设置失败';
}

// 处理设置更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $setting_key = substr($key, 8); // 移除 'setting_' 前缀
            $setting_value = sanitize_input($value);
            
            try {
                $stmt = $pdo->prepare("UPDATE pk_settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$setting_value, $setting_key]);
            } catch (PDOException $e) {
                error_log("更新设置错误: " . $e->getMessage());
                $error = '更新设置失败';
                break;
            }
        }
    }
    
    if (empty($error)) {
        $success = '设置已更新';
        log_action('update_settings', "更新了系统设置");
    }
    
    // 刷新页面
    header("Location: system_settings.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统设置 - 漂流瓶系统</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .settings-form {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .setting-item {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
        }
        
        .setting-item:last-child {
            border-bottom: none;
        }
        
        .setting-label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }
        
        .setting-description {
            font-size: 14px;
            color: #777;
            margin-bottom: 10px;
        }
        
        .setting-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>系统设置</h1>
            <p>配置系统参数</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="POST" class="settings-form">
            <?php foreach ($settings as $setting): ?>
                <div class="setting-item">
                    <label class="setting-label"><?= htmlspecialchars($setting['setting_key']) ?></label>
                    <div class="setting-description"><?= htmlspecialchars($setting['description']) ?></div>
                    <input type="text" 
                           name="setting_<?= htmlspecialchars($setting['setting_key']) ?>" 
                           value="<?= htmlspecialchars($setting['setting_value']) ?>" 
                           class="setting-input">
                </div>
            <?php endforeach; ?>
            
            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn-save">保存设置</button>
            </div>
        </form>
    </div>
</body>
</html>
