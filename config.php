<?php

// 连接数据库

$servername = "";

$username = ""; //数据库用户名

$password = "";//数据库密码

$dbname = "";//数据库名称

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {

    die("连接失败: " . $conn->connect_error);

}

?>