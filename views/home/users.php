<?php
/** @var string $sort */  // 'stu_no' | 'score'
/** @var string $dir */   // 'ASC' | 'DESC'
/** @var array $users */
/** @var array $topStuNos */
require ROOT_DIR . '/views/common/navbar.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>26班网站 - 用户列表</title>
    <link rel="stylesheet" href="/static/css/qzone-base.css">
    <style>
        .user-avatar-sm { width:36px; height:36px; border-radius:50%; object-fit:cover; border:1px solid #ddd; }
        tr.hidden-row { display:none; }
        .filter-bar { display:flex; align-items:center; gap:6px; margin-bottom:12px; font-size:14px; color:#666; }
        .filter-bar label { cursor:pointer; display:flex; align-items:center; gap:4px; }
        .hl-highlight {
            font-weight:700;
            color:#f35626;
            background:linear-gradient(92deg,#f35626,#feab3a);
            background-clip:text;
            -webkit-text-fill-color:transparent;
            animation:hl-hue 10s linear infinite;
        }
        @keyframes hl-hue { from{filter:hue-rotate(0)} to{filter:hue-rotate(-360deg)} }
    </style>
</head>
<body>
<div class="qzone-page">
    <div class="qzone-page-header">
        <h1>用户列表</h1>
    </div>
    <div class="qzone-card qzone-card-nopad">
        <div style="padding:16px 24px 0;">
            <!-- 排序栏 -->
            <div class="qzone-sort-bar">
                <span>排序方式：</span>
                <a href="/home/users?sort=stu_no&dir=<?= $dir ?>" class="qzone-sort-btn <?= $sort==='stu_no'?'active':'' ?>">按学号</a>
                <a href="/home/users?sort=score&dir=<?= $dir ?>" class="qzone-sort-btn <?= $sort==='score'?'active':'' ?>">按分数</a>

                <span style="margin-left:10px;">排序方向：</span>
                <a href="/home/users?sort=<?= $sort ?>&dir=ASC" class="qzone-sort-btn <?= $dir==='ASC'?'active':'' ?>">升序</a>
                <a href="/home/users?sort=<?= $sort ?>&dir=DESC" class="qzone-sort-btn <?= $dir==='DESC'?'active':'' ?>">降序</a>

                <span style="font-size:12px;color:var(--qzone-text-light,#999);margin-left:10px;">
                    当前：<?= $sort==='score' ? '按分数' : '按学号' ?>（<?= $dir==='ASC' ? '升序 ↑' : '降序 ↓' ?>）
                    <?php if ($sort === 'score'): ?> ｜ 同分按学号升序<?php endif; ?>
                </span>
            </div>

            <!-- 过滤器 -->
            <div class="filter-bar">
                <label><input type="checkbox" id="hideSpecial" checked> 隐藏管理员、测试用户、老师</label>
            </div>
        </div>

        <!-- 表格 -->
        <div class="qzone-table-wrap">
            <table class="qzone-table">
                <thead>
                    <tr>
                        <th class="avatar-col">头像</th><th>学号</th><th>姓名</th><th>分数</th><th>邮箱</th><th>权限</th><th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u):
                        $isSpecial = isManager($u['permissions']) || $u['stu_no'] < 0;
                        // 管理员高亮，TopScorer 高亮（排除管理员/测试用户）
                        $isAdmin = isManager($u['permissions']);
                        $isTop = !$isSpecial && in_array($u['stu_no'], $topStuNos);
                        $hl = ($isAdmin || $isTop) ? ' hl-highlight' : '';
                    ?>
                    <tr class="<?= $isSpecial ? 'special-row' : '' ?>">
                        <td class="avatar-col">
                            <img src="<?= htmlspecialchars($u['avatar'] ?: '/static/avatars/default.jpg') ?>" class="user-avatar-sm" alt="<?= htmlspecialchars($u['name']) ?>" onerror="this.src='/static/avatars/default.jpg'">
                        </td>
                        <td><?= htmlspecialchars($u['stu_no']) ?></td>
                        <td><span class="<?= $hl ?>"><?= htmlspecialchars($u['name']) ?></span></td>
                        <td><?= htmlspecialchars($u['score'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= $u['permissions']==='teacher'?'老师':(isManager($u['permissions'])?'管理员':'学生') ?></td>
                        <td>
                            <a href="/user/homepage?stu_no=<?= htmlspecialchars($u['stu_no']) ?>" class="qzone-btn qzone-btn-primary qzone-btn-sm">查看主页</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function(){
    var cb = document.getElementById('hideSpecial');
    var rows = document.querySelectorAll('tr.special-row');
    var toggle = function(){ rows.forEach(function(r){ r.classList.toggle('hidden-row', cb.checked) }) };
    cb.addEventListener('change', toggle);
    toggle();
})();
</script>
</body>
</html>
