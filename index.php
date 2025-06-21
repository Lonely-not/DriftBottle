 
<?php
header("Content-type:text/html;charset=utf-8");
session_start();

// 检查登录状态
if(isset($_SESSION['echo'])){
    // 获取当前登录用户名
    $username = $_SESSION['echo'];
    
    // 如果已经登录了，输出表单
    echo '
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>漂流瓶系统</title>
     <style>
        :root {
            --primary-color: #4CAF50;
            --primary-hover: #45a049;
            --secondary-color: #1e88e5;
            --secondary-hover: #1565c0;
            --text-color: #333;
            --light-bg: #f0f8ff;
            --white: #fff;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --input-bg: #f9f9f9;
            --input-border: #ddd;
            --input-focus-border: #4CAF50;
            --input-placeholder: #aaa;
            --label-color: #666;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Arial", "PingFang SC", "Microsoft YaHei", sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            flex: 1;
        }

        .section {
            background: var(--white);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        h1, h2 {
            color: var(--secondary-color);
            margin-bottom: 15px;
        }

        h2 {
            font-size: 1.5rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .input-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
            align-items: center;
        }

        input[type="text"], textarea, select {
            flex: 1;
            min-width: 200px;
            padding: 12px;
            border: 1px solid var(--input-border);
            border-radius: var(--border-radius);
            font-size: 16px;
            background-color: var(--input-bg);
            color: var(--text-color);
            transition: var(--transition);
        }

        input[type="text"]::placeholder, textarea::placeholder, select::placeholder {
            color: var(--input-placeholder);
        }

        input[type="text"]:focus, textarea:focus, select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--white);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-hover);
        }

        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .result {
            white-space: pre-wrap;
            background: var(--white);
            padding: 15px;
            border: 1px solid #eee;
            border-radius: var(--border-radius);
            min-height: 100px;
            margin-top: 15px;
            overflow-wrap: break-word;
        }

        .bottle-item {
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
        }

        .bottle-meta {
            font-size: 0.9rem;
            color: var(--label-color);
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .emoji {
            font-size: 1.2em;
            margin-right: 5px;
        }

        /* 移动端优化 */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 0;
            }

            .section {
                padding: 15px;
            }

            input[type="text"], textarea, select, .btn {
                padding: 10px 15px;
                font-size: 14px;
                width: 100%;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            h2 {
                font-size: 1.3rem;
            }
        }

        /* 加载动画 */
        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* 响应式表格 */
        .bottle-list {
            width: 100%;
            border-collapse: collapse;
        }

        .bottle-list th, .bottle-list td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        @media (max-width: 600px) {
            .bottle-list {
                display: block;
            }
            
            .bottle-list thead, .bottle-list tbody, 
            .bottle-list th, .bottle-list td, 
            .bottle-list tr {
                display: block;
            }
            
            .bottle-list tr {
                margin-bottom: 15px;
                border: 1px solid #eee;
                border-radius: var(--border-radius);
            }
            
            .bottle-list td {
                border-bottom: none;
                position: relative;
                padding-left: 50%;
            }
            
            .bottle-list td:before {
                position: absolute;
                left: 10px;
                width: 45%;
                padding-right: 10px;
                font-weight: bold;
                content: attr(data-label);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><span class="emoji">🌊</span> 漂流瓶系统 <small>欢迎，'.$username.'</small></h1>
        
        <div class="section">
            <h2><span class="emoji">🔮</span> 扔漂流瓶</h2>
            <div class="input-group">
                <input type="text" id="message" placeholder="写下你的消息...">
                <select id="gender">
                    <option value="男">男生</option>
                    <option value="女">女生</option>
                    <option value="保密">保密</option>
                </select>
                <input type="hidden" id="username" value="'.$username.'">
                <button class="btn btn-primary" onclick="throwBottle()">扔出漂流瓶</button>
            </div>
        </div>

        <div class="section">
            <h2><span class="emoji">🎣</span> 捡漂流瓶</h2>
            <div class="btn-group">
                <button class="btn btn-secondary" onclick="pickBottle()">随机捡一个</button>
                <button class="btn btn-secondary" onclick="viewAllBottles()">查看所有瓶子</button>
            </div>
            <div id="bottleDetails" style="display: none; margin-top: 15px;">
                <div class="bottle-item">
                    <div id="bottleContent"></div>
                    <div class="bottle-meta">
                        <span id="bottleGender"></span>
                        <span id="bottleUsername"></span>
                        <span id="bottleTime"></span>
                    </div>
                </div>
                <div class="input-group" style="margin-top: 15px;">
                    <textarea id="replyContent" placeholder="写下你的回复..."></textarea>
                    <button class="btn btn-primary" onclick="replyBottle()">发送回复</button>
                </div>
            </div>
        </div>

        <div class="section">
            <h2><span class="emoji">📜</span> 结果展示</h2>
            <div id="result" class="result"></div>
        </div>
    </div>

    <script>
        const API_URL = "./api.php";
        let currentBottleId = null;

        function showResult(message, isError = false) {
            const resultDiv = document.getElementById("result");
            resultDiv.innerHTML = message;
            resultDiv.style.color = isError ? "#e74c3c" : "#2ecc71";
            resultDiv.scrollIntoView({ behavior: "smooth" });
        }

        async function throwBottle() {
            const msg = document.getElementById("message").value.trim();
            const gender = document.getElementById("gender").value;
            const username = document.getElementById("username").value;

            if (!msg) {
                showResult("请输入消息内容", true);
                document.getElementById("message").focus();
                return;
            }

            try {
                const response = await fetch(`${API_URL}?msg=${encodeURIComponent(msg)}&gender=${encodeURIComponent(gender)}&username=${encodeURIComponent(username)}&type=1`);
                const text = await response.text();
                showResult(text);
                document.getElementById("message").value = "";
            } catch (error) {
                console.error("Error:", error);
                showResult(`请求失败: ${error.message}`, true);
            }
        }

        async function pickBottle() {
            try {
                const response = await fetch(`${API_URL}?type=2`);
                const text = await response.text();
                showResult(text);
            } catch (error) {
                console.error("Error:", error);
                showResult(`请求失败: ${error.message}`, true);
            }
        }

        async function viewAllBottles() {
            try {
                const response = await fetch(`${API_URL}?type=4`);
                const text = await response.text();
                showResult(text);
            } catch (error) {
                console.error("Error:", error);
                showResult(`请求失败: ${error.message}`, true);
            }
        }

        async function replyBottle() {
            const replyContent = document.getElementById("replyContent").value.trim();

            if (!replyContent) {
                showResult("请输入回复内容", true);
                document.getElementById("replyContent").focus();
                return;
            }

            if (!currentBottleId) {
                showResult("请先捡一个漂流瓶", true);
                return;
            }

            try {
                const response = await fetch(`${API_URL}?action=reply&bottle_id=${currentBottleId}&reply_content=${encodeURIComponent(replyContent)}`);
                const text = await response.text();
                showResult(text);
                document.getElementById("replyContent").value = "";
            } catch (error) {
                console.error("Error:", error);
                showResult(`请求失败: ${error.message}`, true);
            }
        }
    </script>
</body>
</html>';
} else {
    echo "<script>
        alert('你还没登录，请先登录。');
        window.location.href = 'login.php';
    </script>";
    exit();
}
?>
 