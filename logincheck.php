<?php
//页面编码
header("Content-type:text/html;charset=utf-8");
//隐藏报错信息
error_reporting(E_ALL^E_NOTICE^E_WARNING);
//储存登录行为
session_start();
//直接把用户名赋给echo
$_SESSION['echo']="$_POST[username]";
if(isset($_POST["submit"]) && $_POST["submit"] == "登陆")  
{  
    $user = $_POST["username"];  
    $psw = $_POST["password"];  
    if($user == "" || $psw == "")  
    {  
     echo "<script>alert('用户名或密码不能为空'); history.go(-1);</script>";  
    }  
    else  
    {  
        $psw_md5 = md5($psw); // 将密码用md5加密
        $conn = mysqli_connect("","","");   //连接数据库  
        mysqli_select_db($conn, "");  //选择数据库  
        mysqli_query($conn, "SET NAMES 'utf8'");//设定字符集  
        $sql = "select username,password from pk_user where username = '$user' and password = '{$psw_md5}'";  
        $result = mysqli_query($conn, $sql);  
        $num = mysqli_num_rows($result);  
        if($num)  
        {  
            $row = mysqli_fetch_array($result);  
            //验证通过后跳转 
             echo "登录成功！
<script>alert('登录成功');</script>"; echo "<script>window.location.href = 'index.php';</script>"; 
        }  
        else  
        {  
                echo "<script>alert('用户名或密码不正确！');history.go(-1);</script>";  
        }  
    }  
}  
else  
{  
     echo "<script>alert('登录失败'); history.go(-1);</script>";  
}
?>
