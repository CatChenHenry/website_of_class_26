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
    <link rel="stylesheet" href="/static/css/qzone-base.css">
</head>

<body>
    <?php require ROOT_DIR . '/views/common/navbar.php'; ?>
    <div class="qzone-page">
        <div class="qzone-card" style="max-width: 480px; margin: 0 auto;">
            <div class="qzone-page-header" style="justify-content: center;">
                <h2 style="margin:0;">更新密码</h2>
            </div>
            <form method="POST" action="/user/doChangepwd">
                <?php echo csrfField(); ?>
                <div class="qzone-form-item">
                    <label class="qzone-form-label">旧密码：</label>
                    <input type="password" name="oldpwd" class="qzone-form-input" placeholder="请输入原密码" required>
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-label">新密码：</label>
                    <input type="password" name="newpwd" class="qzone-form-input" placeholder="请输入新密码" required>
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-label">重复新密码：</label>
                    <input type="password" name="repeatpwd" class="qzone-form-input" placeholder="请重复新密码" required>
                </div>
                <button type="submit" class="qzone-btn qzone-btn-primary" style="width:100%;">提交</button>
            </form>
        </div>
    </div>
</body>

</html>