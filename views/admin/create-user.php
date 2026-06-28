<?php require ROOT_DIR . '/views/common/navbar.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新建用户</title>
    <link rel="stylesheet" href="/static/css/qzone-base.css">
</head>

<body>
    <div class="qzone-page">
        <div class="qzone-card" style="max-width: 640px; margin: 0 auto;">
            <div class="qzone-page-header">
                <h1>新建用户</h1>
            </div>
            <form method="POST" action="/admin/storeUser">
                <?php echo csrfField(); ?>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">学号：</label>
                    <input type="number" name="stu_no" class="qzone-form-input" style="max-width:300px;" required>
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">姓名：</label>
                    <input type="text" name="name" class="qzone-form-input" style="max-width:300px;" required>
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">邮箱：</label>
                    <input type="email" name="email" class="qzone-form-input" style="max-width:300px;">
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">初始密码：</label>
                    <input type="password" name="password" class="qzone-form-input" style="max-width:300px;" required>
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">权限：</label>
                    <select name="permissions" class="qzone-form-select" style="max-width:300px;">
                        <option value="student">学生</option>
                        <option value="teacher">老师</option>
                        <option value="admin">管理员</option>
                    </select>
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">班级：</label>
                    <input type="text" name="stu_class" class="qzone-form-input" style="max-width:300px;" value="8_26">
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-inline-label">初始分数：</label>
                    <input type="number" name="score" class="qzone-form-input" style="max-width:300px;" value="0" min="0" step="1">
                </div>
                <div class="qzone-form-actions">
                    <button type="submit" class="qzone-btn qzone-btn-primary">提交</button>
                    <a href="/admin/index" class="qzone-btn qzone-btn-secondary">返回</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>