<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// 检查管理员权限
require_admin();

$error = '';
$success = '';
$users = [];

// 获取用户列表
try {
    $stmt = $pdo->prepare("SELECT id, username, email, gender, reg_time, last_login, status FROM pk_user ORDER BY reg_time DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("获取用户列表错误: " . $e->getMessage());
    $error = '获取用户列表失败';
}

// 处理用户状态变更
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = $_POST['user_id'] ?? 0;
    $action = $_POST['action'];
    
    try {
        if ($action === 'ban') {
            $stmt = $pdo->prepare("UPDATE pk_user SET status = 'banned' WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = '用户已被封禁';
            log_action('ban_user', "封禁了用户 ID: $user_id");
        } elseif ($action === 'unban') {
            $stmt = $pdo->prepare("UPDATE pk_user SET status = 'active' WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = '用户已解封';
            log_action('unban_user', "解封了用户 ID: $user_id");
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM pk_user WHERE id = ? AND is_admin = 0");
            $stmt->execute([$user_id]);
            if ($stmt->rowCount() > 0) {
                $success = '用户已删除';
                log_action('delete_user', "删除了用户 ID: $user_id");
            } else {
                $error = '无法删除管理员用户';
            }
        }
    } catch (PDOException $e) {
        error_log("用户操作错误: " . $e->getMessage());
        $error = '操作失败';
    }
    
    // 刷新页面
    header("Location: manage_users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - 漂流瓶系统</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #2c3e50, #1a2530);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .users-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .users-table th,
        .users-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .users-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .status-active {
            color: #2ecc71;
            font-weight: bold;
        }
        
        .status-banned {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .btn-action {
            padding: 5px 10px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-ban {
            background: #e74c3c;
            color: white;
        }
        
        .btn-unban {
            background: #2ecc71;
            color: white;
        }
        
        .btn-delete {
            background: #95a5a6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>用户管理</h1>
            <p>管理所有注册用户</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="users-table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户名</th>
                        <th>邮箱</th>
                        <th>性别</th>
                        <th>注册时间</th>
                        <th>最后登录</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['gender']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($user['reg_time'])) ?></td>
                            <td><?= $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : '从未登录' ?></td>
                            <td class="status-<?= $user['status'] ?>">
                                <?= $user['status'] === 'active' ? '正常' : '封禁' ?>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <?php if ($user['status'] === 'active'): ?>
                                        <button type="submit" name="action" value="ban" class="btn-action btn-ban">封禁</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="unban" class="btn-action btn-unban">解封</button>
                                    <?php endif; ?>
                                    <button type="submit" name="action" value="delete" class="btn-action btn-delete" 
                                            onclick="return confirm('确定要删除这个用户吗？')">删除</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
