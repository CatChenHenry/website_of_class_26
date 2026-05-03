<?php require ROOT_DIR . '/views/common/navbar.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>批量导入用户</title>
    <style>
        .container {
            width: 1100px;
            margin: 20px auto;
            margin-top: 60px;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .info-box {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-box h3 {
            margin-top: 0;
            color: #333;
            font-size: 16px;
        }

        .info-box table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .info-box th,
        .info-box td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: center;
            font-size: 14px;
        }

        .info-box th {
            background: #e9ecef;
            font-weight: 600;
        }

        .info-box p {
            font-size: 14px;
            color: #555;
            margin: 8px 0;
        }

        .info-box ul {
            font-size: 14px;
            color: #555;
            padding-left: 20px;
        }

        .info-box ul li {
            margin: 4px 0;
        }

        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 6px;
            padding: 40px 20px;
            text-align: center;
            margin: 20px 0;
            transition: border-color 0.3s;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: #007bff;
        }

        .upload-area.dragover {
            border-color: #007bff;
            background: #f0f7ff;
        }

        .upload-area p {
            font-size: 16px;
            color: #666;
            margin: 8px 0;
        }

        .upload-area .hint {
            font-size: 13px;
            color: #999;
        }

        .upload-area input[type="file"] {
            display: none;
        }

        .file-name {
            margin-top: 10px;
            font-size: 14px;
            color: #28a745;
            font-weight: 500;
        }

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0069d9;
        }

        .btn-primary:disabled {
            background: #a0c4ff;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-download {
            background: #28a745;
            color: white;
        }

        .btn-download:hover {
            background: #218838;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }





        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.45);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-box {
            background: #fff;
            border-radius: 12px;
            padding: 36px 48px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            max-width: 480px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e0e0e0;
            border-top-color: #007bff;
            border-radius: 50%;
            margin: 0 auto 16px;
            animation: spin 0.8s linear infinite;
        }

        .loading-spinner.hidden {
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 15px;
            color: #333;
            margin-top: 4px;
        }

        .loading-subtext {
            font-size: 13px;
            color: #999;
            margin-top: 6px;
        }

        .result-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            margin: 0 auto 16px;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
        }

        .result-icon.success {
            display: flex;
            background: #28a745;
            color: #fff;
        }

        .result-icon.error {
            display: flex;
            background: #dc3545;
            color: #fff;
        }

        .result-icon.partial {
            display: flex;
            background: #e67e22;
            color: #fff;
        }

        .modal-result-detail {
            text-align: left;
            margin-top: 16px;
            font-size: 14px;
            color: #555;
            display: none;
        }

        .modal-result-detail .detail-row {
            margin: 6px 0;
        }

        .modal-result-detail .detail-row.success-count {
            color: #fff;
            font-weight: 500;
        }

        .modal-result-detail .detail-row.skip-count {
            color: #e67e22;
            font-weight: 500;
        }

        .modal-result-detail .error-list {
            font-size: 13px;
            color: #dc3545;
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }

        .modal-result-detail .error-list p {
            margin: 3px 0;
        }

        .modal-close-btn {
            margin-top: 20px;
            padding: 8px 32px;
            border: none;
            border-radius: 6px;
            background: #007bff;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
            display: none;
        }

        .modal-close-btn:hover {
            background: #0069d9;
        }

        .modal-close-btn.visible {
            display: inline-block;
        }

        @media (max-width: 900px) {
            .container { width: 100%; padding: 0 16px; box-sizing: border-box; }
        }

        @media (max-width: 480px) {
            .container { padding: 0 12px; }

            h1 { font-size: 20px; }

            .info-box { padding: 14px; }

            .info-box table {
                font-size: 12px;
            }

            .info-box th, .info-box td {
                padding: 6px 8px;
            }

            .upload-area {
                padding: 24px 12px;
            }

            .upload-area p { font-size: 14px; }

            .actions {
                flex-wrap: wrap;
            }

            .btn {
                padding: 6px 14px;
                font-size: 13px;
            }

            .loading-box {
                padding: 24px 20px;
                width: 92%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>批量导入用户</h1>

        <div>
            <div class="info-box">
                    <h3>文件格式要求</h3>
                    <p>请按以下格式填写表格（第一行为表头，从第二行开始为数据）：</p>
                    <table>
                        <tr>
                            <th>学号</th>
                            <th>姓名</th>
                            <th>邮箱</th>
                            <th>密码</th>
                            <th>权限</th>
                            <th>班级</th>
                            <th>分数</th>
                        </tr>
                        <tr>
                            <td>2024001</td>
                            <td>张三</td>
                            <td>zhangsan@example.com</td>
                            <td>123456</td>
                            <td>student</td>
                            <td>8_26</td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <td>2024002</td>
                            <td>李四</td>
                            <td></td>
                            <td>abc123</td>
                            <td>student</td>
                            <td>8_26</td>
                            <td>0</td>
                        </tr>
                    </table>
                    <p><strong>说明：</strong></p>
                    <ul>
                        <li><strong>学号、姓名、密码</strong>为必填项</li>
                        <li>邮箱可为空</li>
                        <li>权限只能填 <code>student</code>、<code>teacher</code> 或 <code>admin</code>，默认为 <code>student</code></li>
                        <li>班级默认为 <code>8_26</code>，分数默认为 <code>0</code></li>
                        <li>密码长度不能少于6位</li>
                        <li>学号不能与已有用户重复</li>
                    </ul>
                    <div style="margin-top: 12px;">
                        <a href="/admin/downloadTemplate?format=xlsx" class="btn btn-download">下载 Excel 模板</a>
                    </div>
                </div>

                <form method="POST" action="/admin/doImportUsers" enctype="multipart/form-data" id="importForm">
                    <?php echo csrfField(); ?>
                    <div class="upload-area" id="uploadArea">
                        <p>点击选择或拖拽文件到此处</p>
                        <p class="hint">支持 .xls 和 .xlsx 格式</p>
                        <input type="file" name="excel_file" id="excelFile" accept=".xls,.xlsx">
                        <div class="file-name" id="fileName"></div>
                    </div>
                    <div class="actions">
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>开始导入</button>
                        <a href="/admin/index" class="btn btn-secondary">返回</a>
                    </div>
                </form>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-box">
            <div class="loading-spinner" id="loadingSpinner"></div>
            <div class="result-icon" id="resultIcon"></div>
            <div class="loading-text" id="loadingText">正在导入用户，请稍候...</div>
            <div class="loading-subtext" id="loadingSubtext">正在上传文件</div>
            <div class="modal-result-detail" id="modalResult"></div>
            <button class="modal-close-btn" id="modalCloseBtn" onclick="closeOverlay()">确 定</button>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('excelFile');
        const fileName = document.getElementById('fileName');
        const submitBtn = document.getElementById('submitBtn');
        const importForm = document.getElementById('importForm');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const loadingText = document.getElementById('loadingText');
        const loadingSubtext = document.getElementById('loadingSubtext');
        const resultIcon = document.getElementById('resultIcon');
        const modalResult = document.getElementById('modalResult');
        const modalCloseBtn = document.getElementById('modalCloseBtn');

        uploadArea.addEventListener('click', function () {
            fileInput.click();
        });

        uploadArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function () {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function (e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                showFileName(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', function () {
            if (fileInput.files.length > 0) {
                showFileName(fileInput.files[0]);
            }
        });

        function showFileName(file) {
            fileName.textContent = '已选择：' + file.name;
            submitBtn.disabled = false;
        }

        importForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var formData = new FormData(importForm);

            loadingSpinner.classList.remove('hidden');
            resultIcon.className = 'result-icon';
            resultIcon.textContent = '';
            loadingText.textContent = '正在导入用户，请稍候...';
            loadingSubtext.textContent = '正在上传文件';
            loadingSubtext.style.display = '';
            modalResult.style.display = 'none';
            modalResult.innerHTML = '';
            modalCloseBtn.classList.remove('visible');
            loadingOverlay.classList.add('active');
            submitBtn.disabled = true;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', importForm.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    var percent = Math.round((e.loaded / e.total) * 100);
                    if (percent >= 100) {
                        loadingSubtext.textContent = '文件已上传，正在处理数据...';
                    } else {
                        loadingSubtext.textContent = '正在上传文件 (' + percent + '%)';
                    }
                }
            });

            xhr.onload = function () {
                submitBtn.disabled = false;

                if (xhr.status === 200) {
                    try {
                        var result = JSON.parse(xhr.responseText);
                        showResultInModal(result);
                    } catch (ex) {
                        hideOverlay();
                        showToast('导入完成', 'success');
                    }
                } else {
                    hideOverlay();
                    showToast('服务器错误，请重试', 'error');
                }
            };

            xhr.onerror = function () {
                hideOverlay();
                submitBtn.disabled = false;
                showToast('网络错误，请重试', 'error');
            };

            xhr.send(formData);
        });

        function showResultInModal(result) {
            loadingSpinner.classList.add('hidden');
            loadingSubtext.style.display = 'none';

            if (result.success && result.skipped === 0) {
                resultIcon.className = 'result-icon success';
                resultIcon.textContent = '✓';
                loadingText.textContent = '导入完成';
            } else if (result.success && result.skipped > 0) {
                resultIcon.className = 'result-icon partial';
                resultIcon.textContent = '!';
                loadingText.textContent = '导入完成（部分跳过）';
            } else {
                resultIcon.className = 'result-icon error';
                resultIcon.textContent = '✗';
                loadingText.textContent = '导入失败';
            }

            var html = '';
            html += '<div class="detail-row">总行数：' + result.total + '</div>';
            html += '<div class="detail-row success-count">成功：' + result.imported + ' 条</div>';

            if (result.skipped > 0) {
                html += '<div class="detail-row skip-count">跳过：' + result.skipped + ' 条</div>';
            }

            if (result.errors && result.errors.length > 0) {
                html += '<div class="error-list">';
                for (var i = 0; i < result.errors.length; i++) {
                    html += '<p>' + escapeHtml(result.errors[i]) + '</p>';
                }
                html += '</div>';
            }

            modalResult.innerHTML = html;
            modalResult.style.display = 'block';
            modalCloseBtn.classList.add('visible');
        }

        function closeOverlay() {
            loadingOverlay.classList.remove('active');
        }

        function hideOverlay() {
            loadingOverlay.classList.remove('active');
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }
    </script>
</body>

</html>
