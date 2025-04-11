<?php
// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 检查是否有文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
    $contentType = $_POST['contentType'] ?? '';
    $title = $_POST['title'] ?? '';
    $date = $_POST['date'] ?? '';
    $description = $_POST['description'] ?? '';
    $sender = $_POST['sender'] ?? '';

    // 验证必填字段
    if (empty($title) || empty($date) || empty($description) || empty($sender)) {
        header('Location: add.html?error=missing_fields');
        exit;
    }

    // 读取现有数据
    $data_file = 'data/contents.json';
    $contents = [];
    if (file_exists($data_file)) {
        $contents = json_decode(file_get_contents($data_file), true) ?? [];
    }

    // 处理照片上传
    $images = [];
    if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
        // 确保上传目录存在
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // 处理每个上传的文件
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['photos']['name'][$key];
                $file_type = $_FILES['photos']['type'][$key];
                
                // 验证文件类型
                if (strpos($file_type, 'image/') === 0) {
                    // 生成唯一文件名
                    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_file_name = uniqid() . '.' . $extension;
                    $target_path = $upload_dir . $new_file_name;
                    
                    // 移动文件到目标位置
                    if (move_uploaded_file($tmp_name, $target_path)) {
                        $images[] = $new_file_name;
                    }
                }
            }
        }
    }

    // 创建新内容
    $new_content = [
        'id' => uniqid(),
        'type' => $contentType,
        'title' => $title,
        'date' => $date,
        'description' => $description,
        'sender' => $sender,
        'images' => $images
    ];

    // 添加到内容数组
    $contents[] = $new_content;

    // 按日期排序
    usort($contents, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    // 保存到文件
    file_put_contents($data_file, json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // 重定向到列表页面
    header('Location: list.php?imported=' . count($images));
    exit;
} else {
    // 如果不是 POST 请求，重定向到添加页面
    header('Location: add.html');
    exit;
} 