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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Microsoft Yahei", sans-serif;
            background-color: #f5f5f5;
        }

        .container {
            width: 80%;
            max-width: 1200px;
            margin: 70px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .signature-wrap {
            position: relative;
        }

        #signature {
            width: 100%;
            height: 80px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            line-height: 1.6;
            resize: none;
        }

        .length-tip {
            position: absolute;
            right: 10px;
            bottom: 10px;
            color: #999;
            font-size: 12px;
        }

        #homepage {
            width: 100%;
            height: 300px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
        }

        .btn-group {
            margin: 20px 0;
        }

        button {
            padding: 8px 20px;
            margin-right: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            background-color: #007bff;
            color: white;
        }

        button:hover {
            background-color: #0056b3;
        }

        #preview-area {
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 20px;
            margin-top: 10px;
            display: none;
            background-color: #fafafa;
        }

        #signature-preview {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 4px;
            font-size: 16px;
        }

        .katex {
            font-size: 1.1em !important;
        }

        .katex-display {
            text-align: center;
            margin: 1em 0;
        }

        @media (max-width: 768px) {
            .container {
                width: 100%;
                max-width: 100%;
                margin: 50px 0;
                padding: 16px;
                border-radius: 0;
            }

            .signature-toolbar {
                display: flex;
                flex-wrap: wrap;
                gap: 4px;
            }

            .signature-toolbar button {
                padding: 4px 10px;
                margin-right: 0;
                font-size: 13px;
            }

            #homepage {
                height: 200px;
            }
        }

        @media (max-width: 480px) {
            .container {
                margin: 40px 0;
                padding: 12px;
            }

            h2 {
                font-size: 18px;
            }

            .signature-toolbar button {
                padding: 3px 8px;
                font-size: 12px;
            }

            #signature {
                font-size: 13px;
            }

            #homepage {
                height: 160px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <?php
    require ROOT_DIR . '/views/common/navbar.php';
    ?>
    <div class="container">
        <h2>个人资料编辑</h2>
        <form method="POST" id="profile-form" action="/user/ChangeProfile">
            <?php echo csrfField(); ?>
            <div class="form-group">
                <label for="signature">个性签名（长度≤15字符，支持简单HTML样式）</label>
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
            <div class="form-group">
                <label for="homepage">个人主页内容（支持Markdown、KaTex、代码高亮）</label>
                <textarea id="homepage" name="homepage"
                    placeholder="输入个人主页内容"><?= htmlspecialchars($homepage_content) ?></textarea>
            </div>
            <div class="btn-group">
                <button type="submit" id="save-btn">保存所有内容</button>
                <button type="button" id="preview-btn">预览个人主页</button>
            </div>
        </form>
        <div id="preview-area"></div>
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
</body>

</html>