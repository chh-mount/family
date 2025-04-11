<?php
// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 获取内容ID
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

// 查找并删除指定内容
$found = false;
foreach ($contents as $key => $content) {
    if ($content['id'] === $id) {
        // 如果是照片类型，删除照片文件
        if ($content['type'] === 'photo' && isset($content['image'])) {
            $image_path = 'uploads/' . $content['image'];
            if (file_exists($image_path) && $image_path !== 'uploads/placeholder.jpg') {
                unlink($image_path);
            }
        }
        // 从数组中删除
        unset($contents[$key]);
        $found = true;
        break;
    }
}

// 重新索引数组
$contents = array_values($contents);

// 保存更新后的数据
if ($found) {
    file_put_contents($data_file, json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// 重定向回列表页面
header('Location: list.php?deleted=' . ($found ? '1' : '0'));
exit; 