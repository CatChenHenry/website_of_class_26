<?php
/** @var string $avatar */
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>26班网站 - 修改头像</title>
    <link rel="stylesheet" href="/static/css/message.css">
    <link rel="stylesheet" href="/static/css/qzone-base.css">
</head>

<body>
    <?php require ROOT_DIR . '/views/common/navbar.php'; ?>
    <div class="qzone-page">
        <div class="qzone-card" style="max-width: 500px; margin: 0 auto; text-align: center;">
            <div class="qzone-page-header" style="justify-content: center;">
                <h1 style="margin:0;">修改头像</h1>
            </div>
            <img id="avatarPreview" class="qzone-avatar-preview" src="<?php echo htmlspecialchars($avatar); ?>" alt="当前头像" onerror="this.src='/static/avatars/default.jpg'">
            <form action="/user/doAvatar" method="post" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <div class="qzone-form-item">
                    <input type="file" id="avatarFile" name="avatar" accept="image/jpeg,image/png" class="qzone-form-input" style="padding:10px;cursor:pointer;" required>
                </div>
                <button type="submit" class="qzone-btn qzone-btn-primary">上传头像</button>
                <p style="color:var(--qzone-text-light,#999);font-size:14px;margin-top:12px;">支持JPG/PNG格式，大小不超过2MB</p>
            </form>
        </div>
    </div>

    <script>
        const avatarFile = document.getElementById('avatarFile');
        const avatarPreview = document.getElementById('avatarPreview');

        avatarFile.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    showToast('图片大小不能超过2MB！', 'error');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    avatarPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
    <!-- 全局提示框脚本（与管理员控制台一致） -->
    <script src="/static/js/message.js"></script>
</body>

</html>