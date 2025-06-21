 
<?php
// 数据库配置
include 'config.php';

// 启动会话
session_start();

// 设置响应头为纯文本
header("Content-Type: text/plain; charset=utf-8");

// 确保在输出前没有任何输出
ob_start();

$response = '';

try {
    // 获取当前登录用户名
    $current_user = isset($_SESSION['echo']) ? $_SESSION['echo'] : null;
    
    if (!$current_user) {
        throw new Exception('请先登录系统');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $msg = isset($_GET['msg']) ? $conn->real_escape_string($_GET['msg']) : null;
        $gender = isset($_GET['gender']) ? $conn->real_escape_string($_GET['gender']) : null;
        $username = isset($_GET['username']) ? $conn->real_escape_string($_GET['username']) : null;
        $type = isset($_GET['type']) ? intval($_GET['type']) : 0;

        // 验证提交的用户名是否与当前登录用户一致
        if ($username && $username !== $current_user) {
            throw new Exception('用户身份不匹配');
        }

        if ($msg && $gender && $username && ($type === 1 || $type === 0)) {
            // 丢漂流瓶
            $sql = "INSERT INTO drifting_bottles (content, gender, username, picked) VALUES ('$msg', '$gender', '$username', 0)";
            if ($conn->query($sql) === TRUE) {
                $response = "漂流瓶已成功丢出！";
            } else {
                throw new Exception('数据库错误: ' . $conn->error);
            }
        } elseif ($type === 2) {
            // 捡漂流瓶 - 确保不会捡到自己扔的瓶子
            $sql = "SELECT id, content, gender, username FROM drifting_bottles 
                    WHERE picked = 0 AND username != '$current_user' 
                    ORDER BY RAND() LIMIT 1";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $id = $row['id'];

                // 更新为已捡取
                $update_sql = "UPDATE drifting_bottles SET picked = 1 WHERE id = $id";
                $conn->query($update_sql);

                $response = "漂流瓶内容: " . $row['content'] . "\n";
                $response .= "性别: " . $row['gender'] . "\n";
                $response .= "来自: " . $row['username'];
            } else {
                $response = "没有找到可捡起的漂流瓶。";
            }
        } elseif ($type === 4) {
            // 查看所有未捡漂流瓶 - 排除自己扔的
            $sql = "SELECT id, content, gender, username FROM drifting_bottles 
                    WHERE picked = 0 AND username != '$current_user'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $response = "未捡起的漂流瓶列表:\n\n";
                while ($row = $result->fetch_assoc()) {
                    $response .= "ID: " . $row['id'] . " - 内容: " . $row['content'] . "\n";
                    $response .= "性别: " . $row['gender'] . " - 来自: " . $row['username'] . "\n\n";
                }
            } else {
                $response = "没有找到可捡起的漂流瓶。";
            }
        } elseif ($type === 3) {
            // 查看特定漂流瓶 - 确保不是自己扔的
            $bottleId = isset($_GET['bottle_id']) ? intval($_GET['bottle_id']) : 0;

            $sql = "SELECT id, content, gender, username FROM drifting_bottles 
                    WHERE id = $bottleId AND picked = 0 AND username != '$current_user'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $response = "漂流瓶内容: " . $row['content'] . "\n";
                $response .= "性别: " . $row['gender'] . "\n";
                $response .= "来自: " . $row['username'];
            } else {
                $response = "漂流瓶不存在、已被捡取或是你自己扔的。";
            }
        } else {
            $response = "无效的操作类型。";
        }
    } else {
        $response = "不支持的请求方法。";
    }
} catch (Exception $e) {
    $response = "错误: " . $e->getMessage();
}

// 清除输出缓冲区
ob_end_clean();

// 返回纯文本响应
echo $response;

$conn->close();
?>
 