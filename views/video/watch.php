<?php
/** @var array $video */
require ROOT_DIR . '/views/common/navbar.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($video['title']) ?> - 视频</title>
    <link rel="stylesheet" href="/static/css/qzone-base.css">
    <style>
        .video-player-wrap {
            background: #000; border-radius: 12px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .video-player-wrap iframe { width: 100%; height: 480px; border: none; display: block; }
        .video-player-wrap video { width: 100%; max-height: 480px; display: block; }
        .video-desc {
            background: var(--qzone-card-bg, #fff);
            border-radius: var(--qzone-radius, 12px);
            padding: 24px; margin-top: 20px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            border: 1px solid var(--qzone-border, #e7e7e7);
        }
        .video-desc h2 { margin: 0 0 8px; font-size: 22px; color: #333; }
        .video-meta { font-size: 13px; color: #888; margin-bottom: 14px; }
        .video-meta span { margin-right: 14px; }
        .video-desc p { line-height: 1.8; color: #555; font-size: 15px; white-space: pre-wrap; }
    </style>
</head>
<body>
<div class="qzone-page">
    <div class="qzone-page-header">
        <h1>📺 视频</h1>
        <a href="/video/index" class="qzone-btn qzone-btn-secondary">← 返回列表</a>
    </div>
    <div class="video-player-wrap">
        <?php if (strpos($video['video_url'], '<iframe') !== false): ?>
            <?= $video['video_url'] ?>
        <?php elseif (preg_match('/\.(mp4|webm|ogg|mov|avi|mkv|flv|wmv)($|\?)/i', $video['video_url'])): ?>
            <video controls style="width:100%;max-height:480px;">
                <source src="<?= htmlspecialchars($video['video_url']) ?>">
                您的浏览器不支持视频播放
            </video>
        <?php else: ?>
            <iframe src="<?= htmlspecialchars($video['video_url']) ?>" allowfullscreen allow="autoplay; fullscreen"></iframe>
        <?php endif; ?>
    </div>
    <div class="video-desc">
        <h2>第<?= (int)$video['episode'] ?>集 · <?= htmlspecialchars($video['title']) ?></h2>
        <div class="video-meta">
            <span>发布时间：<?= htmlspecialchars($video['created_at']) ?></span>
        </div>
        <?php if (!empty($video['description'])): ?>
        <p><?= nl2br(htmlspecialchars($video['description'])) ?></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
