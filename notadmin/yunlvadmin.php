<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// 检查管理员权限
require_admin();

// 获取统计数据
$user_count = $pdo->query("SELECT COUNT(*) FROM pk_user")->fetchColumn();
$bottle_count = $pdo->query("SELECT COUNT(*) FROM pk_bottle")->fetchColumn();
$unread_count = $pdo->query("SELECT COUNT(*) FROM pk_bottle WHERE status = 'unread'")->fetchColumn();

// 获取管理员层级
$admin_hierarchy = get_admin_hierarchy($_SESSION['user_id']);

// 获取下级管理员
$subordinate_admins = get_subordinate_admins($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>云旅管理系统 - 后台首页</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #2c3e50, #1a2530);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-title {
            font-size: 18px;
            color: #777;
        }
        
        .admin-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
        }
        
        .admin-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .section-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .hierarchy-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .hierarchy-item {
            padding: 10px 0;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            align-items: center;
        }
        
        .hierarchy-item:last-child {
            border-bottom: none;
        }
        
        .level-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .level-super {
            background-color: #e74c3c;
            color: white;
        }
        
        .level-senior {
            background-color: #f39c12;
            color: white;
        }
        
        .level-junior {
            background-color: #3498db;
            color: white;
        }
        
        .admin-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .admin-item {
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            align-items: center;
        }
        
        .admin-item:last-child {
            border-bottom: none;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .admin-info {
            flex: 1;
        }
        
        .admin-name {
            font-weight: bold;
        }
        
        .admin-meta {
            font-size: 14px;
            color: #777;
        }
        
        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .menu-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .menu-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: #3498db;
        }
        
        .add-admin-btn {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div>
                <h1>云旅管理系统 - 后台控制台</h1>
                <p>欢迎回来，<?= $_SESSION['username'] ?> (<?= $_SESSION['admin_level'] ?>级管理员)</p>
            </div>
            <div>
                <a href="../../index.php" class="btn btn-light">返回前台</a>
                <a href="../../logout.php" class="btn btn-outline-light ml-2">退出登录</a>
            </div>
        </div>
        
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?= $user_count ?></div>
                <div class="stat-title">注册用户</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📜</div>
                <div class="stat-number"><?= $bottle_count ?></div>
                <div class="stat-title">漂流瓶总数</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📬</div>
                <div class="stat-number"><?= $unread_count ?></div>
                <div class="stat-title">未读漂流瓶</div>
            </div>
        </div>
        
        <div class="admin-content">
            <div class="admin-section">
                <div class="section-header">
                    <h2>管理员层级</h2>
                </div>
                
                <ul class="hierarchy-list">
                    <?php foreach ($admin_hierarchy as $admin): ?>
                        <li class="hierarchy-item">
                            <div class="admin-avatar"><?= mb_substr($admin['username'], 0, 1) ?></div>
                            <div class="admin-info">
                                <div class="admin-name">
                                    <?= htmlspecialchars($admin['username']) ?>
                                    <span class="level-badge level-<?= $admin['level'] ?>">
                                        <?= $admin['level'] === 'super' ? '超级管理员' : ($admin['level'] === 'senior' ? '高级管理员' : '初级管理员') ?>
                                    </span>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="admin-section">
                <div class="section-header">
                    <h2>下级管理员</h2>
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addAdminModal">添加管理员</button>
                </div>
                
                <?php if (count($subordinate_admins) > 0): ?>
                    <ul class="admin-list">
                        <?php foreach ($subordinate_admins as $admin): ?>
                            <li class="admin-item">
                                <div class="admin-avatar"><?= mb_substr($admin['username'], 0, 1) ?></div>
                                <div class="admin-info">
                                    <div class="admin-name">
                                        <?= htmlspecialchars($admin['username']) ?>
                                        <span class="level-badge level-<?= $admin['admin_level'] ?>">
                                            <?= $admin['admin_level'] === 'super' ? '超级管理员' : ($admin['admin_level'] === 'senior' ? '高级管理员' : '初级管理员') ?>
                                        </span>
                                    </div>
                                    <div class="admin-meta">
                                        邮箱: <?= htmlspecialchars($admin['email']) ?> | 
                                        注册时间: <?= date('Y-m-d', strtotime($admin['reg_time'])) ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p>暂无下级管理员</p>
                        <button class="btn btn-primary mt-2" data-toggle="modal" data-target="#addAdminModal">添加管理员</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="admin-menu">
            <div class="menu-card">
                <div class="menu-icon">👥</div>
                <h3>用户管理</h3>
                <p>管理所有注册用户</p>
                <a href="manage_users.php" class="btn btn-primary mt-2">进入管理</a>
            </div>
            
            <div class="menu-card">
                <div class="menu-icon">📜</div>
                <h3>漂流瓶管理</h3>
                <p>查看和管理所有漂流瓶</p>
                <a href="manage_bottles.php" class="btn btn-primary mt-2">进入管理</a>
            </div>
            
            <div class="menu-card">
                <div class="menu-icon">⚙️</div>
                <h3>系统设置</h3>
                <p>配置系统参数</p>
                <a href="system_settings.php" class="btn btn-primary mt-2">进入设置</a>
            </div>
        </div>
    </div>
    
    <!-- 添加管理员模态框 -->
    <div class="modal fade" id="addAdminModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">添加管理员</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addAdminForm" method="post" action="add_admin.php">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="adminUsername">用户名</label>
                            <input type="text" class="form-control" id="adminUsername" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="adminEmail">邮箱</label>
                            <input type="email" class="form-control" id="adminEmail" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="adminPassword">密码</label>
                            <input type="password" class="form-control" id="adminPassword" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="adminLevel">管理员级别</label>
                            <select class="form-control" id="adminLevel" name="admin_level" required>
                                <option value="junior">初级管理员</option>
                                <option value="senior">高级管理员</option>
                                <?php if (is_super_admin()): ?>
                                    <option value="super">超级管理员</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">添加管理员</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../../js/script.js"></script>
    <script>
        // 添加管理员表单验证
        document.getElementById('addAdminForm').addEventListener('submit', function(e) {
            const username = document.getElementById('adminUsername').value;
            
            if (!/^[a-zA-Z0-9_]{3,20}$/.test(username)) {
                e.preventDefault();
                alert('用户名必须是3-20个字符，只能包含字母、数字和下划线');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>
