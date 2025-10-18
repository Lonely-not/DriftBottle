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
$username = '';

// 处理登录表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = '用户名和密码不能为空';
    } else {
        try {
            // 查询用户
            $stmt = $pdo->prepare("SELECT id, username, password, is_admin, admin_level, superior_id FROM pk_user WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && verify_password($password, $user['password'])) {
                // 登录成功
                login_user($user['id'], $user['username'], $user['is_admin'], $user['admin_level'], $user['superior_id']);
                
                // 重定向到之前访问的页面或首页
                $redirect_url = $_SESSION['redirect_url'] ?? 'index.php';
                unset($_SESSION['redirect_url']);
                header("Location: $redirect_url");
                exit;
            } else {
                $error = '用户名或密码不正确';
            }
        } catch (PDOException $e) {
            error_log("登录错误: " . $e->getMessage());
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
    <title>登录 - 漂流瓶系统</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
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
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .login-links {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-links a {
            color: #3498db;
            text-decoration: none;
        }
        
        .login-links a:hover {
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🌊 漂流瓶系统</h1>
            <p>登录您的账户</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn-login">登录</button>
        </form>
        
        <div class="login-links">
            <a href="register.php">还没有账号？立即注册</a>
        </div>
    </div>
</body>
</html>
