<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// 如果已经登录，重定向到首页
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$username = '';
$email = '';

// 处理注册表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $gender = sanitize_input($_POST['gender'] ?? '保密');
    
    // 验证输入
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = '所有字段都是必填的';
    } elseif (!validate_username($username)) {
        $error = '用户名必须是3-20个字符，只能包含字母、数字和下划线';
    } elseif (!validate_email($email)) {
        $error = '无效的邮箱地址';
    } elseif ($password !== $confirm_password) {
        $error = '两次输入的密码不一致';
    } elseif (strlen($password) < 6) {
        $error = '密码长度至少为6个字符';
    } else {
        try {
            // 检查用户名是否已存在
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pk_user WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = '用户名已被使用';
            } else {
                // 创建新用户
                $hashed_password = hash_password($password);
                $stmt = $pdo->prepare("INSERT INTO pk_user (username, password, email, gender) VALUES (?, ?, ?, ?)");
                
                if ($stmt->execute([$username, $hashed_password, $email, $gender])) {
                    $success = '注册成功，请登录';
                    // 清空表单
                    $username = '';
                    $email = '';
                } else {
                    $error = '注册失败，请稍后再试';
                }
            }
        } catch (PDOException $e) {
            error_log("注册错误: " . $e->getMessage());
            $error = '系统错误，请稍后再试';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - 漂流瓶系统</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .register-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h1 {
            color: #3498db;
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1极 solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        select.form-control {
            height: 46px;
        }
        
        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 极;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
        }
        
        .register-links {
            text-align: center;
            margin-top: 20px;
        }
        
        .register-links a {
            color: #3498db;
            text-decoration: none;
        }
        
        .register-links a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffeaea;
            border-radius: 5px;
        }
        
        .success-message {
            color: #27ae60;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #eafaf1;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>🌊 漂流瓶系统</h1>
            <p>创建新账户</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required>
                <small>3-20个字符，只能包含字母、数字和下划线</small>
            </div>
            
            <div class极="form-group">
                <label for="email">邮箱</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <small>至少6个字符</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">确认密码</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="gender">性别</label>
                <select id="gender" name="gender" class="form-control">
                    <option value="保密">保密</option>
                    <option value="男">男</option>
                    <option value="女">女</option>
                </select>
            </div>
            
            <button type="submit" class="btn-register">注册</button>
        </form>
        
        <div class="register-links">
            <a href="login.php">已有账号？立即登录</a>
        </div>
    </div>
</body>
</html>
