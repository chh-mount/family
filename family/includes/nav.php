<?php
// 获取当前页面名称
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="nav-bar">
    <div class="nav-container">
        <a href="index.html" class="nav-logo">
            <i class="fas fa-home"></i>
            家庭回忆录
        </a>
        <div class="nav-links">
            <a href="index.html" class="nav-link <?php echo $current_page === 'index.html' ? 'active' : ''; ?>">首页</a>
            <a href="list.php" class="nav-link <?php echo $current_page === 'list.php' ? 'active' : ''; ?>">内容列表</a>
            <a href="members.php" class="nav-link <?php echo $current_page === 'members.php' ? 'active' : ''; ?>">家庭成员</a>
            <a href="add.html" class="nav-link <?php echo $current_page === 'add.html' ? 'active' : ''; ?>">添加内容</a>
        </div>
    </div>
</nav> 