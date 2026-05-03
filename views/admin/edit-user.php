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
    <style>
        .container {
            width: 600px;
            margin: 20px auto;
            margin-top: 60px;
        }

        .form-item {
            margin: 15px 0;
        }

        label {
            display: inline-block;
            width: 100px;
        }

        input,
        select {
            padding: 8px;
            width: 300px;
        }

        .btn {
            padding: 8px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .container {
                width: 100%;
                margin-top: 40px;
                padding: 0 16px;
                box-sizing: border-box;
            }

            label {
                display: block;
                width: auto;
                margin-bottom: 4px;
            }

            input, select {
                width: 100%;
                max-width: 300px;
                box-sizing: border-box;
            }
        }

        @media (max-width: 480px) {
            .container {
                margin-top: 30px;
                padding: 0 12px;
            }

            input, select {
                max-width: 100%;
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>编辑用户：<?php echo htmlspecialchars($editUser['name']); ?></h1>
        <form method="POST" action="/admin/updateUser">
            <?php echo csrfField(); ?>
            <div class="form-item">
                <label>学号：</label>
                <input type="hidden" name="stu_no" value="<?php echo htmlspecialchars($editUser['stu_no']); ?>">
                <span><?php echo htmlspecialchars($editUser['stu_no']); ?></span>
            </div>
            <div class="form-item">
                <label>姓名：</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($editUser['name']); ?>" required>
            </div>
            <div class="form-item">
                <label>邮箱：</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($editUser['email'] ?? ''); ?>">
            </div>
            <div class="form-item">
                <label>权限：</label>
                <select name="permissions">
                    <option value="student" <?php echo $editUser['permissions'] == 'student' ? 'selected' : ''; ?>>学生</option>
                    <option value="teacher" <?php echo $editUser['permissions'] == 'teacher' ? 'selected' : ''; ?>>老师</option>
                </select>
                <?php if ($editUser['permissions'] === 'teacher'): ?>
                <span style="color:#999;font-size:13px;">（可将老师降级为学生）</span>
                <?php endif; ?>
            </div>
            <div class="form-item">
                <label>班级：</label>
                <input type="text" name="stu_class" value="<?php echo htmlspecialchars($editUser['stu_class']); ?>">
            </div>
            <div class="form-item">
                <label>分数：</label>
                <input type="number" name="score" value="<?php echo htmlspecialchars($editUser['score'] ?? 0); ?>" min="0" step="1">
            </div>
            <div class="form-item">
                <label></label>
                <button type="submit" class="btn">保存修改</button>
                <a href="/admin/index" class="btn" style="background: #6c757d; margin-left: 10px;">返回</a>
            </div>
        </form>
    </div>
</body>

</html>