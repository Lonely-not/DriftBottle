<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap.min.css">  
  <!--背景图-->
<style>
        body {
            background-image: url("http://chat.3qpd.com/chat3.1/img/index.png");
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
        }
    </style>
    <title>登录表单</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], input[type="password"] {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        input[type="submit"] {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
        .button {
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            padding: 5px 10px;
            text-decoration: none;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .danger {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <form role="form" action="logincheck.php" method="post">
        <div class="alert alert-danger">为防止此应用内置邮箱接口被无限滥用，请注册账号后使用！此应用账号和论坛互通，注册后返回本页面用账号登录即可！</div>
        <input type="text" class="message" id="name" name="username" placeholder="请输入账号">
        <br/>
        <input type="password" class="message" id="password" name="password" placeholder="请输入密码">
        <br/>
        <input type="submit" name="submit" class="button" value="登陆" onclick="fn()"/>
        <p>没有账号？<a href="/reg.html">立即注册</a></p>
        <br/>
    </form>
    <script>
        function fn() {
            // 在这里添加你的JavaScript代码
        }
    </script>
</body>
</html>
