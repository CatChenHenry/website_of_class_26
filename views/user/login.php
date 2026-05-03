<?php
/** @var string $title */
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="/static/css/message.css">
    <style>
        body {
            max-width: 600px;
            margin: auto;
            font-family: "Microsoft Yahei";
            background: #f5f5f5;
        }

        .form-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px #eee;
        }

        h2 {
            color: #2c3e50;
            text-align: center;
        }

        .input-item {
            margin: 15px 0;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #2980b9;
        }

        @media (max-width: 768px) {
            .form-box {
                margin: 20px 16px;
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .form-box {
                margin: 16px 12px;
                padding: 16px;
            }

            h2 {
                font-size: 18px;
            }

            input {
                font-size: 16px;
                padding: 8px;
            }

            button {
                font-size: 15px;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <?php require ROOT_DIR . '/views/common/navbar.php'; ?>
    <div class="page-content">
        <div class="form-box">
            <h2>26班网站 - 登录</h2>
            <form method="POST" action="/user/doLogin">
                <?php echo csrfField(); ?>
                <div class="input-item">
                    <label>用户名：</label>
                    <input type="text" name="username" placeholder="请输入用户名" required>
                </div>
                <div class="input-item">
                    <label>密码：</label>
                    <input type="password" name="password" placeholder="请输入密码" required>
                </div>
                <button type="submit">登录</button>
            </form>
        </div>
    </div>

</body>

</html>