<?php
/** @var array<string, mixed> $activity */
require ROOT_DIR . '/views/common/navbar.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑活动</title>
    <link rel="stylesheet" href="https://unpkg.com/katex@0.16.21/dist/katex.min.css">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
    <link rel="stylesheet" href="/static/css/qzone-base.css">
    <script src="https://cdn.bootcdn.net/ajax/libs/marked/14.0.0/marked.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://unpkg.com/katex@0.16.21/dist/katex.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/dompurify/3.2.5/purify.min.js"></script>
    <style>
        .editor-area {
            display: flex;
            gap: 16px;
            margin-top: 8px;
        }
        .editor-pane, .preview-pane {
            flex: 1;
            min-width: 0;
            overflow: hidden;
        }
        .editor-pane label,
        .preview-pane label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #555;
            margin-bottom: 6px;
        }
        .editor-textarea {
            width: 100%;
            height: 400px;
            padding: 12px;
            font-size: 14px;
            font-family: 'Consolas', 'Monaco', monospace;
            border: 1px solid var(--qzone-border, #ddd);
            border-radius: var(--qzone-radius, 12px);
            resize: vertical;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .editor-textarea:focus {
            outline: none;
            border-color: var(--qzone-primary, #00a1d6);
        }
        .preview-box {
            width: 100%;
            min-height: 400px;
            max-height: 400px;
            padding: 12px;
            border: 1px solid var(--qzone-border, #ddd);
            border-radius: var(--qzone-radius, 12px);
            background: #fafafa;
            overflow-y: auto;
            line-height: 1.8;
            font-size: 15px;
            box-sizing: border-box;
            scrollbar-gutter: stable;
        }

        .preview-box h1, .preview-box h2, .preview-box h3 { margin-top: 16px; margin-bottom: 8px; }
        .preview-box p { margin: 8px 0; }
        .preview-box pre { background: #f6f8fa; padding: 12px; border-radius: 4px; overflow-x: auto; }
        .preview-box code { font-size: 14px; }
        .preview-box img { max-width: 100%; height: auto; }
        .preview-box table { border-collapse: collapse; margin: 8px 0; width: 100%; table-layout: fixed; }
        .preview-box pre { white-space: pre-wrap; word-break: break-all; }
        .preview-box th, .preview-box td { border: 1px solid #ddd; padding: 6px 10px; }
        .preview-box th { background: #f6f8fa; }
        .preview-box blockquote { border-left: 4px solid #ddd; padding-left: 14px; color: #666; margin: 8px 0; }
        @media (max-width: 768px) {
            .editor-area { flex-direction: column; }
            .editor-textarea { height: 250px; }
            .preview-box { min-height: 200px; max-height: 200px; }
        }
        @media (max-width: 480px) {
            .editor-textarea { height: 200px; font-size: 13px; }
            .preview-box { min-height: 150px; max-height: 150px; font-size: 14px; }
        }
    </style>
</head>

<body>
    <div class="qzone-page">
        <div class="qzone-card">
            <div class="qzone-page-header">
                <h1>编辑活动</h1>
            </div>
            <form method="POST" action="/activity/update" id="activityForm">
                <?php echo csrfField(); ?>
                <input type="hidden" name="id" value="<?php echo (int) $activity['id']; ?>">
                <div class="qzone-form-item">
                    <label class="qzone-form-label">活动名称</label>
                    <input type="text" name="name" id="actName" class="qzone-form-input" required maxlength="255" value="<?php echo htmlspecialchars($activity['name']); ?>">
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-label">活动时间</label>
                    <input type="datetime-local" name="activity_time" id="actTime" class="qzone-form-input" required value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($activity['activity_time']))); ?>">
                </div>
                <div class="qzone-form-item">
                    <div class="editor-area">
                        <div class="editor-pane">
                            <label>活动内容（Markdown）</label>
                            <textarea name="content" id="contentEditor" class="editor-textarea" placeholder="支持 Markdown 语法"><?php echo htmlspecialchars($activity['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="preview-pane">
                            <label>实时预览</label>
                            <div class="preview-box" id="previewBox"></div>
                        </div>
                    </div>
                </div>
                <div class="qzone-form-actions">
                    <button type="submit" class="qzone-btn qzone-btn-primary">保存</button>
                    <a href="javascript:history.back()" class="qzone-btn qzone-btn-secondary">返回</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const editor = document.getElementById('contentEditor');
        const preview = document.getElementById('previewBox');

        marked.setOptions({ gfm: true, breaks: true, mangle: false });

        function renderPreview() {
            const text = editor.value;
            if (!text.trim()) {
                preview.innerHTML = '<span style="color:#999;">预览区域</span>';
                return;
            }
            try {
                // 第一步：保护 Markdown 代码块（围栏代码块和行内代码），防止数学公式提取错误匹配
                const markdownCodeBlocks = [];
                let raw = text;
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
                const mathItems = []; // { formula, display }
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

                // 第四步：还原数学公式并用 KaTeX 渲染（公式保持原始文本，未被 marked 转义）
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
                        // KaTeX 渲染失败时显示原始公式文本
                        return '<code style="background:#fef0f0;color:#c00;padding:2px 6px;border-radius:3px;">' +
                               item.formula.replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</code>';
                    }
                });

                // 第五步：恢复代码块
                html = html.replace(/%%%CB(\d+)%%%/g, function(match, idx) {
                    var block = codeBlocks[parseInt(idx)];
                    return block || match;
                });

                html = DOMPurify.sanitize(html, { ADD_ATTR: ['class'] });
                preview.innerHTML = html;

                if (window.hljs) {
                    preview.querySelectorAll('pre code').forEach(function(block) {
                        hljs.highlightElement(block);
                    });
                }
            } catch (err) {
                preview.innerHTML = '<span style="color:red;">渲染出错：' + err.message + '</span>';
            }
        }

        editor.addEventListener('input', renderPreview);
        renderPreview();
    </script>
</body>

</html>
