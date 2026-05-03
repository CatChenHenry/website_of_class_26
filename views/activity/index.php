<?php require ROOT_DIR . '/views/common/navbar.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>26班网站 - 活动</title>
    <style>
        .container {
            width: 1000px;
            margin: 20px auto;
            margin-top: 60px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0069d9;
        }

        .btn-edit {
            display: inline;
            background: #28a745;
            color: white;
            padding: 4px 10px;
            font-size: 13px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: inherit;
            line-height: normal;
            box-sizing: border-box;
        }

        .btn-edit:hover {
            background: #218838;
        }

        .btn-view {
            display: inline;
            background: #007bff;
            color: white;
            padding: 4px 10px;
            font-size: 13px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: inherit;
            line-height: normal;
            box-sizing: border-box;
        }

        .btn-view:hover {
            background: #0069d9;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 4px 10px;
            font-size: 13px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: inherit;
            line-height: normal;
            box-sizing: border-box;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 14px 16px;
            border: 1px solid #e0e0e0;
            text-align: left;
        }

        th {
            background: #2c3e50;
            color: white;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        tr:hover {
            background: #eef2f7;
        }

        .action-cell {
            display: flex;
            gap: 6px;
        }

        .empty-msg {
            text-align: center;
            color: #999;
            padding: 60px 0;
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .container {
                width: 100%;
                margin-top: 40px;
                padding: 0 16px;
                box-sizing: border-box;
            }

            .page-header h1 {
                font-size: 20px;
            }

            table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                font-size: 14px;
            }

            th, td {
                padding: 10px 8px;
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .container {
                margin-top: 30px;
                padding: 0 12px;
            }

            .page-header {
                flex-wrap: wrap;
                gap: 10px;
            }

            th, td {
                padding: 8px 6px;
                font-size: 12px;
            }

            .btn-edit, .btn-view, .btn-delete {
                padding: 3px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="page-header">
            <h1>活动列表</h1>
            <?php if (isset($_SESSION['username']) && canManageActivity($_SESSION['permissions'])): ?>
                <a href="/activity/create" class="btn btn-primary">+ 发布活动</a>
            <?php endif; ?>
        </div>

        <?php if (empty($activities)): ?>
            <div class="empty-msg">暂无活动</div>
        <?php else: ?>
            <table>
                <tr>
                    <th style="width:60px;">序号</th>
                    <th>活动名称</th>
                    <th style="width:200px;">活动时间</th>
                    <th style="width:200px;">操作</th>
                </tr>
                <?php $i = 1;
                foreach ($activities as $act): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo sanitizeHtml($act['name']); ?></td>
                        <td><?php echo htmlspecialchars($act['activity_time']); ?></td>
                        <td>
                            <div class="action-cell">
                                <form method="POST" action="/activity/delete" style="display:inline;" class="act-delete-form">
                                    <button type="button" class="btn btn-view" onclick="window.location.href='/activity/show?id=<?php echo (int) $act['id']; ?>'">查看</button>
                                    <?php if (isset($_SESSION['username']) && canManageActivity($_SESSION['permissions'])): ?>
                                        <button type="button" class="btn btn-edit" onclick="window.location.href='/activity/edit?id=<?php echo (int) $act['id']; ?>'">编辑</button>
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int) $act['id']; ?>">
                                        <button type="button" class="btn btn-delete" onclick="confirmActDelete(this)">删除</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
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