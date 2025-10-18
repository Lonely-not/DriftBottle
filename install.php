<?php
// 安装引导脚本
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 检查是否已安装
if (file_exists('config.php')) {
    header('Location: index.php');
    exit;
}

// 处理表单提交
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取并验证输入
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    
    // 验证输入
    if (empty($db_host)) $errors[] = "数据库主机不能为空";
    if (empty($db_name)) $errors[] = "数据库名称不能为空";
    if (empty($db_user)) $errors[] = "数据库用户名不能为空";
    
    if (empty($errors)) {
        try {
            // 测试数据库连接
            $dsn = "mysql:host={$db_host};charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            // 创建数据库（如果不存在）
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}`");
            $pdo->exec("USE `{$db_name}`");
            
            // 导入SQL结构
            $sqlFile = __DIR__ . '/database/schema.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("SQL文件不存在: {$sqlFile}");
            }
            
            $sql = file_get_contents($sqlFile);
            $pdo->exec($sql);
            
            // 创建配置文件
            $configContent = <<<PHP
<?php
// 数据库配置 - 自动生成
define('DB_HOST', '{$db_host}');
define('DB_NAME', '{$db_name}');
define('DB_USER', '{$db_user}');
define('DB_PASS', '{$db_pass}');

// 安全设置
define('SITE_KEY', 'driftbottle_' . bin2hex(random_bytes(16)));
session_start();

// 数据库连接
try {
    \$pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException \$e) {
    error_log("数据库连接失败: " . \$e->getMessage());
    die("系统维护中，请稍后再试");
}
PHP;
            
            if (file_put_contents('config.php', $configContent) === false) {
                throw new Exception("无法创建配置文件");
            }
            
            $success = true;
            
        } catch (PDOException $e) {
            $errors[] = "数据库连接失败: " . $e->getMessage();
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>漂流瓶系统 - 安装向导</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .install-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .install-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .install-header h1 {
            color: #3498db;
            margin-bottom: 10px;
        }
        
        .install-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .step {
            text-align: center;
            flex: 1;
            position: relative;
        }
        
        .step:not(:last-child):after {
            content: '';
            position: absolute;
            top: 20px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #3498db;
            z-index: 1;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            line-height: 40px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            display: inline-block;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }
        
        .step.active .step-number {
            background: #e74c3c;
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
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .btn-install {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .success-box {
            text-align: center;
            padding: 30px;
        }
        
        .success-icon {
            font-size: 60px;
            color: #2ecc71;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>🌊 漂流瓶系统安装向导</h1>
            <p>只需几步，即可完成系统安装</p>
        </div>
        
        <div class="install-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div>数据库配置</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div>安装完成</div>
            </div>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>安装过程中出现问题：</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-box">
                <div class="success-icon">✓</div>
                <h2>安装成功！</h2>
                <p>系统已成功安装并配置完成</p>
                <p>管理员账号: <strong>YunLv</strong> 密码: <strong>YunLv.1118</strong></p>
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary">进入首页</a>
                    <a href="login.php" class="btn btn-outline-primary ml-2">登录系统</a>
                </div>
                <p class="mt-3 text-muted"><small>为了安全，请删除或重命名 install.php 文件</small></p>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="db_host">数据库主机</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                    <small class="text-muted">通常是 localhost 或 127.0.0.1</small>
                </div>
                
                <div class="form-group">
                    <label for="db_name">数据库名称</label>
                    <input type="text" id="db_name" name="db_name" required>
                    <small class="text-muted">系统将自动创建此数据库</small>
                </div>
                
                <div class="form-group">
                    <label for="db_user">数据库用户名</label>
                    <input type="text" id="db_user" name="db_user" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">数据库密码</label>
                    <input type="password" id="db_pass" name="db_pass">
                </div>
                
                <button type="submit" class="btn-install">开始安装</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
