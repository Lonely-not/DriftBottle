<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// 检查用户是否登录
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// 获取用户信息
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$is_admin = $_SESSION['is_admin'] ?? false;

// 获取用户未读消息数量
$unread_count = 0;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pk_bottle WHERE receiver_id = ? AND status = 'unread'");
$stmt->execute([$user_id]);
$unread_count = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>漂流瓶系统</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ocean-bg {
            background: linear-gradient(to bottom, #1e5799, #207cca, #2989d8, #1e5799);
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .ocean-bg:before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="1" d="M0,224L48,218.7C96,213,192,203,288,181.3C384,160,480,128,576,138.7C672,149,768,203,864,213.3C960,224,1056,192,1152,165.3C1248,139,1344,117,1392,106.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: bottom;
        }
        
        .bottle-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .welcome-box {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .action-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .action-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .action-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, #3498db, #2ecc71);
        }
        
        .action-icon {
            font-size: 50px;
            margin-bottom: 15px;
            color: #3498db;
        }
        
        .notification-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
        }
        
        .recent-bottles {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .bottle-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .bottle-item:last-child {
            border-bottom: none;
        }
        
        .bottle-icon {
            font-size: 24px;
            margin-right: 15px;
            color: #3498db;
        }
        
        .bottle-content {
            flex: 1;
        }
        
        .bottle-meta {
            font-size: 14px;
            color: #777;
        }
        
        .admin-panel-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body class="ocean-bg">
    <div class="bottle-container">
        <div class="welcome-box">
            <h1>🌊 欢迎回来, <?= htmlspecialchars($username) ?>!</h1>
            <p>您有 <span class="badge badge-danger"><?= $unread_count ?></span> 个未读漂流瓶</p>
        </div>
        
        <div class="action-cards">
            <div class="action-card">
                <div class="action-icon">📨</div>
                <h3>发送漂流瓶</h3>
                <p>写下你的心情，让它漂向远方</p>
                <a href="send_bottle.php" class="btn btn-primary mt-2">立即发送</a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">🎣</div>
                <h3>拾取漂流瓶</h3>
                <p>看看大海带来了什么惊喜</p>
                <a href="pick_bottle.php" class="btn btn-success mt-2">开始拾取</a>
            </div>
            
            <div class="action-card">
                <div class="action-icon">📦</div>
                <h3>我的漂流瓶</h3>
                <p>查看你发送和收到的漂流瓶</p>
                <a href="my_bottles.php" class="btn btn-info mt-2">查看全部</a>
                <?php if ($unread_count > 0): ?>
                    <div class="notification-badge"><?= $unread_count ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="recent-bottles">
            <h2>最近收到的漂流瓶</h2>
            <?php
            $stmt = $pdo->prepare("SELECT b.*, u.username AS sender_name 
                                  FROM pk_bottle b 
                                  JOIN pk_user u ON b.sender_id = u.id 
                                  WHERE b.receiver_id = ? 
                                  ORDER BY b.send_time DESC 
                                  LIMIT 5");
            $stmt->execute([$user_id]);
            $bottles = $stmt->fetchAll();
            
            if (count($bottles) > 0): 
                foreach ($bottles as $bottle): 
            ?>
                <div class="bottle-item">
                    <div class="bottle-icon">📜</div>
                    <div class="bottle-content">
                        <div class="bottle-message"><?= htmlspecialchars(mb_substr($bottle['message'], 0, 50)) ?>...</div>
                        <div class="bottle-meta">
                            来自: <?= htmlspecialchars($bottle['sender_name']) ?> | 
                            时间: <?= date('Y-m-d H:i', strtotime($bottle['send_time'])) ?>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach; 
            else: 
            ?>
                <div class="text-center py-4">
                    <p>你还没有收到任何漂流瓶</p>
                    <p>快去发送一个吧！</p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($is_admin): ?>
            <a href="notadmin/yunlvindex.php" class="admin-panel-link">进入后台管理</a>
        <?php endif; ?>
    </div>
</body>
</html>
