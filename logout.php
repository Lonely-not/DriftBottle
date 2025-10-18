<?php
session_start();
require_once 'includes/auth.php';

// 注销用户
logout_user();

// 重定向到登录页面
header('Location: login.php');
exit;
