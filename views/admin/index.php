<?php
/** @var array<int, array<string, mixed>> $users */
require ROOT_DIR . '/views/common/navbar.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员控制台</title>
    <style>
        .container {
            width: 1200px;
            margin: 20px auto;
            margin-top: 60px;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-edit {
            background: #28a745;
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f8f9fa;
        }

        .success {
            color: #fff;
            margin: 10px 0;
        }

        .batch-bar {
            display: none;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
            margin: 12px 0;
        }
        .batch-bar.active { display: flex; }
        .batch-bar .batch-info { font-size: 14px; color: #856404; }
        .btn-batch-delete {
            padding: 6px 16px;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-batch-delete:hover { background: #c82333; }
        .btn-batch-cancel {
            padding: 6px 16px;
            background: #6c757d;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }

        .cb-col { width: 40px; text-align: center; }
        .cb-col input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }

        .avatar-col { width: 50px; text-align: center; }
        .user-avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ddd;
        }

        @media (max-width: 768px) {
            .container {
                width: 100%;
                margin-top: 40px;
                padding: 0 12px;
                box-sizing: border-box;
            }

            h1 {
                font-size: 20px;
            }

            table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                font-size: 13px;
            }

            th, td {
                padding: 8px 6px;
                font-size: 12px;
            }

            .batch-bar {
                flex-wrap: wrap;
                gap: 8px;
            }
        }

        @media (max-width: 480px) {
            .container {
                margin-top: 30px;
                padding: 0 8px;
            }

            .btn {
                padding: 4px 8px;
                font-size: 12px;
            }

            .user-avatar-sm {
                width: 28px;
                height: 28px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>管理员控制台 - 用户管理</h1>
        <a href="/admin/createUser" class="btn btn-primary">新建用户</a>
        <a href="/admin/importUsers" class="btn btn-primary" style="margin-left: 8px; background: #17a2b8;">批量导入</a>

        <div class="batch-bar" id="batchBar">
            <span class="batch-info">已选择 <strong id="selectedCount">0</strong> 个用户</span>
            <button class="btn-batch-delete" onclick="showBatchDeleteModal()">批量删除</button>
            <button class="btn-batch-cancel" onclick="clearSelection()">取消选择</button>
        </div>

        <table>
            <tr>
                <th class="cb-col"><input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"></th>
                <th>学号</th>
                <th>头像</th>
                <th>姓名</th>
                <th>分数</th>
                <th>邮箱</th>
                <th>权限</th>
                <th>班级</th>
                <th>操作</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr data-stu-no="<?php echo htmlspecialchars($user['stu_no']); ?>">
                    <td class="cb-col">
                        <?php if ($user['permissions'] !== 'admin' && $user['permissions'] !== 'administrator'): ?>
                        <input type="checkbox" class="row-cb" value="<?php echo htmlspecialchars($user['stu_no']); ?>" onchange="onRowCheckChange()">
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($user['stu_no']); ?></td>
                    <td class="avatar-col">
                        <img src="<?php echo htmlspecialchars(!empty($user['avatar']) ? $user['avatar'] : '/static/avatars/default.jpg'); ?>" class="user-avatar-sm" alt="<?php echo htmlspecialchars($user['name']); ?>" onerror="this.src='/static/avatars/default.jpg'">
                    </td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['score'] ?? 0); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['permissions'] === 'teacher' ? '老师' : (isManager($user['permissions']) ? '管理员' : '学生'); ?></td>
                    <td><?php echo htmlspecialchars($user['stu_class']); ?></td>
                    <td>
                        <?php
                        if ($user['permissions'] !== 'admin' && $user['permissions'] !== 'administrator') {
                            ?>
                            <a href="/admin/editUser?stu_no=<?php echo htmlspecialchars($user['stu_no']); ?>" class="btn btn-edit">编辑</a>
                            <form method="POST" action="/admin/deleteUser" style="display:inline;" class="delete-form">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="stu_no" value="<?php echo htmlspecialchars($user['stu_no']); ?>">
                                <button type="button" class="btn btn-delete" style="border:none;cursor:pointer;" onclick="showDeleteModal(this, '<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>')">删除</button>
                            </form>
                        <?php } ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <script>
        function showDeleteModal(btn, name) {
            var form = btn.closest('form');
            var row = btn.closest('tr');
            showConfirmModal({
                message: '确定删除【' + name + '】吗？',
                confirmText: '确定删除',
                onConfirm: function() {
                    var formData = new FormData(form);
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', form.action, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.onload = function() {
                        try {
                            var res = JSON.parse(xhr.responseText);
                            if (res.success) {
                                showToast(res.message, 'success');
                                if (row) row.remove();
                            } else {
                                showToast(res.message, 'error');
                            }
                            if (res.csrf_token) {
                                var tokenInput = document.querySelector('input[name="csrf_token"]');
                                if (tokenInput) tokenInput.value = res.csrf_token;
                            }
                        } catch (e) {
                            showToast('删除用户成功', 'success');
                            if (row) row.remove();
                        }
                    };
                    xhr.onerror = function() {
                        showToast('请求失败，请重试', 'error');
                    };
                    xhr.send(formData);
                }
            });
        }

        function toggleSelectAll(cb) {
            var boxes = document.querySelectorAll('.row-cb');
            for (var i = 0; i < boxes.length; i++) {
                boxes[i].checked = cb.checked;
            }
            onRowCheckChange();
        }

        function onRowCheckChange() {
            var boxes = document.querySelectorAll('.row-cb');
            var checked = document.querySelectorAll('.row-cb:checked');
            var bar = document.getElementById('batchBar');
            document.getElementById('selectedCount').textContent = checked.length;

            if (checked.length > 0) {
                bar.classList.add('active');
            } else {
                bar.classList.remove('active');
            }

            selectAll.checked = boxes.length > 0 && checked.length === boxes.length;
        }

        function clearSelection() {
            var boxes = document.querySelectorAll('.row-cb');
            for (var i = 0; i < boxes.length; i++) {
                boxes[i].checked = false;
            }
            document.getElementById('selectAll').checked = false;
            document.getElementById('batchBar').classList.remove('active');
        }

        function showBatchDeleteModal() {
            var checked = document.querySelectorAll('.row-cb:checked');
            if (checked.length === 0) return;

            showConfirmModal({
                message: '确定删除选中的 ' + checked.length + ' 个用户吗？此操作不可撤销！',
                confirmText: '确定删除',
                onConfirm: function() {
                    var stuNos = [];
                    for (var i = 0; i < checked.length; i++) {
                        stuNos.push(checked[i].value);
                    }

                    var formData = new FormData();
                    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
                    for (var i = 0; i < stuNos.length; i++) {
                        formData.append('stu_nos[]', stuNos[i]);
                    }

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '/admin/batchDeleteUsers', true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.onload = function() {
                        try {
                            var res = JSON.parse(xhr.responseText);
                            if (res.success) {
                                showToast(res.message, 'success');
                                for (var j = 0; j < stuNos.length; j++) {
                                    var row = document.querySelector('tr[data-stu-no="' + stuNos[j] + '"]');
                                    if (row) row.remove();
                                }
                                document.getElementById('batchBar').classList.remove('active');
                                document.getElementById('selectAll').checked = false;
                            } else {
                                showToast(res.message, 'error');
                            }
                            if (res.csrf_token) {
                                var tokenInput = document.querySelector('input[name="csrf_token"]');
                                if (tokenInput) tokenInput.value = res.csrf_token;
                            }
                        } catch (e) {
                            showToast('批量删除完成', 'success');
                        }
                    };
                    xhr.onerror = function() {
                        showToast('请求失败，请重试', 'error');
                    };
                    xhr.send(formData);
                }
            });
        }
    </script>
</body>

</html>