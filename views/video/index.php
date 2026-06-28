<?php
/** @var array $videos */
require ROOT_DIR . '/views/common/navbar.php';
$isAdmin = isset($_SESSION['permissions']) && canManageActivity($_SESSION['permissions']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>视频 - 26班网站</title>
    <link rel="stylesheet" href="/static/css/qzone-base.css">
    <style>
        .video-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .video-card {
            background: var(--qzone-card-bg, #fff);
            border-radius: var(--qzone-radius, 12px);
            overflow: hidden;
            box-shadow: var(--qzone-shadow, 0 2px 12px rgba(0,0,0,0.1));
            border: 1px solid var(--qzone-border, #e7e7e7);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .video-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
        .video-thumb {
            width: 100%; aspect-ratio: 16/9;
            background: #2c3e50;
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .video-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .video-thumb .play-icon {
            position: absolute;
            font-size: 48px; color: rgba(255,255,255,0.85);
            text-shadow: 0 2px 8px rgba(0,0,0,0.4);
        }
        .video-episode {
            position: absolute; top: 8px; left: 8px;
            background: var(--qzone-primary, #00a1d6);
            color: #fff; font-size: 12px; font-weight: 600;
            padding: 3px 10px; border-radius: 12px;
        }
        .video-info { padding: 16px; }
        .video-info h3 { font-size: 16px; margin: 0 0 6px; color: #333; }
        .video-info p { font-size: 13px; color: #888; margin: 0; line-height: 1.5; }
        .video-actions { padding: 0 16px 14px; }
        .video-actions a,
        .video-actions button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            line-height: 1.5;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.25s;
            box-sizing: border-box;
        }
        .video-actions a:hover,
        .video-actions button:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .video-actions .edit-btn { background: #28a745; color: #fff; }
        .video-actions .edit-btn:hover { background: #218838; }
        .video-actions .delete-btn { background: #dc3545; color: #fff; }
        .video-actions .delete-btn:hover { background: #c82333; }
        .empty-msg { text-align: center; color: #999; padding: 80px 0; font-size: 16px; }
    </style>
</head>
<body>
<div class="qzone-page">
    <div class="qzone-page-header">
        <h1>📺 视频</h1>
        <?php if ($isAdmin): ?>
        <a href="/video/create" class="qzone-btn qzone-btn-primary">+ 发布视频</a>
        <?php endif; ?>
    </div>
    <?php if (empty($videos)): ?>
        <div class="empty-msg">暂无视频</div>
    <?php else: ?>
    <div class="video-grid">
        <?php foreach ($videos as $v): ?>
        <div>
            <a class="video-card" href="/video/watch?id=<?= (int)$v['id'] ?>">
                <div class="video-thumb">
                    <?php if ($v['thumbnail']): ?>
                        <img src="<?= htmlspecialchars($v['thumbnail']) ?>" alt="<?= htmlspecialchars($v['title']) ?>">
                    <?php else: ?>
                        <span class="play-icon">▶</span>
                    <?php endif; ?>
                    <span class="video-episode">第<?= (int)$v['episode'] ?>集</span>
                </div>
                <div class="video-info">
                    <h3><?= htmlspecialchars($v['title']) ?></h3>
                    <p><?= htmlspecialchars(mb_strlen($v['description'] ?? '') > 80 ? mb_substr($v['description'], 0, 80) . '...' : ($v['description'] ?? '')) ?></p>
                </div>
            </a>
            <?php if ($isAdmin): ?>
            <div class="video-actions">
                <a href="/video/edit?id=<?= (int)$v['id'] ?>" class="edit-btn">编辑</a>
                <form method="POST" action="/video/delete" style="display:inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                    <button type="submit" class="delete-btn" onclick="return confirm('确定删除「<?= htmlspecialchars($v['title'], ENT_QUOTES) ?>」吗？')">删除</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
