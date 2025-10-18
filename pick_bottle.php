<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// 检查用户是否登录
require_login();

$error = '';
$bottle = null;
$success = '';

// 处理拾取漂流瓶请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['pick'])) {
    $user_id = $_SESSION['user_id'];
    
    try {
        // 随机获取一个未被拾取的漂流瓶（排除自己发送的）
        $stmt = $pdo->prepare("SELECT b.*, u.username as sender_name 
                              FROM pk_bottle b 
                              JOIN pk_user u ON b.sender_id = u.id 
                              WHERE b.status = 'drifting' AND b.sender_id != ? 
                              ORDER BY RAND() LIMIT 1");
        $stmt->execute([$user_id]);
        $bottle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bottle) {
            // 更新漂流瓶状态为已拾取
            $stmt = $pdo->prepare("UPDATE pk_bottle SET status = 'picked', receiver_id = ?, receive_time = NOW() WHERE id = ?");
            $stmt->execute([$user_id, $bottle['id']]);
            
            log_action('pick_bottle', "拾取了漂流瓶 #" . $bottle['id']);
        } else {
            $error = '暂时没有可拾取的漂流瓶';
        }
    } catch (PDOException $e) {
        error_log("拾取漂流瓶错误: " . $e->getMessage());
        $error = '系统错误，请稍后再试';
    }
}

// 处理回复漂流瓶
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $bottle_id = $_POST['bottle_id'] ?? 0;
    $reply_content = sanitize_input($_POST['reply_content'] ?? '');
    
    if (empty($reply_content)) {
        $error = '回复内容不能为空';
    } else {
        try {
            $user_id = $_SESSION['user_id'];
            
            // 检查用户是否拾取了这个漂流瓶
            $stmt = $pdo->prepare("SELECT id FROM pk_bottle WHERE id = ? AND receiver_id = ?");
            $stmt->execute([$bottle_id, $user_id]);
            
            if ($stmt->fetch()) {
                // 更新漂流瓶回复
                $stmt = $pdo->prepare("UPDATE pk_bottle SET status = 'replied', reply_content = ?, reply_time = NOW() WHERE id = ?");
                if ($stmt->execute([$reply_content, $bottle_id])) {
                    $success = '回复已发送！';
                    log_action('reply_bottle', "回复了漂流瓶 #" . $bottle_id);
                } else {
                    $error = '回复失败，请稍后再试';
                }
            } else {
                $error = '无效的漂流瓶';
            }
        } catch (PDOException $e) {
            error_log("回复漂流瓶错误: " . $e->getMessage());
            $error = '极错误，请稍后再试';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>拾取漂流瓶 - 漂流瓶系统</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .pick-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .pick-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .pick-header h1 {
            color: #3498db;
            margin-bottom: 10px;
        }
        
        .bottle-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .bottle-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, #3498db, #2ecc71);
        }
        
        .bottle-message {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .bottle-meta {
            font-size: 14px;
            color: #777;
            margin-bottom: 15px;
        }
        
        .btn-pick {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #2ecc71, #27ae极);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-pick:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
        }
        
        .reply-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .reply-textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            resize: vertical;
            margin-bottom: 15px;
        }
        
        .btn-reply {
            padding: 10px 20px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-reply:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 15px;
            padding: 10极;
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
    </style>
</head>
<body>
    <div class="pick-container">
        <div class="pick-header">
            <h1>🎣 拾取漂流瓶</h1>
            <p>看看大海带来了什么惊喜</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if ($bottle): ?>
            <div class="bottle-card">
                <div class="bottle-message">
                    <?= nl2br(htmlspecialchars($bottle['message'])) ?>
                </div>
                
                <div class="bottle-meta">
                    来自: <?= htmlspecialchars($bottle['sender_name']) ?> | 
                    性别: <?= htmlspecialchars($bottle['sender_gender']) ?> | 
                    时间: <?= format_time($bottle['send_time']) ?>
                </div>
                
                <div class="reply-form">
                    <h3>💌 回复这个漂流瓶</h3>
                    <form method="POST">
                        <textarea name="reply_content" class="reply-textarea" placeholder="写下你的回复..." required></textarea>
                        <input type="hidden" name="bottle_id" value="<?= $bottle['id'] ?>">
                        <button type="submit" name="reply" class="btn-reply">发送回复</button>
                    </form>
                </div>
            </div>
            
            <form method="POST">
                <button type="submit" class="btn-pick">再捞一个漂流瓶</button>
            </form>
        <?php elseif (empty($error)): ?>
            <div class="empty-state">
                <div class="empty-icon">🌊</div>
                <h2>准备捞取漂流瓶</h2>
                <p>点击下方按钮，捞取一个随机的漂流瓶</p>
                <form method="POST">
                    <button type="submit" class="btn-pick">开始捞取</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
