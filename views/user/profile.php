<?php
/** @var string $signature_content */
/** @var string $homepage_content */
$username = $_SESSION['username'] ?? '游客';
?>

<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($username) ?> - 个人资料编辑</title>
    <link rel="stylesheet" href="https://unpkg.com/katex@0.16.21/dist/katex.min.css">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/styles/github.min.css">
    <link rel="stylesheet" href="/static/css/message.css">
    <link rel="stylesheet" href="/static/css/qzone-base.css">
    <style>
        .signature-wrap { position: relative; }
        #signature { width: 100%; height: 80px; padding: 10px; border: 1px solid var(--qzone-border,#ddd); border-radius: 6px; font-size: 14px; line-height: 1.6; resize: none; font-family: inherit; box-sizing: border-box; }
        #signature:focus { outline: none; border-color: var(--qzone-primary,#00a1d6); box-shadow: 0 0 0 3px rgba(0,161,214,.12); }
        .length-tip { position: absolute; right: 10px; bottom: 10px; color: #999; font-size: 12px; }
        #homepage { width: 100%; height: 300px; padding: 10px; border: 1px solid var(--qzone-border,#ddd); border-radius: 6px; font-size: 14px; line-height: 1.6; resize: vertical; font-family: inherit; box-sizing: border-box; }
        #homepage:focus { outline: none; border-color: var(--qzone-primary,#00a1d6); box-shadow: 0 0 0 3px rgba(0,161,214,.12); }
        .signature-toolbar { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 8px; }
        .signature-toolbar button { padding: 4px 12px; border: 1px solid var(--qzone-border,#ddd); border-radius: 4px; background: #fff; cursor: pointer; font-size: 14px; font-family: inherit; transition: all .2s; }
        .signature-toolbar button:hover { background: #f0f0f0; border-color: var(--qzone-primary,#00a1d6); }
        #signature-preview { margin: 10px 0; padding: 10px; border: 1px solid var(--qzone-border,#eee); border-radius: 6px; font-size: 16px; background: #fafafa; }
        #preview-area { border: 1px solid var(--qzone-border,#eee); border-radius: 6px; padding: 20px; margin-top: 10px; display: none; background-color: #fafafa; }
        .katex { font-size: 1.1em !important; }
        .katex-display { text-align: center; margin: 1em 0; }
        @media (max-width: 768px) {
            .signature-toolbar button { padding: 4px 10px; font-size: 13px; }
            #homepage { height: 200px; }
        }
        @media (max-width: 480px) {
            #homepage { height: 160px; font-size: 13px; }
            #signature { font-size: 13px; }
        }
    </style>
</head>

<body>
    <?php
    require ROOT_DIR . '/views/common/navbar.php';
    ?>
    <div class="qzone-page">
        <div class="qzone-card">
            <div class="qzone-page-header">
                <h2>个人资料编辑</h2>
            </div>
            <form method="POST" id="profile-form" action="/user/ChangeProfile">
                <?php echo csrfField(); ?>
                <div class="qzone-form-item">
                    <label class="qzone-form-label" for="signature">个性签名（长度≤15字符，支持简单HTML样式）</label>
                    <div class="signature-toolbar">
                        <button type="button" onclick="wrapSig('b')" title="加粗"><b>B</b></button>
                        <button type="button" onclick="wrapSig('i')" title="斜体"><i>I</i></button>
                        <button type="button" onclick="wrapSig('u')" title="下划线"><u>U</u></button>
                        <button type="button" onclick="wrapSig('s')" title="删除线"><s>S</s></button>
                        <button type="button" onclick="wrapSig('em')" title="强调"><em>em</em></button>
                        <button type="button" onclick="wrapSig('strong')" title="着重"><strong>strong</strong></button>
                        <button type="button" onclick="wrapSig('small')" title="小字"><small>小</small></button>
                        <button type="button" onclick="wrapSig('sub')" title="下标">X<sub>n</sub></button>
                        <button type="button" onclick="wrapSig('sup')" title="上标">X<sup>n</sup></button>
                    </div>
                    <div class="signature-wrap">
                        <textarea id="signature" name="signature"
                            placeholder="输入个性签名，如：<b>加粗</b> <i>斜体</i>"><?= htmlspecialchars($signature_content) ?></textarea>
                        <span class="length-tip" id="signature-length">0/15</span>
                    </div>
                    <div id="signature-preview">预览：<?= sanitizeHtml($signature_content) ?></div>
                </div>
                <div class="qzone-form-item">
                    <label class="qzone-form-label" for="homepage">个人主页内容（支持Markdown、KaTex、代码高亮）</label>
                    <textarea id="homepage" name="homepage"
                        placeholder="输入个人主页内容"><?= htmlspecialchars($homepage_content) ?></textarea>
                </div>
                <div class="qzone-form-actions">
                    <button type="submit" id="save-btn" class="qzone-btn qzone-btn-primary">保存所有内容</button>
                    <button type="button" id="preview-btn" class="qzone-btn qzone-btn-secondary">预览个人主页</button>
                </div>
            </form>
            <div id="preview-area"></div>
        </div>
    </div>

    <script src="https://cdn.bootcdn.net/ajax/libs/marked/14.0.0/marked.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://unpkg.com/katex@0.16.21/dist/katex.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/dompurify/3.2.5/purify.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const previewBtn = document.getElementById('preview-btn');
            const homepageText = document.getElementById('homepage');
            const previewArea = document.getElementById('preview-area');
            const profileForm = document.getElementById('profile-form');
            const signatureText = document.getElementById('signature');
            const signatureLength = document.getElementById('signature-length');
            const signaturePreview = document.getElementById('signature-preview');

            signatureText.addEventListener('input', function () {
                const plainText = this.value.trim().replace(/<[^>]*>/g, '');
                const len = mbStrLen(plainText);
                signatureLength.textContent = `${len}/15`;
                signatureLength.style.color = len > 15 ? 'red' : '#999';
                let sigContent = this.value.trim();
                signaturePreview.innerHTML = '预览：' + DOMPurify.sanitize(sigContent, {
                    ALLOWED_TAGS: ['b','i','u','s','em','strong','small','sub','sup','br','span','a'],
                    ALLOWED_ATTR: ['href','class']
                });
            });

            marked.setOptions({
                gfm: true,
                breaks: true,
                mangle: false
            });

            previewBtn.addEventListener('click', function () {
                try {
                    previewArea.style.display = 'block';
                    let content = homepageText.value || '暂无个人主页内容';
                    content = content.replace(/\\\\/g, '\\');
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
            });

            function mbStrLen(str) {
                if (typeof str !== 'string') return 0;
                return [...str].length;
            }

            window.wrapSig = function(tag) {
                const ta = document.getElementById('signature');
                const start = ta.selectionStart;
                const end = ta.selectionEnd;
                const sel = ta.value.substring(start, end);
                const replacement = `<${tag}>${sel}</${tag}>`;
                ta.setRangeText(replacement, start, end, 'end');
                ta.focus();
                ta.dispatchEvent(new Event('input'));
            };

            signatureText.dispatchEvent(new Event('input'));
        });
    </script>
    <!-- 全局提示框脚本（与管理员控制台一致） -->
    <script src="/static/js/message.js"></script>
</body>

</html>