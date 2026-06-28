<?php
/** @var array<string, mixed> $editUser */
require ROOT_DIR . '/views/common/navbar.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑用户</title>
    <link rel="stylesheet" href="/static/css/qzone-base.css">
    <style>
        .readonly-stu-no {
            padding: 10px 12px;
            font-size: 14px;
            color: #666;
            background: #f5f5f5;
            border-radius: 6px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="qzone-page">
        <div class="qzone-card" style="max-width: 640px; margin: 0 auto;">
            <div class="qzone-page-header">
                <h1>编辑用户：<?php echo htmlspecialchars($editUser['name']); ?></h1>
            </div>
            <form method="POST" action="/admin/updateUser">
                <?php echo csrfField(); ?>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">学号：</label>
                    <input type="hidden" name="stu_no" value="<?php echo htmlspecialchars($editUser['stu_no']); ?>">
                    <span class="readonly-stu-no"><?php echo htmlspecialchars($editUser['stu_no']); ?></span>
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">姓名：</label>
                    <input type="text" name="name" class="qzone-form-input" style="max-width:300px;" value="<?php echo htmlspecialchars($editUser['name']); ?>" required>
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">邮箱：</label>
                    <input type="email" name="email" class="qzone-form-input" style="max-width:300px;" value="<?php echo htmlspecialchars($editUser['email'] ?? ''); ?>">
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">权限：</label>
                    <select name="permissions" class="qzone-form-select" style="max-width:300px;">
                        <option value="student" <?php echo $editUser['permissions'] == 'student' ? 'selected' : ''; ?>>学生</option>
                        <option value="teacher" <?php echo $editUser['permissions'] == 'teacher' ? 'selected' : ''; ?>>老师</option>
                    </select>
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">班级：</label>
                    <input type="text" name="stu_class" class="qzone-form-input" style="max-width:300px;" value="<?php echo htmlspecialchars($editUser['stu_class']); ?>">
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">分数：</label>
                    <input type="number" name="score" class="qzone-form-input" style="max-width:300px;" value="<?php echo htmlspecialchars($editUser['score'] ?? 0); ?>" min="0" step="1">
                </div>
                <div class="qzone-form-actions">
                    <button type="submit" class="qzone-btn qzone-btn-primary">保存修改</button>
                    <a href="/admin/index" class="qzone-btn qzone-btn-secondary">返回</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>