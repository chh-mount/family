<?php
// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 获取记录ID
$id = $_GET['id'] ?? '';
if (empty($id)) {
    header('Location: list.php');
    exit;
}

// 读取数据文件
$data_file = 'data/contents.json';
$contents = [];
if (file_exists($data_file)) {
    $contents = json_decode(file_get_contents($data_file), true) ?? [];
}

// 查找指定记录
$content = null;
foreach ($contents as $item) {
    if ($item['id'] === $id) {
        $content = $item;
        break;
    }
}

// 如果记录不存在，重定向到列表页面
if ($content === null) {
    header('Location: list.php');
    exit;
}

// 获取上一条和下一条记录
$prev_content = null;
$next_content = null;
$found = false;
foreach ($contents as $item) {
    if ($found) {
        $next_content = $item;
        break;
    }
    if ($item['id'] === $id) {
        $found = true;
    } else {
        $prev_content = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($content['title']); ?> - 家庭回忆录</title>
    <style>
        :root {
            --primary-color: #4a90e2;
            --text-color: #333;
            --bg-color: #f5f5f5;
            --card-bg: #fff;
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
            background-color: var(--bg-color);
        }

        .container {
            max-width: 800px;
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

        .content-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .content-image {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            background-color: #f8f9fa;
        }

        .content-info {
            padding: 20px;
        }

        .content-date {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .content-title {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .content-description {
            color: #444;
            margin-bottom: 15px;
            white-space: pre-wrap;
        }

        .content-sender {
            font-size: 0.9em;
            color: #888;
            margin-bottom: 15px;
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
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

        .nav-link.disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .content-image {
                max-height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>查看记录</h1>
        </div>

        <div class="content-card">
            <?php if ($content['type'] === 'photo'): ?>
            <img src="uploads/<?php echo htmlspecialchars($content['image'] ?? 'placeholder.jpg'); ?>" 
                 alt="<?php echo htmlspecialchars($content['title']); ?>"
                 class="content-image">
            <?php endif; ?>
            
            <div class="content-info">
                <div class="content-date">
                    <?php echo date('Y-m-d H:i', strtotime($content['date'])); ?>
                </div>
                <h2 class="content-title">
                    <?php echo htmlspecialchars($content['title']); ?>
                </h2>
                <p class="content-description">
                    <?php echo htmlspecialchars($content['description']); ?>
                </p>
                <div class="content-sender">
                    发送者: <?php echo htmlspecialchars($content['sender']); ?>
                </div>
            </div>
        </div>

        <div class="navigation">
            <?php if ($prev_content): ?>
            <a href="view.php?id=<?php echo htmlspecialchars($prev_content['id']); ?>" class="nav-link">
                上一条
            </a>
            <?php else: ?>
            <span class="nav-link disabled">上一条</span>
            <?php endif; ?>

            <?php if ($next_content): ?>
            <a href="view.php?id=<?php echo htmlspecialchars($next_content['id']); ?>" class="nav-link">
                下一条
            </a>
            <?php else: ?>
            <span class="nav-link disabled">下一条</span>
            <?php endif; ?>
        </div>

        <a href="list.php" class="back-link">返回列表</a>
    </div>
</body>
</html> 