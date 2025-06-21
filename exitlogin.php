<?php
header("Content-type:text/html;charset=utf-8");
session_start();
// 您可以用$_SESSION['echo']存储任何您希望在退出后弹出的消息
$_SESSION['echo'] = "您已成功退出。";
echo "<script>
    alert('". $_SESSION['echo'] . "');   // 显示弹窗
    window.location.href = 'login.php';  // 跳转
    </script>";
// 为防止潜在的内存泄漏，销毁并重启会话
session_destroy();
session_start();
?>
