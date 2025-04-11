<?php
// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 获取成员ID
$member_id = isset($_GET['id']) ? $_GET['id'] : '';

// 读取家庭成员数据
$members_file = 'data/members.json';
$members = [];
if (file_exists($members_file)) {
    $members = json_decode(file_get_contents($members_file), true) ?? [];
}

// 查找指定ID的成员
$member = null;
foreach ($members as $m) {
    if ($m['id'] === $member_id) {
        $member = $m;
        break;
    }
}

// 如果找不到成员，重定向到成员列表页面
if (!$member) {
    header('Location: members.php?error=not_found');
    exit;
}

// 处理删除操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 删除成员头像
    $avatar_path = 'uploads/members/' . $member['avatar'];
    if (file_exists($avatar_path) && $member['avatar'] !== 'default.jpg') {
        unlink($avatar_path);
    }

    // 从数组中移除成员
    $members = array_filter($members, function($m) use ($member_id) {
        return $m['id'] !== $member_id;
    });

    // 重新索引数组
    $members = array_values($members);

    // 保存到文件
    file_put_contents($members_file, json_encode($members, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // 重定向到成员列表页面
    header('Location: members.php?deleted=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>删除家庭成员 - 家庭回忆录</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/nav.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --border-color: #ddd;
            --danger-color: #dc3545;
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
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            color: var(--danger-color);
            margin-bottom: 10px;
        }

        .confirmation-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .member-info {
            margin: 20px 0;
            padding: 20px;
            background: var(--secondary-color);
            border-radius: 8px;
        }

        .member-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: block;
            object-fit: cover;
        }

        .member-name {
            font-size: 1.2em;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .member-role {
            color: #666;
            margin-bottom: 10px;
        }

        .warning-message {
            color: var(--danger-color);
            margin: 20px 0;
            padding: 15px;
            background-color: rgba(220, 53, 69, 0.1);
            border-radius: 8px;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            text-decoration: none;
            margin: 10px;
        }

        .btn:hover {
            background-color: #357abd;
        }

        .btn-danger {
            background-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .confirmation-card {
                padding: 20px;
            }

            .btn {
                display: block;
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <div class="container">
        <div class="header">
            <h1>删除家庭成员</h1>
            <p>请确认是否要删除以下家庭成员</p>
        </div>

        <div class="confirmation-card">
            <div class="member-info">
                <img src="uploads/members/<?php echo htmlspecialchars($member['avatar']); ?>" 
                     alt="<?php echo htmlspecialchars($member['name']); ?>" 
                     class="member-avatar">
                <div class="member-name"><?php echo htmlspecialchars($member['name']); ?></div>
                <div class="member-role"><?php echo htmlspecialchars($member['role']); ?></div>
            </div>

            <div class="warning-message">
                警告：此操作将永久删除该家庭成员的所有信息，包括头像照片。此操作不可恢复。
            </div>

            <form method="post" onsubmit="return confirm('确定要删除这个家庭成员吗？此操作不可恢复。');">
                <a href="members.php" class="btn btn-secondary">取消</a>
                <button type="submit" class="btn btn-danger">确认删除</button>
            </form>
        </div>
    </div>
</body>
</html> 