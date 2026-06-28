<?php require ROOT_DIR . '/views/common/navbar.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>26班网站 - 活动</title>
    <link rel="stylesheet" href="/static/css/qzone-base.css">
    <style>
        .action-cell { display: flex; gap: 6px; }
        .empty-msg { text-align: center; color: var(--qzone-text-light); padding: 60px 0; font-size: 16px; }
    </style>
</head>

<body>
    <div class="qzone-page">
        <div class="qzone-page-header">
            <h1>活动列表</h1>
            <?php if (isset($_SESSION['username']) && canManageActivity($_SESSION['permissions'])): ?>
                <a href="/activity/create" class="qzone-btn qzone-btn-primary">+ 发布活动</a>
            <?php endif; ?>
        </div>

        <?php if (empty($activities)): ?>
            <div class="empty-msg">暂无活动</div>
        <?php else: ?>
            <div class="qzone-card qzone-card-nopad">
                <div class="qzone-table-wrap">
                    <table class="qzone-table">
                        <thead>
                            <tr>
                                <th style="width:60px;">序号</th>
                                <th>活动名称</th>
                                <th style="width:200px;">活动时间</th>
                                <th style="width:200px;">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1;
                        foreach ($activities as $act): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo sanitizeHtml($act['name']); ?></td>
                                <td><?php echo htmlspecialchars($act['activity_time']); ?></td>
                                <td>
                                    <div class="action-cell">
                                        <form method="POST" action="/activity/delete" style="display:inline;" class="act-delete-form">
                                            <button type="button" class="qzone-btn qzone-btn-primary qzone-btn-sm" onclick="window.location.href='/activity/show?id=<?php echo (int) $act['id']; ?>'">查看</button>
                                            <?php if (isset($_SESSION['username']) && canManageActivity($_SESSION['permissions'])): ?>
                                                <button type="button" class="qzone-btn qzone-btn-success qzone-btn-sm" onclick="window.location.href='/activity/edit?id=<?php echo (int) $act['id']; ?>'">编辑</button>
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="id" value="<?php echo (int) $act['id']; ?>">
                                                <button type="button" class="qzone-btn qzone-btn-danger qzone-btn-sm" onclick="confirmActDelete(this)">删除</button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmActDelete(btn) {
            var form = btn.closest('form');
            showConfirmModal({
                message: '确定删除该活动吗？',
                confirmText: '确定删除',
                onConfirm: function() { form.submit(); }
            });
        }
    </script>
</body>

</html>