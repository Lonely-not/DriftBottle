🌊 DriftBottle System
<div align="center"> <img src="https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white"> <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white"> <img src="https://img.shields.io/badge/License-MIT-green"> </div>
基于PHP/MySQL的漂流瓶社交系统 | PHP/MySQL based drifting bottle message platform

📖 简介 / Introduction
中文
DriftBottle 是一个创新的Web社交应用，模拟海洋漂流瓶的概念。用户可以匿名"扔出"包含消息的漂流瓶，也可以随机"捡到"他人扔出的瓶子。系统支持消息传递、性别标签、回复功能，并采用响应式设计适配所有设备。

English
DriftBottle is an innovative web-based social platform that simulates the concept of ocean drifting bottles. Users can anonymously "throw" message bottles and randomly "pick up" bottles from others. Features include message exchange, gender tags, reply functionality, and responsive design for all devices.

✨ 功能特性 / Features
🔐 用户认证系统 (User Authentication)
注册/登录账户管理

🚢 扔漂流瓶 (Throw Bottles)
发送带性别标签的消息

🎣 随机捡瓶 (Random Bottle Pickup)
随机获取他人漂流瓶

👀 查看所有瓶子 (View All Bottles)
浏览未捡取的漂流瓶列表

💬 回复瓶子 (Reply to Bottles)
对感兴趣的漂流瓶发送回复

📱 响应式设计 (Responsive Design)
适配手机/平板/电脑各种设备

⚙️ 技术栈 / Tech Stack
前端: HTML5, CSS3, JavaScript

后端: PHP 7+, MySQL

安全: MD5加密 + 会话验证

架构: API驱动设计

🚀 安装指南 / Installation
1. 克隆仓库
bash
git clone https://github.com/your-username/driftbottle.git
cd driftbottle
2. 创建数据库
sql
CREATE DATABASE bottle_system;
USE bottle_system;

CREATE TABLE pk_user (
  username VARCHAR(50) PRIMARY KEY,
  password CHAR(32) NOT NULL
);

CREATE TABLE drifting_bottles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  content TEXT NOT NULL,
  gender ENUM('男','女','保密') NOT NULL,
  username VARCHAR(50) NOT NULL,
  picked TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
3. 配置文件设置
创建 config.php 文件：

php
<?php
$servername = "localhost";
$username = "your_db_user";
$password = "your_db_password";
$dbname = "bottle_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
4. 访问系统
通过浏览器访问：
http://your-server/login.php

🧭 使用说明 / Usage
用户注册
访问 /reg.html 页面
填写用户名和密码
点击注册按钮

扔漂流瓶

登录后进入主页
在输入框中写下消息
选择性别标签（男/女/保密）
点击"扔出漂流瓶"按钮

捡漂流瓶

随机捡一个：点击"随机捡一个"按钮获取随机漂流瓶
查看所有：点击"查看所有瓶子"浏览全部漂流瓶
回复瓶子：在瓶子详情下方输入回复内容并发送
