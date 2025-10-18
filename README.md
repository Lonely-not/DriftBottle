# 漂流瓶系统

<div align="center">
  <img src="https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white">
  <img src="https://img.shields.io/badge/License-MIT-green">
  <img src="https://img.shields.io/github/repo-size/Lonely-not/DriftBottle?color=blue">
  <a href="https://github.com/Lonely-not/DriftBottle/issues">
    <img src="https://img.shields.io/github/issues/Lonely-not/DriftBottle?color=orange">
  </a>
</div>

## 简介

基于PHP/MySQL的漂流瓶社交系统，模拟海洋漂流瓶的概念，支持匿名消息传递和随机社交互动。

## 功能特性

- 用户认证系统（注册/登录）
- 发送带性别标签的漂流瓶
- 随机拾取他人漂流瓶
- 查看所有未拾取漂流瓶
- 回复感兴趣的漂流瓶
- 多级管理员系统
- 响应式设计适配所有设备

## 安装步骤

1. 上传所有文件到Web服务器
2. 访问 `install.php` 完成安装
3. 使用默认管理员账号登录：
   - 用户名: `YunLv`
   - 密码: `examplepassword`
4. 删除或重命名 `install.php` 文件

## 管理员后台

访问 `/notadmin/yunlvindex.php` 进入管理员后台，支持：

- 用户管理
- 漂流瓶管理
- 系统设置配置
- 添加下级管理员

## 技术栈

- PHP 7.4+
- MySQL 5.7+
- HTML5/CSS3
- JavaScript

## 安全特性

- CSRF防护
- 密码哈希存储
- 输入验证和过滤
- 会话安全管理

## 注意事项

- 安装完成后请删除或重命名 `install.php`
- 定期备份数据库
- 使用强密码策略
- 限制管理员权限