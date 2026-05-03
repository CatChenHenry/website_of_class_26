<?php require ROOT_DIR . '/views/common/navbar.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新建用户</title>
    <style>
        .container {
            width: 600px;
            margin: 20px auto;
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
        <h1>新建用户</h1>
        <form method="POST" action="/admin/storeUser">
            <?php echo csrfField(); ?>
            <div class="form-item">
                <label>学号：</label>
                <input type="number" name="stu_no" required>
            </div>
            <div class="form-item">
                <label>姓名：</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-item">
                <label>邮箱：</label>
                <input type="email" name="email">
            </div>
            <div class="form-item">
                <label>初始密码：</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-item">
                <label>权限：</label>
                <select name="permissions">
                    <option value="student">学生</option>
                    <option value="teacher">老师</option>
                    <option value="admin">管理员</option>
                </select>
            </div>
            <div class="form-item">
                <label>班级：</label>
                <input type="text" name="stu_class" value="8_26">
            </div>
            <div class="form-item">
                <label>初始分数：</label>
                <input type="number" name="score" value="0" min="0" step="1">
            </div>
            <div class="form-item">
                <label></label>
                <button type="submit" class="btn">提交</button>
                <a href="/admin/index" class="btn" style="background: #6c757d; margin-left: 10px;">返回</a>
            </div>
        </form>
    </div>
</body>

</html>