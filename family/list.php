<?php
// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 读取内容数据
$data_file = 'data/contents.json';
$contents = [];
if (file_exists($data_file)) {
    $contents = json_decode(file_get_contents($data_file), true) ?? [];
}

// 读取家庭成员数据
$members_file = 'data/members.json';
$members = [];
if (file_exists($members_file)) {
    $members = json_decode(file_get_contents($members_file), true) ?? [];
}

// 获取导入成功的消息数量
$imported_count = isset($_GET['imported']) ? (int)$_GET['imported'] : 0;

// 获取删除结果
$delete_result = isset($_GET['delete']) ? $_GET['delete'] : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>内容列表 - 家庭回忆录</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/nav.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --border-color: #ddd;
            --danger-color: #dc3545;
            --success-color: #28a745;
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

        .nav-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .nav-link {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .nav-link:hover {
            background-color: #357abd;
        }

        .success-message {
            background-color: var(--success-color);
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error-message {
            background-color: var(--danger-color);
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .section-title {
            color: var(--primary-color);
            margin: 40px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .content-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .content-card:hover {
            transform: translateY(-5px);
        }

        .image-gallery {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .image-gallery img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            padding: 10px;
            cursor: pointer;
            border: none;
            border-radius: 50%;
        }

        .image-nav.prev {
            left: 10px;
        }

        .image-nav.next {
            right: 10px;
        }

        .image-counter {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.5);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }

        .content-info {
            padding: 15px;
        }

        .content-date {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .content-title {
            font-size: 1.2em;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .content-description {
            margin-bottom: 10px;
        }

        .content-sender {
            color: #666;
            font-size: 0.9em;
        }

        .content-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }

        .btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
            transition: background-color 0.2s;
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

        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .member-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
            padding: 15px;
        }

        .member-name {
            font-size: 1.2em;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .member-role {
            color: #666;
            font-size: 1em;
            margin-bottom: 10px;
        }

        .member-birthday {
            color: #888;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .member-description {
            color: var(--text-color);
            font-size: 0.9em;
            margin-bottom: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            margin-top: 20px;
        }

        .empty-state h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .add-button {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .add-button:hover {
            background-color: #357abd;
        }

        @media (max-width: 768px) {
            .content-grid, .members-grid {
                grid-template-columns: 1fr;
            }

            .image-gallery, .member-avatar {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <div class="container">
        <div class="header">
            <h1>家庭回忆录</h1>
            <p>珍藏美好时光</p>
        </div>

        <div class="nav-links">
            <a href="add.html" class="nav-link">添加内容</a>
            <a href="members.php" class="nav-link">管理家庭成员</a>
        </div>

        <?php if ($imported_count > 0): ?>
        <div class="success-message">
            成功导入 <?php echo $imported_count; ?> 张照片
        </div>
        <?php endif; ?>

        <?php if ($delete_result === 'success'): ?>
        <div class="success-message">
            内容已成功删除
        </div>
        <?php elseif ($delete_result === 'error'): ?>
        <div class="error-message">
            删除内容时出错
        </div>
        <?php endif; ?>

        <!-- 家庭成员介绍部分 -->
        <h2 class="section-title">家庭成员介绍</h2>
        <?php if (empty($members)): ?>
        <div class="empty-state">
            <h2>暂无家庭成员信息</h2>
            <p>开始添加您的家人信息吧！</p>
            <a href="add_member.php" class="add-button">添加成员</a>
        </div>
        <?php else: ?>
        <div class="members-grid">
            <?php foreach ($members as $member): ?>
            <div class="member-card">
                <img src="uploads/members/<?php echo htmlspecialchars($member['avatar'] ?? 'default.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($member['name']); ?>" 
                     class="member-avatar">
                <div class="member-info">
                    <h3 class="member-name"><?php echo htmlspecialchars($member['name']); ?></h3>
                    <div class="member-role"><?php echo htmlspecialchars($member['role']); ?></div>
                    <div class="member-birthday">生日：<?php echo htmlspecialchars($member['birthday']); ?></div>
                    <p class="member-description"><?php echo htmlspecialchars($member['description']); ?></p>
                    <div class="content-actions">
                        <a href="edit_member.php?id=<?php echo htmlspecialchars($member['id']); ?>" class="btn">编辑</a>
                        <a href="delete_member.php?id=<?php echo htmlspecialchars($member['id']); ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('确定要删除这个家庭成员吗？此操作不可恢复。')">删除</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin: 20px 0;">
            <a href="add_member.php" class="add-button">添加新成员</a>
        </div>
        <?php endif; ?>

        <!-- 内容列表部分 -->
        <h2 class="section-title">家庭回忆内容</h2>
        <?php if (empty($contents)): ?>
        <div class="empty-state">
            <h2>暂无内容</h2>
            <p>开始添加您的第一个回忆吧！</p>
            <a href="add.html" class="add-button">添加内容</a>
        </div>
        <?php else: ?>
        <div class="content-grid">
            <?php foreach ($contents as $content): ?>
            <div class="content-card">
                <div class="image-gallery">
                    <?php if (!empty($content['images'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($content['images'][0]); ?>" alt="<?php echo htmlspecialchars($content['title']); ?>">
                        <?php if (count($content['images']) > 1): ?>
                            <button class="image-nav prev" onclick="showPrevImage(this)">❮</button>
                            <button class="image-nav next" onclick="showNextImage(this)">❯</button>
                            <div class="image-counter">1/<?php echo count($content['images']); ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <img src="images/placeholder.jpg" alt="暂无图片">
                    <?php endif; ?>
                </div>
                <div class="content-info">
                    <div class="content-date"><?php echo htmlspecialchars($content['date']); ?></div>
                    <h3 class="content-title"><?php echo htmlspecialchars($content['title']); ?></h3>
                    <p class="content-description"><?php echo htmlspecialchars($content['description']); ?></p>
                    <div class="content-sender">发送者：<?php echo htmlspecialchars($content['sender']); ?></div>
                    <div class="content-actions">
                        <a href="view.php?id=<?php echo htmlspecialchars($content['id']); ?>" class="btn">查看</a>
                        <a href="edit_content.php?id=<?php echo htmlspecialchars($content['id']); ?>" class="btn">编辑</a>
                        <a href="delete_content.php?id=<?php echo htmlspecialchars($content['id']); ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('确定要删除这条内容吗？此操作不可恢复。')">删除</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function showPrevImage(button) {
            const gallery = button.parentElement;
            const img = gallery.querySelector('img');
            const counter = gallery.querySelector('.image-counter');
            const images = <?php echo json_encode(array_column($contents, 'images')); ?>;
            const currentIndex = parseInt(counter.textContent.split('/')[0]) - 1;
            const contentIndex = Array.from(gallery.parentElement.parentElement.children).indexOf(gallery.parentElement);
            const imageArray = images[contentIndex];
            
            if (currentIndex > 0) {
                img.src = 'uploads/' + imageArray[currentIndex - 1];
                counter.textContent = `${currentIndex}/${imageArray.length}`;
            }
        }

        function showNextImage(button) {
            const gallery = button.parentElement;
            const img = gallery.querySelector('img');
            const counter = gallery.querySelector('.image-counter');
            const images = <?php echo json_encode(array_column($contents, 'images')); ?>;
            const currentIndex = parseInt(counter.textContent.split('/')[0]) - 1;
            const contentIndex = Array.from(gallery.parentElement.parentElement.children).indexOf(gallery.parentElement);
            const imageArray = images[contentIndex];
            
            if (currentIndex < imageArray.length - 1) {
                img.src = 'uploads/' + imageArray[currentIndex + 1];
                counter.textContent = `${currentIndex + 2}/${imageArray.length}`;
            }
        }
    </script>
</body>
</html> 