<?php
/** @var string $avatar */
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>26班网站 - 修改头像</title>
    <style>
        .avatar-container {
            width: 800px;
            margin: 50px auto;
            text-align: center;
        }

        .avatar-preview {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            border: 2px solid #ccc;
            object-fit: cover;
            margin: 20px auto;
            display: block;
        }

        .upload-form {
            margin-top: 30px;
        }

        .file-input {
            padding: 10px;
            margin-bottom: 20px;
        }

        .submit-btn {
            padding: 10px 30px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .hint {
            color: #666;
            font-size: 14px;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .avatar-container {
                width: 100%;
                padding: 0 16px;
                box-sizing: border-box;
            }

            .avatar-preview {
                width: 150px;
                height: 150px;
            }
        }

        @media (max-width: 480px) {
            .avatar-container {
                margin: 30px auto;
                padding: 0 12px;
            }

            .avatar-preview {
                width: 120px;
                height: 120px;
            }

            .submit-btn {
                padding: 8px 20px;
            }
        }
    </style>
</head>

<body>
    <?php require ROOT_DIR . '/views/common/navbar.php'; ?>
    <div class="avatar-container">
        <h1>修改头像</h1>
        <img id="avatarPreview" class="avatar-preview" src="<?php echo htmlspecialchars($avatar); ?>" alt="当前头像" onerror="this.src='/static/avatars/default.jpg'">
        <form class="upload-form" action="/user/doAvatar" method="post" enctype="multipart/form-data">
            <?php echo csrfField(); ?>
            <input type="file" id="avatarFile" name="avatar" class="file-input" accept="image/jpeg,image/png" required>
            <br>
            <button type="submit" class="submit-btn">上传头像</button>
            <p class="hint">支持JPG/PNG格式，大小不超过2MB</p>
        </form>
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
</body>

</html>