<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// 检查用户是否登录
require_login();

$error = '';
$success = '';
$message = '';
$gender = $_SESSION['gender'] ?? '保密';

// 处理发送漂流瓶表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = sanitize_input($_POST['message'] ?? '');
    $gender = sanitize_input($_POST['gender'] ?? '保密');
    
    // 验证输入
    if (empty($message)) {
        $error = '消息内容不能为空';
    } elseif (mb_strlen($message) > 500) {
        $error = '消息内容不能超过500个字符';
    } else {
        try {
            $user_id = $_SESSION['user_id'];
            
            // 检查今日发送限制
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pk_bottle 
                                  WHERE sender_id = ? AND DATE(send_time) = CURDATE()");
            $stmt->execute([$user_id]);
            $today_count = $stmt->fetchColumn();
            
            $max_per_day = get_setting('bottle_limit_per_day', 10);
            if ($today_count >= $max_per_day) {
                $error = "今日发送漂流瓶数量已达上限 ($max_per_day 个)";
            } else {
                // 插入漂流瓶
                $stmt = $pdo->prepare("INSERT INTO pk_bottle (message, sender_id, sender_gender) VALUES (?, ?, ?)");
                if ($stmt->execute([$message, $user_id, $gender])) {
                    $success = '漂流瓶已成功发送！';
                    $message = '';
                    log_action('send_bottle', "发送了漂流瓶: " . mb_substr($message, 0, 50));
                } else {
                    $error = '发送失败，请稍后再试';
                }
            }
        } catch (PDOException $e) {
            error_log("发送漂流瓶错误: " . $e->getMessage());
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
    <title>发送漂流瓶 - 漂流瓶系统</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .send-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .send-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .send-header h1 {
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
        
        textarea.form-control {
            width: 100%;
            min-height: 150px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            resize: vertical;
            transition: border-color 0.3s;
        }
        
        textarea.form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        select.form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            height: 46px;
        }
        
        .btn-send {
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
        
        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 极);
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
        
        .char-count {
            text-align: right;
            font-size: 14px;
            color: #777;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="send-container">
        <div class="send-header">
            <h1>📨 发送漂流瓶</h1>
            <p>写下你的心情，让它漂向远方</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="message">消息内容</label>
                <textarea id="message" name="message" class="form-control" placeholder="写下你想说的话..." required><?= htmlspecialchars($message) ?></textarea>
                <div class="char-count"><span id="char-count">0</span>/500</div>
            </div>
            
            <div class="form-group">
                <label for="gender">你的性别</label>
                <select id="gender" name="gender" class="form-control">
                    <option value="保密" <?= $gender === '保密' ? 'selected' : '' ?>>保密</option>
                    <option value="男" <?= $gender === '男' ? 'selected' : '' ?>>男</option>
                    <option value="女" <?= $gender === '女' ? 'selected' : '' ?>>女</option>
                </select>
            </div>
            
            <button type="submit" class="btn-send">发送漂流瓶</button>
        </form>
    </div>
    
    <script>
        // 字符计数
        const messageTextarea = document.getElementById('message');
        const charCount = document.getElementById('char-count');
        
        messageTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length;
            
            if (length > 500) {
                charCount.style.color = '#e74c3c';
            } else {
                charCount.style.color = '#777';
            }
        });
        
        // 初始化字符计数
        charCount.textContent = messageTextarea.value.length;
    </script>
</body>
</html>
