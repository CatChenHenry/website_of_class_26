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
    <link rel="stylesheet" href="/static/css/message.css">
    <link rel="stylesheet" href="/static/css/qzone-base.css">
    <style>
        .user-avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #ddd;
        }
        @media (max-width: 480px) {
            .user-avatar-sm { width: 28px; height: 28px; }
        }
    </style>
</head>

<body>
    <div class="qzone-page">
        <div class="qzone-page-header">
            <h1>管理员控制台 - 用户管理</h1>
            <div class="qzone-page-actions">
                <a href="/admin/createUser" class="qzone-btn qzone-btn-primary">新建用户</a>
                <a href="/admin/importUsers" class="qzone-btn qzone-btn-success" style="background:#17a2b8;">批量导入</a>
            </div>
        </div>

        <div class="qzone-card qzone-card-nopad">
            <div class="qzone-batch-bar" id="batchBar">
                <span class="batch-info">已选择 <strong id="selectedCount">0</strong> 个用户</span>
                <button class="qzone-btn qzone-btn-danger qzone-btn-sm" onclick="showBatchDeleteModal()">批量删除</button>
                <button class="qzone-btn qzone-btn-secondary qzone-btn-sm" onclick="clearSelection()">取消选择</button>
            </div>
            <div class="qzone-table-wrap">
            <table class="qzone-table">
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
                            <a href="/admin/editUser?stu_no=<?php echo htmlspecialchars($user['stu_no']); ?>" class="qzone-btn qzone-btn-success qzone-btn-sm">编辑</a>
                            <form method="POST" action="/admin/deleteUser" style="display:inline;" class="delete-form">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="stu_no" value="<?php echo htmlspecialchars($user['stu_no']); ?>">
                                <button type="button" class="qzone-btn qzone-btn-danger qzone-btn-sm" style="border:none;cursor:pointer;" onclick="showDeleteModal(this, '<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>')">删除</button>
                            </form>
                        <?php } ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </table>
            </div>
        </div>
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
                            showToast('操作失败：服务器返回无效响应', 'error');
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
                            showToast('批量删除操作失败：服务器返回无效响应', 'error');
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