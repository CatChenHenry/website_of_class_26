<?php require ROOT_DIR . '/views/common/navbar.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>发布视频</title>
    <link rel="stylesheet" href="/static/css/qzone-base.css">
    <style>
        .upload-tabs { display: flex; gap: 0; margin-bottom: 20px; border-radius: 8px; overflow: hidden; border: 1px solid var(--qzone-border, #ddd); }
        .upload-tab {
            flex: 1; padding: 10px 16px; text-align: center; cursor: pointer;
            background: #f5f5f5; color: #666; font-size: 14px; font-weight: 500;
            border: none; font-family: inherit; transition: all 0.2s;
        }
        .upload-tab + .upload-tab { border-left: 1px solid var(--qzone-border, #ddd); }
        .upload-tab.active { background: var(--qzone-primary, #00a1d6); color: #fff; }
        .upload-tab:hover:not(.active) { background: #e8e8e8; }
        .upload-panel { display: none; }
        .upload-panel.active { display: block; }
        .upload-drop-zone {
            border: 2px dashed #ccc; border-radius: 10px;
            padding: 40px 20px; text-align: center; cursor: pointer;
            transition: border-color 0.3s, background 0.3s;
        }
        .upload-drop-zone:hover, .upload-drop-zone.dragover {
            border-color: var(--qzone-primary, #00a1d6);
            background: #f0f7ff;
        }
        .upload-drop-zone .upload-icon { font-size: 48px; color: #aaa; margin-bottom: 10px; }
        .upload-drop-zone p { font-size: 15px; color: #666; margin: 6px 0; }
        .upload-drop-zone .upload-hint { font-size: 12px; color: #999; }
        .upload-drop-zone input[type="file"] { display: none; }
        .file-selected {
            margin-top: 16px; padding: 12px 16px; background: #e8f5e9;
            border-radius: 8px; font-size: 14px; color: #2e7d32;
            display: none; align-items: center; gap: 10px;
        }
        .file-selected.show { display: flex; }
        .file-selected .file-name { flex: 1; word-break: break-all; }
        .file-selected .file-remove {
            background: #ef5350; color: #fff; border: none; border-radius: 4px;
            padding: 4px 10px; cursor: pointer; font-size: 13px; white-space: nowrap;
        }
        .upload-progress { display: none; margin-top: 12px; }
        .upload-progress.show { display: block; }
        .upload-progress-bar {
            width: 100%; height: 6px; background: #e0e0e0; border-radius: 3px; overflow: hidden;
        }
        .upload-progress-fill {
            height: 100%; background: var(--qzone-primary, #00a1d6);
            border-radius: 3px; transition: width 0.3s; width: 0;
        }
        .upload-progress-text { font-size: 12px; color: #666; margin-top: 4px; text-align: center; }
    </style>
</head>
<body>
<div class="qzone-page">
    <div class="qzone-card" style="max-width: 700px; margin: 0 auto;">
        <div class="qzone-page-header"><h1>发布视频</h1></div>
        <form method="POST" action="/video/store" enctype="multipart/form-data" id="videoForm">
            <?= csrfField() ?>
            <div class="qzone-form-item">
                <label class="qzone-form-label">集数</label>
                <input type="number" name="episode" class="qzone-form-input" value="1" min="1" required>
            </div>
            <div class="qzone-form-item">
                <label class="qzone-form-label">标题</label>
                <input type="text" name="title" class="qzone-form-input" placeholder="如：第1集 - 开学典礼" required>
            </div>

            <!-- 上传方式切换 -->
            <div class="qzone-form-item">
                <label class="qzone-form-label">视频来源</label>
                <div class="upload-tabs">
                    <button type="button" class="upload-tab active" data-tab="url">🔗 视频链接</button>
                    <button type="button" class="upload-tab" data-tab="file">📁 本地上传</button>
                </div>

                <!-- URL 输入面板 -->
                <div class="upload-panel active" id="panel-url">
                    <input type="url" name="video_url" class="qzone-form-input" id="videoUrlInput"
                           placeholder="B站/腾讯视频 嵌入链接 或 MP4直链">
                    <p style="font-size:12px;color:#999;margin-top:4px;">
                        支持 B站 iframe 嵌入代码、视频直链(.mp4)、腾讯视频分享链接
                    </p>
                </div>

                <!-- 本地上传面板 -->
                <div class="upload-panel" id="panel-file">
                    <div class="upload-drop-zone" id="dropZone">
                        <div class="upload-icon">📤</div>
                        <p>点击选择视频文件 或拖拽到此处</p>
                        <p class="upload-hint">支持 mp4、webm、ogg、mov、avi、mkv、flv、wmv，最大 500MB</p>
                        <input type="file" name="video_file" id="videoFileInput" accept="video/*">
                    </div>
                    <div class="file-selected" id="fileSelected">
                        <span>📹</span>
                        <span class="file-name" id="fileName"></span>
                        <button type="button" class="file-remove" id="fileRemove">移除</button>
                    </div>
                    <div class="upload-progress" id="uploadProgress">
                        <div class="upload-progress-bar">
                            <div class="upload-progress-fill" id="progressFill"></div>
                        </div>
                        <div class="upload-progress-text" id="progressText"></div>
                    </div>
                </div>
            </div>

            <div class="qzone-form-item">
                <label class="qzone-form-label">封面图链接（可选）</label>
                <input type="url" name="thumbnail" class="qzone-form-input" placeholder="https://...">
            </div>
            <div class="qzone-form-item">
                <label class="qzone-form-label">简介（可选）</label>
                <textarea name="description" class="qzone-form-textarea" rows="4" placeholder="视频简介..."></textarea>
            </div>
            <div class="qzone-form-actions">
                <button type="submit" class="qzone-btn qzone-btn-primary">发布</button>
                <a href="/video/index" class="qzone-btn qzone-btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var tabs = document.querySelectorAll('.upload-tab');
    var panelUrl = document.getElementById('panel-url');
    var panelFile = document.getElementById('panel-file');
    var urlInput = document.getElementById('videoUrlInput');
    var fileInput = document.getElementById('videoFileInput');
    var dropZone = document.getElementById('dropZone');
    var fileSelected = document.getElementById('fileSelected');
    var fileName = document.getElementById('fileName');
    var fileRemove = document.getElementById('fileRemove');

    // Tab 切换
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            this.classList.add('active');
            var mode = this.dataset.tab;
            if (mode === 'url') {
                panelUrl.classList.add('active');
                panelFile.classList.remove('active');
            } else {
                panelUrl.classList.remove('active');
                panelFile.classList.add('active');
            }
        });
    });

    // 点击上传区域触发文件选择
    dropZone.addEventListener('click', function(e) {
        if (e.target !== fileRemove) fileInput.click();
    });

    // 拖拽
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });
    dropZone.addEventListener('dragleave', function() {
        dropZone.classList.remove('dragover');
    });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            showFileInfo(files[0]);
        }
    });

    // 文件选择
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            showFileInfo(this.files[0]);
        }
    });

    function showFileInfo(file) {
        var sizeMB = (file.size / 1024 / 1024).toFixed(1);
        if (file.size > 500 * 1024 * 1024) {
            alert('文件大小超过 500MB 限制');
            fileInput.value = '';
            return;
        }
        fileName.textContent = file.name + ' (' + sizeMB + ' MB)';
        fileSelected.classList.add('show');
    }

    fileRemove.addEventListener('click', function(e) {
        e.stopPropagation();
        fileInput.value = '';
        fileSelected.classList.remove('show');
    });

    // 提交前验证
    document.getElementById('videoForm').addEventListener('submit', function(e) {
        var isFileMode = panelFile.classList.contains('active');
        if (isFileMode) {
            if (!fileInput.files.length) {
                e.preventDefault();
                alert('请选择要上传的视频文件');
            }
        } else {
            if (!urlInput.value.trim()) {
                e.preventDefault();
                alert('请输入视频链接');
            }
        }
    });
})();
</script>
</body>
</html>
