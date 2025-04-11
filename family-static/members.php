<?php
// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 读取家庭成员数据
$members_file = 'data/members.json';
$members = [];
if (file_exists($members_file)) {
    $members = json_decode(file_get_contents($members_file), true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>家庭成员 - 家庭回忆录</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/nav.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --border-color: #ddd;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--secondary-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .member-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .member-card:hover {
            transform: translateY(-5px);
        }

        .member-avatar {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .member-info {
            padding: 20px;
        }

        .member-name {
            font-size: 1.4em;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .member-role {
            color: #666;
            margin-bottom: 10px;
        }

        .member-birthday {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.9em;
        }

        .member-description {
            color: var(--text-color);
            margin-bottom: 20px;
        }

        .member-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #357abd;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 3em;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .empty-state h2 {
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 20px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .members-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .member-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <div class="container">
        <div class="header">
            <h1>家庭成员</h1>
            <p>管理家庭成员信息</p>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
        <div class="success-message">
            家庭成员已成功删除。
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'not_found'): ?>
        <div class="error-message">
            未找到指定的家庭成员。
        </div>
        <?php endif; ?>

        <?php if (empty($members)): ?>
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <h2>暂无家庭成员</h2>
            <p>点击下面的按钮添加家庭成员</p>
            <a href="add_member.php" class="btn">添加家庭成员</a>
        </div>
        <?php else: ?>
        <div class="members-grid">
            <?php foreach ($members as $member): ?>
            <div class="member-card">
                <img src="uploads/members/<?php echo htmlspecialchars($member['avatar']); ?>" 
                     alt="<?php echo htmlspecialchars($member['name']); ?>" 
                     class="member-avatar">
                <div class="member-info">
                    <h2 class="member-name"><?php echo htmlspecialchars($member['name']); ?></h2>
                    <div class="member-role"><?php echo htmlspecialchars($member['role']); ?></div>
                    <div class="member-birthday">
                        <i class="fas fa-birthday-cake"></i>
                        <?php echo htmlspecialchars($member['birthday']); ?>
                    </div>
                    <div class="member-description"><?php echo htmlspecialchars($member['description']); ?></div>
                    <div class="member-actions">
                        <a href="edit_member.php?id=<?php echo htmlspecialchars($member['id']); ?>" class="btn">编辑</a>
                        <a href="delete_member.php?id=<?php echo htmlspecialchars($member['id']); ?>" class="btn btn-danger">删除</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 