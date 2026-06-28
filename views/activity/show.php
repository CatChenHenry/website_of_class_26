<?php
/** @var array<string, mixed> $activity */
/** @var array<int, array<string, mixed>> $comments */
require ROOT_DIR . '/views/common/navbar.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>26班网站 - <?php echo sanitizeHtml($activity['name']); ?></title>
    <link rel="stylesheet" href="https://unpkg.com/katex@0.16.21/dist/katex.min.css">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
    <link rel="stylesheet" href="/static/css/qzone-base.css">
    <!-- 全局提示框样式（与管理员控制台一致） -->
    <link rel="stylesheet" href="/static/css/message.css">
    <script src="https://cdn.bootcdn.net/ajax/libs/marked/14.0.0/marked.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://unpkg.com/katex@0.16.21/dist/katex.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/dompurify/3.2.5/purify.min.js"></script>
    <style>
        .activity-header h1 {
            font-size: 28px;
            color: var(--qzone-text-primary, #333);
            margin-bottom: 8px;
        }
        .activity-meta {
            font-size: 14px;
            color: var(--qzone-text-secondary, #888);
        }
        .activity-meta span { margin-right: 16px; }
        .activity-actions { margin-top: 12px; display: flex; gap: 10px; }
        .activity-content {
            line-height: 1.8;
            font-size: 15px;
            color: #333;
            padding: 20px 0;
        }
        .activity-content h1, .activity-content h2, .activity-content h3 { margin-top: 20px; margin-bottom: 10px; }
        .activity-content p { margin: 10px 0; }
        .activity-content pre { background: #f6f8fa; padding: 16px; border-radius: 6px; overflow-x: auto; }
        .activity-content code { font-size: 14px; }
        .activity-content img { max-width: 100%; }
        .activity-content table { border-collapse: collapse; margin: 10px 0; }
        .activity-content th, .activity-content td { border: 1px solid #ddd; padding: 8px 12px; }
        .activity-content th { background: #f6f8fa; }
        .activity-content blockquote { border-left: 4px solid #ddd; padding-left: 16px; color: #666; margin: 10px 0; }
        .empty-content { color: #999; font-size: 15px; padding: 40px 0; }
        .comment-section { margin-top: 40px; border-top: 1px solid var(--qzone-border,#e0e0e0); padding-top: 24px; }
        .comment-section h2 { font-size: 20px; color: #333; margin-bottom: 20px; }
        .comment-count { font-size: 14px; color: #888; font-weight: normal; }
        .comment-form { margin-bottom: 24px; }
        .comment-form textarea {
            width: 100%; height: 80px; padding: 12px; font-size: 14px;
            border: 1px solid var(--qzone-border,#ddd); border-radius: 6px;
            resize: vertical; font-family: inherit; box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .comment-form textarea:focus { outline: none; border-color: var(--qzone-primary,#00a1d6); }
        .comment-form-actions { display: flex; justify-content: flex-end; margin-top: 8px; }
        .comment-login-hint {
            padding: 16px; background: #f8f9fa; border-radius: 6px;
            text-align: center; color: #888; font-size: 14px; margin-bottom: 24px;
        }
        .comment-login-hint a { color: var(--qzone-primary,#007bff); text-decoration: none; }
        .comment-login-hint a:hover { text-decoration: underline; }
        .comment-list { list-style: none; padding: 0; margin: 0; }
        .comment-item { display: flex; gap: 12px; padding: 16px 0; border-bottom: 1px solid #f0f0f0; }
        .comment-item:last-child { border-bottom: none; }
        .comment-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid #eee; }
        .comment-body { flex: 1; min-width: 0; }
        .comment-header { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
        .comment-username { font-size: 14px; font-weight: 600; color: #333; }
        .comment-time { font-size: 12px; color: #999; }
        .comment-delete-btn {
            margin-left: auto; padding: 2px 8px; font-size: 12px;
            color: #dc3545; background: none; border: 1px solid #dc3545;
            border-radius: 3px; cursor: pointer; opacity: 0.7;
        }
        .comment-delete-btn:hover { opacity: 1; background: #fff5f5; }
        .comment-text { font-size: 14px; color: #444; line-height: 1.6; word-break: break-word; }
        .no-comments { text-align: center; color: #aaa; padding: 30px 0; font-size: 14px; }
        @media (max-width: 768px) {
            .activity-header h1 { font-size: 22px; }
            .activity-meta { font-size: 13px; }
            .activity-meta span { display: block; margin-right: 0; margin-bottom: 2px; }
            .activity-content { font-size: 14px; padding: 16px 0; }
            .activity-content pre { padding: 12px; font-size: 13px; }
            .comment-item { gap: 8px; padding: 12px 0; }
            .comment-avatar { width: 32px; height: 32px; }
            .comment-form textarea { height: 60px; font-size: 13px; }
        }
        @media (max-width: 480px) {
            .activity-header h1 { font-size: 18px; }
            .activity-actions { gap: 6px; }
            .comment-avatar { width: 28px; height: 28px; }
            .comment-username { font-size: 13px; }
            .comment-text { font-size: 13px; }
            .comment-delete-btn { padding: 1px 6px; font-size: 11px; }
        }
    </style>
</head>

<body>
    <div class="qzone-page">
        <div class="qzone-card">
            <div class="activity-header">
                <h1><?php echo sanitizeHtml($activity['name']); ?></h1>
                <div class="activity-meta">
                    <span>活动时间：<?php echo htmlspecialchars($activity['activity_time']); ?></span>
                    <span>发布时间：<?php echo htmlspecialchars($activity['created_at']); ?></span>
                </div>
                <?php if (isset($_SESSION['username']) && canManageActivity($_SESSION['permissions'])): ?>
                    <div class="activity-actions">
                        <form method="POST" action="/activity/delete" style="display:inline;" id="actDeleteForm">
                            <button type="button" class="qzone-btn qzone-btn-success qzone-btn-sm" onclick="window.location.href='/activity/edit?id=<?php echo (int) $activity['id']; ?>'">编辑</button>
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="id" value="<?php echo (int) $activity['id']; ?>">
                            <button type="button" class="qzone-btn qzone-btn-danger qzone-btn-sm" onclick="showConfirmModal({message:'确定删除该活动吗？',confirmText:'确定删除',onConfirm:function(){document.getElementById('actDeleteForm').submit();}})">删除</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <div id="activity-content" class="activity-content"
            data-raw-length="<?php echo strlen($activity['content'] ?? ''); ?>"
            data-has-content="<?php echo !empty($activity['content']) ? 'yes' : 'no'; ?>"
        ></div>

        <?php if (!empty($activity['content'])): ?>
        <noscript>
            <div class="activity-content" style="padding:20px 0;">
                <pre style="white-space:pre-wrap;word-break:break-word;"><?php echo htmlspecialchars($activity['content']); ?></pre>
            </div>
        </noscript>
        <?php endif; ?>
        </div><!-- /.qzone-card -->

        <div class="comment-section">
            <h2>评论 <span class="comment-count" id="commentCount">(<?php echo count($comments); ?>)</span></h2>

            <?php if (isset($_SESSION['username'])): ?>
            <div class="comment-form">
                <textarea id="commentInput" placeholder="写下你的评论..." maxlength="1000"></textarea>
                <div class="comment-form-actions">
                    <button class="qzone-btn qzone-btn-primary" id="commentSubmitBtn" onclick="submitComment()">发表评论</button>
                </div>
            </div>
            <?php else: ?>
            <div class="comment-login-hint">
                <a href="/user/login">登录</a> 后可以发表评论
            </div>
            <?php endif; ?>

            <ul class="comment-list" id="commentList">
                <?php foreach ($comments as $comment): ?>
                <li class="comment-item" data-id="<?php echo (int) $comment['id']; ?>">
                    <img src="<?php echo htmlspecialchars(!empty($comment['avatar']) ? $comment['avatar'] : '/static/avatars/default.jpg'); ?>" class="comment-avatar" alt="<?php echo htmlspecialchars($comment['username']); ?>" onerror="this.src='/static/avatars/default.jpg'">
                    <div class="comment-body">
                        <div class="comment-header">
                            <span class="comment-username"><?php echo htmlspecialchars($comment['username']); ?></span>
                            <span class="comment-time"><?php echo htmlspecialchars($comment['created_at']); ?></span>
                            <?php
                                $canDelete = (isset($_SESSION['id']) && $comment['stu_no'] == $_SESSION['id'])
                                    || (isset($_SESSION['permissions']) && canManageActivity($_SESSION['permissions']));
                                if ($canDelete):
                            ?>
                            <button class="comment-delete-btn" onclick="deleteComment(<?php echo (int) $comment['id']; ?>, this)">删除</button>
                            <?php endif; ?>
                        </div>
                        <div class="comment-text"><?php echo nl2br(htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')); ?></div>
                    </div>
                </li>
                <?php endforeach; ?>
                <?php if (empty($comments)): ?>
                <li class="no-comments" id="noCommentsHint">暂无评论，快来抢沙发吧！</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <script>
        const activityId = <?php echo (int) $activity['id']; ?>;
        const isLoggedIn = <?php echo isset($_SESSION['username']) ? 'true' : 'false'; ?>;
        let csrfToken = <?php echo json_encode($_SESSION['csrf_token'] ?? ''); ?>;
        const currentStuNo = <?php echo isset($_SESSION['id']) ? (int) $_SESSION['id'] : 'null'; ?>;
        const isAdmin = <?php echo (isset($_SESSION['permissions']) && canManageActivity($_SESSION['permissions'])) ? 'true' : 'false'; ?>;

        const contentText = <?php echo json_encode($activity['content'] ?? ''); ?>;
        const contentArea = document.getElementById('activity-content');

        if (!contentText || contentText.trim() === '') {
            contentArea.innerHTML = '<div class="empty-content">暂无活动详情</div>';
        } else if (typeof marked === 'undefined' || typeof DOMPurify === 'undefined') {
            contentArea.innerHTML = '<pre style="white-space:pre-wrap;word-break:break-word;">' + escapeHtml(contentText) + '</pre>';
        } else {
            marked.setOptions({ gfm: true, breaks: true, mangle: false });
            try {
                // 第一步：保护 Markdown 代码块（围栏代码块和行内代码），防止数学公式提取错误匹配
                const markdownCodeBlocks = [];
                let raw = contentText;
                // 匹配围栏代码块：```language ... ``` 或 ~~~ ... ~~~
                raw = raw.replace(/```[\s\S]*?```/g, function(match) {
                    var idx = markdownCodeBlocks.length;
                    markdownCodeBlocks.push(match);
                    return '%%%CODE' + idx + '%%%';
                });
                // 匹配行内代码：`...`（不支持嵌套）
                raw = raw.replace(/`[^`]*`/g, function(match) {
                    var idx = markdownCodeBlocks.length;
                    markdownCodeBlocks.push(match);
                    return '%%%CODE' + idx + '%%%';
                });

                // 第二步：从原始 Markdown 中提取数学公式，避免 marked 破坏 & \\ 等字符
                const mathItems = [];
                // 先提取行间公式 $$...$$
                raw = raw.replace(/\$\$[\s\S]*?\$\$/g, function(match) {
                    var idx = mathItems.length;
                    mathItems.push({ formula: match.slice(2, -2), display: true }); // 不 trim 保留原始空白
                    return '%%%MATH' + idx + '%%%';
                });
                // 再提取行内公式 $...$，使用更可靠的正则表达式
                raw = raw.replace(/\$(?!\$)([^$]+?)\$(?!\$)/g, function(match, content) {
                    var idx = mathItems.length;
                    mathItems.push({ formula: content, display: false }); // 不 trim
                    return '%%%MATH' + idx + '%%%';
                });

                // 第三步：恢复 Markdown 代码块，以便 marked 正确解析
                raw = raw.replace(/%%%CODE(\d+)%%%/g, function(match, idx) {
                    var block = markdownCodeBlocks[parseInt(idx)];
                    return block || match;
                });

                // 第四步：marked 解析（数学公式已被占位符保护）
                let html = marked.parse(raw);

                // 第三步：保护代码块，防止 KaTeX 处理
                const codeBlocks = [];
                html = html.replace(/<pre>\s*<code[^>]*>[\s\S]*?<\/code>\s*<\/pre>/g, function(match) {
                    var idx = codeBlocks.length;
                    codeBlocks.push(match);
                    return '%%%CB' + idx + '%%%';
                });

                // 第四步：还原数学公式并用 KaTeX 渲染
                if (typeof katex !== 'undefined') {
                    html = html.replace(/%%%MATH(\d+)%%%/g, function(match, idx) {
                        var item = mathItems[parseInt(idx)];
                        if (!item) return match;
                        try {
                            return katex.renderToString(item.formula, {
                                throwOnError: false,
                                displayMode: item.display,
                                strict: false
                            });
                        } catch(e) {
                            return '<code style="background:#fef0f0;color:#c00;padding:2px 6px;border-radius:3px;">' +
                                   item.formula.replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</code>';
                        }
                    });
                }

                // 第五步：恢复代码块
                html = html.replace(/%%%CB(\d+)%%%/g, function(match, idx) {
                    var block = codeBlocks[parseInt(idx)];
                    return block || match;
                });

                html = DOMPurify.sanitize(html, { ADD_ATTR: ['class'], ADD_TAGS: ['style'] });
                contentArea.innerHTML = html;
                if (window.hljs) { hljs.highlightAll(); }
            } catch (err) {
                contentArea.innerHTML = '<pre style="white-space:pre-wrap;word-break:break-word;">' + escapeHtml(contentText) + '</pre>';
            }
        }

        function submitComment() {
            var input = document.getElementById('commentInput');
            var btn = document.getElementById('commentSubmitBtn');
            var content = input.value.trim();

            if (!content) {
                showToast('请输入评论内容', 'error');
                return;
            }

            btn.disabled = true;
            btn.textContent = '发表中...';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/activity/addComment', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function () {
                btn.disabled = false;
                btn.textContent = '发表评论';

                if (xhr.status === 200) {
                    try {
                        var result = JSON.parse(xhr.responseText);
                        if (result.success) {
                            input.value = '';
                            appendComment(result.comment);
                            showToast('评论成功', 'success');
                        } else {
                            showToast(result.message || '评论失败', 'error');
                        }
                        if (result.csrf_token) {
                            csrfToken = result.csrf_token;
                            var tokenInput = document.querySelector('input[name="csrf_token"]');
                            if (tokenInput) tokenInput.value = result.csrf_token;
                        }
                    } catch (e) {
                        showToast('评论失败', 'error');
                    }
                } else if (xhr.status === 401) {
                    showToast('请先登录', 'error');
                    window.location.href = '/user/login';
                } else {
                    showToast('评论失败', 'error');
                }
            };

            xhr.onerror = function () {
                btn.disabled = false;
                btn.textContent = '发表评论';
                showToast('网络错误', 'error');
            };

            xhr.send('activity_id=' + encodeURIComponent(activityId)
                + '&content=' + encodeURIComponent(content)
                + '&csrf_token=' + encodeURIComponent(csrfToken));
        }

        function appendComment(comment) {
            var noHint = document.getElementById('noCommentsHint');
            if (noHint) noHint.remove();

            var canDelete = isAdmin || (currentStuNo !== null && comment.stu_no == currentStuNo);
            var deleteBtn = canDelete
                ? '<button class="comment-delete-btn" onclick="deleteComment(' + comment.id + ', this)">删除</button>'
                : '';

            var li = document.createElement('li');
            li.className = 'comment-item';
            li.dataset.id = comment.id;
            li.innerHTML = '<img src="' + escapeAttr(comment.avatar || '/static/avatars/default.jpg') + '" class="comment-avatar" alt="' + escapeAttr(comment.username) + '" onerror="this.src=\'/static/avatars/default.jpg\'">'
                + '<div class="comment-body">'
                + '  <div class="comment-header">'
                + '    <span class="comment-username">' + escapeHtml(comment.username) + '</span>'
                + '    <span class="comment-time">' + escapeHtml(comment.created_at) + '</span>'
                + '    ' + deleteBtn
                + '  </div>'
                + '  <div class="comment-text">' + escapeHtml(comment.content).replace(/\n/g, '<br>') + '</div>'
                + '</div>';

            var list = document.getElementById('commentList');
            list.appendChild(li);

            var countEl = document.getElementById('commentCount');
            var count = list.querySelectorAll('.comment-item').length;
            countEl.textContent = '(' + count + ')';
        }

        function deleteComment(commentId, btnEl) {
            showConfirmModal({
                message: '确定删除这条评论吗？',
                confirmText: '删除',
                onConfirm: function () {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '/activity/deleteComment', true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            try {
                                var result = JSON.parse(xhr.responseText);
                                if (result.success) {
                                    var item = btnEl.closest('.comment-item');
                                    if (item) item.remove();
                                    showToast('评论已删除', 'success');

                                    var list = document.getElementById('commentList');
                                    var count = list.querySelectorAll('.comment-item').length;
                                    document.getElementById('commentCount').textContent = '(' + count + ')';
                                    if (count === 0) {
                                        var noHint = document.createElement('li');
                                        noHint.className = 'no-comments';
                                        noHint.id = 'noCommentsHint';
                                        noHint.textContent = '暂无评论，快来抢沙发吧！';
                                        list.appendChild(noHint);
                                    }
                                } else {
                                    showToast('删除失败', 'error');
                                }
                                if (result.csrf_token) {
                                    csrfToken = result.csrf_token;
                                    var tokenInput = document.querySelector('input[name="csrf_token"]');
                                    if (tokenInput) tokenInput.value = result.csrf_token;
                                }
                            } catch (e) {
                                showToast('删除失败', 'error');
                            }
                        } else {
                            showToast('删除失败', 'error');
                        }
                    };

                    xhr.onerror = function () {
                        showToast('网络错误', 'error');
                    };

                    xhr.send('comment_id=' + encodeURIComponent(commentId)
                        + '&csrf_token=' + encodeURIComponent(csrfToken));
                }
            });
        }

        function escapeHtml(text) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }

        function escapeAttr(text) {
            return text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
    </script>
    <!-- 全局提示框脚本（与管理员控制台一致） -->
    <script src="/static/js/message.js"></script>
</body>

</html>
