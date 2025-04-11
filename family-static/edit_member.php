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
    header('Location: members.php');
    exit;
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? '';
    $birthday = $_POST['birthday'] ?? '';
    $description = $_POST['description'] ?? '';

    // 验证必填字段
    if (empty($name) || empty($role) || empty($birthday)) {
        $error = "请填写所有必填字段";
    } else {
        // 处理头像上传
        $avatar = $member['avatar']; // 默认使用现有头像
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/members/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_extension, $allowed_extensions)) {
                $avatar = uniqid() . '.' . $file_extension;
                $target_path = $upload_dir . $avatar;

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
                    // 文件上传成功，删除旧头像（如果不是默认头像）
                    if ($member['avatar'] !== 'default.jpg' && file_exists($upload_dir . $member['avatar'])) {
                        unlink($upload_dir . $member['avatar']);
                    }
                } else {
                    $error = "文件上传失败";
                }
            } else {
                $error = "只允许上传 JPG、JPEG、PNG 或 GIF 格式的图片";
            }
        }

        if (!isset($error)) {
            // 更新成员数据
            foreach ($members as &$m) {
                if ($m['id'] === $member_id) {
                    $m['name'] = $name;
                    $m['role'] = $role;
                    $m['birthday'] = $birthday;
                    $m['description'] = $description;
                    $m['avatar'] = $avatar;
                    break;
                }
            }

            // 保存到文件
            file_put_contents($members_file, json_encode($members, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // 重定向到成员列表页面
            header('Location: members.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑家庭成员 - 家庭回忆录</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/nav.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --border-color: #ddd;
            --error-color: #dc3545;
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

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .avatar-upload {
            border: 2px dashed var(--border-color);
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .avatar-upload:hover {
            border-color: var(--primary-color);
        }

        .avatar-upload input[type="file"] {
            display: none;
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 20px auto;
            overflow: hidden;
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .error-message {
            color: var(--error-color);
            margin-bottom: 20px;
            padding: 10px;
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
        }

        .btn:hover {
            background-color: #357abd;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .form-card {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <div class="container">
        <div class="header">
            <h1>编辑家庭成员</h1>
            <p>更新家人的信息</p>
        </div>

        <div class="form-card">
            <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form action="edit_member.php?id=<?php echo htmlspecialchars($member_id); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label" for="name">姓名 *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($member['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="role">角色 *</label>
                    <input type="text" id="role" name="role" class="form-control" value="<?php echo htmlspecialchars($member['role']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="birthday">生日 *</label>
                    <input type="date" id="birthday" name="birthday" class="form-control" value="<?php echo htmlspecialchars($member['birthday']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">描述</label>
                    <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($member['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">头像</label>
                    <div class="avatar-upload" onclick="document.getElementById('avatar').click()">
                        <input type="file" id="avatar" name="avatar" accept="image/*" class="form-control">
                        <p>点击或拖拽图片到此处上传</p>
                        <small>支持 JPG、JPEG、PNG、GIF 格式</small>
                    </div>
                    <div class="avatar-preview" id="avatarPreview">
                        <img src="uploads/members/<?php echo htmlspecialchars($member['avatar']); ?>" alt="头像预览">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="members.php" class="btn btn-secondary">取消</a>
                    <button type="submit" class="btn">保存更改</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // 处理头像上传预览
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatarPreview');
        const previewImg = avatarPreview.querySelector('img');

        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // 拖拽上传
        const avatarUpload = document.querySelector('.avatar-upload');

        avatarUpload.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--primary-color)';
        });

        avatarUpload.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--border-color)';
        });

        avatarUpload.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--border-color)';
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                const dt = new DataTransfer();
                dt.items.add(file);
                avatarInput.files = dt.files;
                
                // 触发 change 事件以显示预览
                avatarInput.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html> 