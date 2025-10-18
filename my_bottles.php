<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// 检查用户是否登录
require_login();

$user_id = $_SESSION['user_id'];
$bottles = [];
$error = '';

// 获取用户发送的漂流瓶
try {
    $stmt = $pdo->prepare("SELECT b.*, u.username as receiver_name 
                          FROM pk_bottle b 
                          LEFT JOIN pk_user u ON b.receiver_id = u.id 
                          WHERE b.sender_id = ? 
                          ORDER BY b.send_time DESC");
    $stmt->execute([$user_id]);
    $sent_bottles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("获取发送的漂流瓶错误: " . $e->getMessage());
    $error = '获取数据失败';
}

// 获取用户收到的漂流瓶
try {
    $stmt = $pdo->prepare("SELECT b.*, u.username as sender_name 
                          FROM pk_bottle b 
                          JOIN pk_user u ON b.sender_id = u.id 
                          WHERE b.receiver_id = ? 
                          ORDER BY b.receive_time DESC");
    $stmt->execute([$user_id]);
    $received_bottles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("获取收到的漂流瓶错误: " . $e->getMessage());
    $error = '获取数据失败';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的漂流瓶 - 漂流瓶系统</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .my-bottles-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .my-bottles-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .my-bottles-header h1 {
            color: #3498db;
            margin-bottom: 10px;
        }
        
        .bottle-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .bottle-tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .bottle-tab.active {
            border-bottom-color: #3498db;
            color: #3498db;
            font-weight: bold;
        }
        
        .bottle-tab:hover {
            background: #f5f5f5;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .bottle-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .bottle-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .bottle-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
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
        
        .bottle-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-drifting {
            background: #f39c12;
            color: white;
        }
        
        .status-picked {
            background: #3498db;
            color: white;
        }
        
        .status-read {
            background: #2ecc71;
            color: white;
        }
        
        .status-replied {
            background: #9b59b6;
            color: white;
        }
        
        .reply-content {
            margin-top: 15px;
            padding: 15px;
            background: #eafaf1;
            border-radius: 8px;
            border-left: 4px solid #2ecc71;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .empty-icon {
            font-size: 60px;
            margin-bottom: 20px;
            color: #ddd;
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
    <div class="my-bottles-container">
        <div class="my-bottles-header">
            <h1>📦 我的漂流瓶</h1>
            <p>查看你发送和收到的漂流瓶</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="bottle-tabs">
            <div class="bottle-tab active" data-tab="sent">我发送的</div>
            <div class="bottle-tab" data-tab="received">我收到的</div>
        </div>
        
        <div class="tab-content active" id="sent-tab">
            <?php if (count($sent_bottles) > 0): ?>
                <ul class="bottle-list">
                    <?php foreach ($sent_bottles as $bottle): ?>
                        <li class="bottle-item">
                            <div class="bottle-message">
                                <?= nl2br(htmlspecialchars($bottle['message'])) ?>
                            </div>
                            
                            <div class="bottle-meta">
                                发送时间: <?= format_time($bottle['send_time']) ?> | 
                                状态: <span class="bottle-status status-<?= $bottle['status'] ?>">
                                    <?= $bottle['status'] === 'drifting' ? '漂流中' : 
                                         ($bottle['status'] === 'picked' ? '已被拾取' : 
                                         ($bottle['status'] === 'read' ? '已阅读' : '已回复')) ?>
                                </span>
                                <?php if ($bottle['receiver_name']): ?>
                                    | 接收者: <?= htmlspecialchars($bottle['receiver_name']) ?>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($bottle['reply_content']): ?>
                                <div class="reply-content">
                                    <strong>回复:</strong><br>
                                    <?= nl2br(htmlspecialchars($bottle['reply_content'])) ?>
                                    <div class="bottle-meta">
                                        回复时间: <?= format_time($bottle['reply_time']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📨</div>
                    <h2>还没有发送过漂流瓶</h2>
                    <p>快去发送一个漂流瓶吧！</p>
                    <a href="send_bottle.php" class="btn btn-primary">发送漂流瓶</a>
                </极>
            <?php endif; ?>
        </div>
        
        <div class="tab-content" id="received-tab">
            <?php if (count($received_bottles) > 0): ?>
                <ul class="bottle-list">
                    <?php foreach ($received_bottles as $bottle): ?>
                        <li class="bottle-item">
                            <div class="bottle-message">
                                <?= nl2br(htmlspecialchars($bottle['message'])) ?>
                            </div>
                            
                            <div class="bottle-meta">
                                来自: <?= htmlspecialchars($bottle['sender_name']) ?> | 
                                性别: <?= htmlspecialchars($bottle['sender_gender']) ?> | 
                                发送时间: <?= format_time($bottle['send_time']) ?> | 
                                拾取时间: <?= format_time($bottle['receive_time']) ?>
                            </div>
                            
                            <?php if ($bottle['reply_content']): ?>
                                <div class="reply-content">
                                    <strong>你的回复:</strong><br>
                                    <?= nl2br(htmlspecialchars($bottle['reply_content'])) ?>
                                    <div class="bottle-meta">
                                        回复时间: <?= format_time($bottle['reply_time']) ?>
                                    </div>
                                </div>
                            <?php elseif ($bottle['status'] === 'picked'): ?>
                                <div class="reply-form">
                                    <form method="POST" action="pick_bottle.php">
                                        <textarea name="reply_content" class="reply-textarea" placeholder="写下你的回复..." required></textarea>
                                        <input type="hidden" name="bottle_id" value="<?= $bottle['id'] ?>">
                                        <button type="submit" name="reply" class="btn btn-primary">发送回复</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📥</div>
                    <h2>还没有收到漂流瓶</h2>
                    <p>快去捞取一个漂流瓶吧！</p>
                    <a href="pick_bottle.php" class="btn btn-primary">捞取漂流瓶</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // 标签切换
        const tabs = document.querySelectorAll('.bottle-tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // 移除所有活动状态
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(tc => tc.classList.remove('active'));
                
                // 添加当前活动状态
                this.classList.add('active');
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });
    </script>
</body>
</html>
