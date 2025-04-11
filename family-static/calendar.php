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

// 获取当前年月
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

// 确保月份在1-12之间
if ($month < 1) {
    $month = 12;
    $year--;
} elseif ($month > 12) {
    $month = 1;
    $year++;
}

// 获取月份的第一天是星期几
$firstDay = date('w', mktime(0, 0, 0, $month, 1, $year));
// 获取月份的总天数
$daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));

// 按日期组织内容
$eventsByDate = [];
foreach ($contents as $content) {
    $date = $content['date'];
    $dateObj = date_parse($date);
    if ($dateObj['year'] == $year && $dateObj['month'] == $month) {
        $day = $dateObj['day'];
        if (!isset($eventsByDate[$day])) {
            $eventsByDate[$day] = [];
        }
        $eventsByDate[$day][] = $content;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>家庭日历 - 家庭回忆录</title>
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .calendar-title {
            font-size: 2rem;
            color: var(--secondary-color);
        }
        
        .calendar-nav {
            display: flex;
            gap: 1rem;
        }
        
        .calendar-nav a {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            color: var(--dark-color);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .calendar-nav a:hover {
            background-color: #f0f0f0;
        }
        
        .calendar-nav a.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .calendar {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            border-bottom: 1px solid #eee;
        }
        
        .calendar-day-header {
            padding: 1rem;
            text-align: center;
            font-weight: bold;
            background-color: var(--secondary-color);
            color: white;
        }
        
        .calendar-day {
            min-height: 120px;
            padding: 0.5rem;
            border-right: 1px solid #eee;
            border-bottom: 1px solid #eee;
            position: relative;
        }
        
        .calendar-day:nth-child(7n) {
            border-right: none;
        }
        
        .day-number {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
        }
        
        .day-number.today {
            background-color: var(--primary-color);
            color: white;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .day-events {
            font-size: 0.8rem;
        }
        
        .event-item {
            margin-bottom: 0.3rem;
            padding: 0.2rem 0.4rem;
            background-color: #f0f0f0;
            border-radius: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .event-item:hover {
            background-color: #e0e0e0;
        }
        
        .event-item.photo {
            border-left: 3px solid #e74c3c;
        }
        
        .event-item.story {
            border-left: 3px solid #3498db;
        }
        
        .event-item.event {
            border-left: 3px solid #2ecc71;
        }
        
        .add-event-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--primary-color);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: var(--transition);
            z-index: 100;
            text-decoration: none;
        }
        
        .add-event-btn:hover {
            transform: scale(1.1);
            background-color: #c0392b;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 90%;
            max-width: 500px;
            padding: 2rem;
            position: relative;
        }
        
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
        
        .modal-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--secondary-color);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-color);
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
            font-weight: 500;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #c0392b;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        
        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
        }
        
        .empty-day {
            color: #ccc;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .calendar-day {
                min-height: 80px;
                padding: 0.3rem;
            }
            
            .day-number {
                font-size: 0.8rem;
            }
            
            .event-item {
                font-size: 0.7rem;
                padding: 0.1rem 0.3rem;
            }
            
            .nav-links {
                display: none;
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
                <a href="calendar.php" class="active">日历</a>
                <a href="add.html">添加内容</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="calendar-header">
            <h1 class="calendar-title"><?php echo $year; ?>年<?php echo $month; ?>月</h1>
            <div class="calendar-nav">
                <a href="?year=<?php echo $year; ?>&month=<?php echo $month-1; ?>">上个月</a>
                <a href="?year=<?php echo date('Y'); ?>&month=<?php echo date('n'); ?>">本月</a>
                <a href="?year=<?php echo $year; ?>&month=<?php echo $month+1; ?>">下个月</a>
            </div>
        </div>
        
        <div class="calendar">
            <div class="calendar-grid">
                <div class="calendar-day-header">日</div>
                <div class="calendar-day-header">一</div>
                <div class="calendar-day-header">二</div>
                <div class="calendar-day-header">三</div>
                <div class="calendar-day-header">四</div>
                <div class="calendar-day-header">五</div>
                <div class="calendar-day-header">六</div>
                
                <?php
                // 填充月初空白日期
                for ($i = 0; $i < $firstDay; $i++) {
                    echo '<div class="calendar-day empty-day"></div>';
                }
                
                // 填充日期
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $isToday = ($day == date('j') && $month == date('n') && $year == date('Y'));
                    $dayClass = $isToday ? 'day-number today' : 'day-number';
                    
                    echo '<div class="calendar-day">';
                    echo '<div class="' . $dayClass . '">' . $day . '</div>';
                    
                    // 显示当天的活动
                    if (isset($eventsByDate[$day])) {
                        echo '<div class="day-events">';
                        foreach ($eventsByDate[$day] as $event) {
                            $typeClass = $event['type'];
                            echo '<div class="event-item ' . $typeClass . '" onclick="window.location.href=\'view.php?id=' . htmlspecialchars($event['id']) . '\'">';
                            echo htmlspecialchars($event['title']);
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
                
                // 填充月末空白日期
                $lastDay = ($firstDay + $daysInMonth) % 7;
                if ($lastDay > 0) {
                    for ($i = $lastDay; $i < 7; $i++) {
                        echo '<div class="calendar-day empty-day"></div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
    
    <a href="add.html" class="add-event-btn" title="添加新内容">+</a>
    
    <!-- 添加事件模态框 -->
    <div class="modal" id="addEventModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h2 class="modal-title">添加新事件</h2>
            <form action="save.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label" for="title">标题</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="date">日期</label>
                    <input type="date" id="date" name="date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="contentType">内容类型</label>
                    <select id="contentType" name="contentType" class="form-control" required>
                        <option value="photo">照片</option>
                        <option value="story">故事</option>
                        <option value="event">大事记</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="description">描述</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="photos">照片（可选）</label>
                    <input type="file" id="photos" name="photos[]" class="form-control" multiple accept="image/*">
                </div>
                <button type="submit" class="btn btn-block">保存</button>
            </form>
        </div>
    </div>
    
    <script>
        // 打开模态框
        function openModal() {
            document.getElementById('addEventModal').style.display = 'flex';
        }
        
        // 关闭模态框
        function closeModal() {
            document.getElementById('addEventModal').style.display = 'none';
        }
        
        // 点击模态框外部关闭
        window.onclick = function(event) {
            const modal = document.getElementById('addEventModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // 设置添加按钮点击事件
        document.querySelector('.add-event-btn').addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
        
        // 设置日期输入框的默认值为今天
        document.getElementById('date').valueAsDate = new Date();
    </script>
</body>
</html> 