<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// 检查管理员权限
require_admin();

$error = '';
$success = '';
$bottles = [];
$filter = $_GET['filter'] ?? 'all';

// 构建查询条件
$where_conditions = [];
$params = [];

if ($filter === 'drifting') {
    $where_conditions[] = "b.status = 'drifting'";
} elseif ($filter === 'picked') {
    $where_conditions[] = "b.status = 'picked'";
} elseif ($filter === 'replied') {
    $where_conditions[] = "b.status = 'replied'";
}

$where_sql = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// 获取漂流瓶列表
try {
    $sql = "SELECT b.*, u1.username as sender_name, u2.username as receiver_name 
            FROM pk_bottle b 
            JOIN pk_user u1 ON b.sender_id = u1.id 
            LEFT JOIN pk_user u2 ON b.receiver_id = u2.id 
            $where_sql 
            ORDER BY b.send_time DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bottles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("获取漂流瓶列表错误: " . $e->getMessage());
    $error = '获取漂流瓶列表失败';
}

// 处理漂流瓶操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $bottle_id = $_POST['bottle_id'] ?? 0;
    $action = $_POST['action'];
    
    try {
        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM pk_bottle WHERE id = ?");
            $stmt->execute([$bottle_id]);
            $success = '漂流瓶已删除';
            log_action('delete_bottle', "删除了漂流瓶 ID: $bottle_id");
        }
    } catch (PDOException $e) {
        error_log("漂流瓶操作错误: " . $e->getMessage());
        $error = '操作失败';
    }
    
    // 刷新页面
    header("Location: manage_bottles.php?filter=$filter");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>漂流瓶管理 - 漂流瓶系统</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .filter-tabs {
            display: flex;
            margin-bottom: 20px;
            background: white;
            border-radius: 10px;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-tab {
            padding: 10px 20px;
            margin-right: 10px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }
        
        .filter-tab.active {
            background: #3498db;
            color: white;
        }
        
        .bottle-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .bottle-message {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .bottle-meta {
            font-size: 14px;
            color: #777;
            margin-bottom: 10px;
        }
        
        .bottle-actions {
            margin-top: 15px;
            text-align: right;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>漂流瓶管理</h1>
            <p>管理所有漂流瓶</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">全部</a>
            <a href="?filter=drifting" class="filter-tab <?= $filter === 'drifting' ? 'active' : '' ?>">漂流中</a>
            <a href="?filter=picked" class="filter-tab <?= $filter === 'picked' ? 'active' : '' ?>">已拾取</a>
            <a href="?filter=replied" class="filter-tab <?= $filter === 'replied' ? 'active' : '' ?>">已回复</a>
        </div>
        
        <div class="bottles-list">
            <?php foreach ($bottles as $bottle): ?>
                <div class="bottle-item">
                    <div class="bottle-message">
                        <?= nl2br(htmlspecialchars($bottle['message'])) ?>
                    </div>
                    
                    <div class="bottle-meta">
                        发送者: <?= htmlspecialchars($bottle['sender_name']) ?> | 
                        性别: <?= htmlspecialchars($bottle['sender_gender']) ?> | 
                        发送时间: <?= date('Y-m-d H:i', strtotime($bottle['send_time'])) ?>
                        <?php if ($bottle['receiver_name']): ?>
                            | 接收者: <?= htmlspecialchars($bottle['receiver_name']) ?>
                        <?php endif; ?>
                        <?php if ($bottle['receive_time']): ?>
                            | 接收时间: <?= date('Y-m-d H:i', strtotime($bottle['receive_time'])) ?>
                        <?php endif; ?>
                        | 状态: 
                        <span class="status-<?= $bottle['status'] ?>">
                            <?= $bottle['status'] === 'drifting' ? '漂流中' : 
                                 ($bottle['status'] === 'picked' ? '已拾取' : '已回复') ?>
                        </span>
                    </div>
                    
                    <?php if ($bottle['reply_content']): ?>
                        <div class="reply-content">
                            <strong>回复内容:</strong><br>
                            <?= nl2br(htmlspecialchars($bottle['reply_content'])) ?>
                            <div class="bottle-meta">
                                回复时间: <?= date('Y-m-d H:i', strtotime($bottle['reply_time'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="bottle-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="bottle_id" value="<?= $bottle['id'] ?>">
                            <button type="submit" name="action" value="delete" class="btn-delete" 
                                    onclick="return confirm('确定要删除这个漂流瓶吗？')">删除</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($bottles)): ?>
                <div class="empty-state">
                    <p>暂无漂流瓶</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
