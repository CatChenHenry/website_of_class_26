<?php
/** @var array<int, array<string, mixed>> $users */
/** @var string $orderBy */
/** @var string $orderDir */
/** @var array<int, int> $topStuNos */
require ROOT_DIR . '/views/common/navbar.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>26班网站 - 用户列表</title>
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

        .sort-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            align-items: center;
        }

        .sort-bar span {
            font-size: 14px;
            color: #666;
        }

        .sort-btn {
            padding: 6px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .sort-btn:hover {
            background: #f0f0f0;
        }

        .sort-btn.active {
            background: #2c3e50;
            color: white;
            border-color: #2c3e50;
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

        .beautiful-animated {
            font-weight: 700;
            color: #f35626;
            background-image: linear-gradient(
                92deg,
                rgb(243, 86, 38) 0%,
                rgb(254, 171, 58) 100%
            );
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: 10s linear 0s infinite normal none running beautiful-animation;
        }

        @keyframes beautiful-animation {
            from {
                -webkit-filter: hue-rotate(0deg);
            }
            to {
                -webkit-filter: hue-rotate(-360deg);
            }
        }

        .filter-bar {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
            font-size: 14px;
            color: #666;
        }

        .filter-bar label {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .filter-bar input[type="checkbox"] {
            cursor: pointer;
        }

        tr.hidden-row {
            display: none;
        }

        /* 头像列 */
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

            .sort-bar {
                flex-wrap: wrap;
                gap: 6px;
            }

            .sort-bar span {
                font-size: 13px;
            }

            .sort-btn {
                padding: 4px 10px;
                font-size: 13px;
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
        }

        @media (max-width: 480px) {
            .container {
                margin-top: 30px;
                padding: 0 8px;
            }

            .sort-bar span {
                font-size: 12px;
            }

            .user-avatar-sm {
                width: 28px;
                height: 28px;
            }

            .btn-edit {
                padding: 3px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="sort-bar">
            <span>排序方式：</span>
            <a href="/home/users?sort=stu_no&dir=<?php echo $orderDir; ?>" class="sort-btn <?php echo ($orderBy === 'stu_no') ? 'active' : ''; ?>">按学号</a>
            <a href="/home/users?sort=score&dir=<?php echo $orderDir; ?>" class="sort-btn <?php echo ($orderBy === 'score') ? 'active' : ''; ?>">按分数</a>
            <span style="margin-left: 10px;">排序方向：</span>
            <a href="/home/users?sort=<?php echo $orderBy; ?>&dir=ASC" class="sort-btn <?php echo (strtoupper($orderDir) === 'ASC') ? 'active' : ''; ?>">升序</a>
            <a href="/home/users?sort=<?php echo $orderBy; ?>&dir=DESC" class="sort-btn <?php echo (strtoupper($orderDir) === 'DESC') ? 'active' : ''; ?>">降序</a>
            <span style="font-size:12px;color:#999;margin-left:10px;">* 按分数排序时，分数相同则按学号升序排列；管理员和测试用户一律排在最上方</span>
        </div>
        <div class="filter-bar">
            <label>
                <input type="checkbox" id="hideSpecial" checked>
                隐藏管理员和测试用户（学号为负数）
            </label>
        </div>
        <table>
            <tr>
                <th>头像</th>
                <th>学号</th>
                <th>姓名</th>
                <th>分数</th>
                <th>邮箱</th>
                <th>权限</th>
                <th>个性签名</th>
                <th>操作</th>
            </tr>
            <?php
            foreach ($users as $user):
                $isAdmin = isManager($user['permissions']);
                $isTestUser = $user['stu_no'] < 0;
                $isSpecial = $isAdmin || $isTestUser;
                $isHighlighted = $isAdmin || (!$isSpecial && in_array($user['stu_no'], $topStuNos));
                $hlClass = $isHighlighted ? ' beautiful-animated' : '';
            ?>
                <tr class="<?php echo $isSpecial ? 'special-row' : ''; ?>">
                    <td class="avatar-col">
                        <img src="<?php echo htmlspecialchars(!empty($user['avatar']) ? $user['avatar'] : '/static/avatars/default.jpg'); ?>" class="user-avatar-sm" alt="<?php echo htmlspecialchars($user['name']); ?>" onerror="this.src='/static/avatars/default.jpg'">
                    </td>
                    <td><?php echo htmlspecialchars($user['stu_no']); ?></td>
                    <td><span class="<?php echo $hlClass; ?>"><?php echo htmlspecialchars($user['name']); ?></span></td>
                    <td><?php echo htmlspecialchars($user['score'] ?? 0); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['permissions'] === 'teacher' ? '老师' : (isManager($user['permissions']) ? '管理员' : '学生'); ?></td>
                    <td><span class="<?php echo $hlClass; ?>"><?php echo !empty($user['signature']) ? sanitizeHtml($user['signature']) : ''; ?></span></td>
                    <td>
                        <a href="/user/homepage?stu_no=<?php echo htmlspecialchars($user['stu_no']); ?>" class="btn btn-edit">查看主页</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <script>
    (function() {
        var checkbox = document.getElementById('hideSpecial');
        var rows = document.querySelectorAll('tr.special-row');
        function toggleRows() {
            rows.forEach(function(row) {
                row.classList.toggle('hidden-row', checkbox.checked);
            });
        }
        checkbox.addEventListener('change', toggleRows);
        toggleRows();
    })();
    </script>
</body>

</html>
