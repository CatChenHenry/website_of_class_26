<?php
/** @var array<string, mixed> $user1 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>26班网站 - <?php echo htmlspecialchars($user1['name']); ?>的个人主页</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        #homepage-area {
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 20px;
            line-height: 1.8;
        }

        @media (max-width: 768px) {
            #homepage-area {
                padding: 0 16px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            #homepage-area {
                padding: 0 12px;
                font-size: 13px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/katex@0.16.21/dist/katex.min.css">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
    <script src="https://cdn.bootcdn.net/ajax/libs/marked/14.0.0/marked.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://unpkg.com/katex@0.16.21/dist/katex.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/dompurify/3.2.5/purify.min.js"></script>
</head>

<body>
    <?php
    require ROOT_DIR . '/views/common/navbar.php';
    ?>
    <div id="homepage-area" style="margin-top: 60px;"></div>
    <script>
        const homepageText = <?php echo json_encode($user1['homepage']); ?>;
        const previewArea = document.getElementById('homepage-area');
        if (!homepageText || !homepageText.trim()) {
            previewArea.innerHTML = '<div style="text-align:center;color:#aaa;padding:80px 20px;font-size:16px;">Oh，此人似乎没有设置个人主页</div>';
        } else {
            marked.setOptions({
                gfm: true,
                breaks: true,
                mangle: false
            });
            try {
                previewArea.style.display = 'block';
                let content = homepageText.replace(/\\\\/g, '\\');
                let html = marked.parse(content);
                previewArea.innerHTML = DOMPurify.sanitize(html, { ADD_ATTR: ['class'] });
                if (window.hljs) {
                    hljs.highlightAll();
                }
                previewArea.innerHTML = DOMPurify.sanitize(previewArea.innerHTML.replace(
                    /\$\$([\s\S]*?)\$\$/g,
                    (match, formula) => {
                        const cleanFormula = formula.replace(/<br\s*\/?>/g, '').trim();
                        return katex.renderToString(cleanFormula, {
                            throwOnError: false,
                            displayMode: true,
                            strict: 'ignore'
                        });
                    }
                ), { ADD_ATTR: ['class'] });
                previewArea.innerHTML = DOMPurify.sanitize(previewArea.innerHTML.replace(
                    /\$([^\$]*?)\$/g,
                    (match, formula) => {
                        const cleanFormula = formula.trim();
                        return katex.renderToString(cleanFormula, {
                            throwOnError: false,
                            displayMode: false,
                            strict: 'ignore'
                        });
                    }
                ), { ADD_ATTR: ['class'] });
            } catch (err) {
                showToast('预览出错：' + err.message, 'error');
                console.error('预览错误详情：', err);
            }
        }
    </script>
</body>
</html>