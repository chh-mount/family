<?php
// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 读取数据文件
$dataFile = 'data/contents.json';
if (!file_exists($dataFile)) {
    die('数据文件不存在');
}

$jsonData = file_get_contents($dataFile);
if ($jsonData === false) {
    die('读取数据文件失败');
}

$contents = json_decode($jsonData, true) ?? [];

// 获取所有照片类型的内容
$photos = array_filter($contents, function($item) {
    return $item['type'] === 'photo';
});

// 照片分类
$categories = [
    'family' => '家庭合影',
    'travel' => '旅行照片',
    'event' => '重要事件',
    'daily' => '日常生活',
    'other' => '其他'
];

// 按分类组织照片
$photosByCategory = [];
foreach ($categories as $key => $name) {
    $photosByCategory[$key] = [];
}

foreach ($photos as $photo) {
    $category = $photo['category'] ?? 'other';
    if (!isset($photosByCategory[$category])) {
        $category = 'other';
    }
    $photosByCategory[$category][] = $photo;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>照片分类 - 家庭回忆录</title>
    <style>
        :root {
            --primary-color: #e74c3c;
            --secondary-color: #2c3e50;
            --accent-color: #3498db;
            --light-color: #f5f5f5;
            --dark-color: #333;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Microsoft YaHei', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: var(--light-color);
            padding-top: 70px;
        }
        
        .navbar {
            background-color: white;
            box-shadow: var(--box-shadow);
            padding: 1rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 1.5rem;
        }
        
        .nav-links a {
            color: var(--dark-color);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }
        
        .nav-links a:hover {
            color: var(--primary-color);
        }
        
        .nav-links a.active {
            color: var(--primary-color);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .page-title {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
        
        .category-tabs {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .category-tab {
            padding: 0.5rem 1.5rem;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .category-tab:hover {
            background-color: #f0f0f0;
        }
        
        .category-tab.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .photo-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }
        
        .photo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .photo-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .photo-info {
            padding: 1rem;
        }
        
        .photo-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
        }
        
        .photo-date {
            font-size: 0.9rem;
            color: #666;
        }
        
        .photo-description {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #666;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .empty-category {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: var(--border-radius);
            color: #666;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .photo-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }
            
            .category-tabs {
                gap: 0.5rem;
            }
            
            .category-tab {
                padding: 0.4rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="nav-logo">家庭回忆录</a>
            <div class="nav-links">
                <a href="index.html">首页</a>
                <a href="list.php">内容列表</a>
                <a href="calendar.php">日历</a>
                <a href="photos.php" class="active">照片分类</a>
                <a href="add.html">添加内容</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <header class="page-header">
            <h1 class="page-title">照片分类</h1>
            <div class="category-tabs">
                <button class="category-tab active" data-category="all">全部照片</button>
                <?php foreach ($categories as $key => $name): ?>
                <button class="category-tab" data-category="<?php echo htmlspecialchars($key); ?>">
                    <?php echo htmlspecialchars($name); ?>
                </button>
                <?php endforeach; ?>
            </div>
        </header>

        <?php foreach ($categories as $key => $name): ?>
        <div class="photo-grid" id="category-<?php echo htmlspecialchars($key); ?>" style="display: none;">
            <?php if (empty($photosByCategory[$key])): ?>
            <div class="empty-category">
                该分类下暂无照片
            </div>
            <?php else: ?>
            <?php foreach ($photosByCategory[$key] as $photo): ?>
            <div class="photo-card">
                <img src="<?php echo htmlspecialchars($photo['image_path']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>" class="photo-img">
                <div class="photo-info">
                    <h3 class="photo-title"><?php echo htmlspecialchars($photo['title']); ?></h3>
                    <div class="photo-date"><?php echo htmlspecialchars($photo['date']); ?></div>
                    <p class="photo-description"><?php echo htmlspecialchars($photo['description']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <!-- 全部照片网格 -->
        <div class="photo-grid" id="category-all">
            <?php if (empty($photos)): ?>
            <div class="empty-category">
                暂无照片
            </div>
            <?php else: ?>
            <?php foreach ($photos as $photo): ?>
            <div class="photo-card">
                <img src="<?php echo htmlspecialchars($photo['image_path']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>" class="photo-img">
                <div class="photo-info">
                    <h3 class="photo-title"><?php echo htmlspecialchars($photo['title']); ?></h3>
                    <div class="photo-date"><?php echo htmlspecialchars($photo['date']); ?></div>
                    <p class="photo-description"><?php echo htmlspecialchars($photo['description']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // 分类切换功能
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // 更新标签状态
                document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // 显示对应分类的照片
                const category = tab.dataset.category;
                document.querySelectorAll('.photo-grid').forEach(grid => {
                    grid.style.display = 'none';
                });
                
                if (category === 'all') {
                    document.getElementById('category-all').style.display = 'grid';
                } else {
                    document.getElementById(`category-${category}`).style.display = 'grid';
                }
            });
        });
    </script>
</body>
</html> 