<?php
global $avatar;
?>
<style>
  .navbar {
    width: 100%;
    height: 60px;
    background-color: #2c3e50;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 9999;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .sidebar-toggle-outer {
    position: absolute;
    left: 16px;
    top: 0;
    height: 60px;
    display: flex;
    align-items: center;
    z-index: 1;
  }

  .navbar-content {
    width: 1200px;
    max-width: 90%;
    height: 100%;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .navbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .navbar-logo {
    font-size: 18px;
    font-weight: bold;
    text-decoration: none;
    color: white;
  }

  .sidebar-toggle {
    background: none;
    border: none;
    color: #ecf0f1;
    font-size: 14px;
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 4px;
    transition: background-color 0.3s;
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
  }

  .sidebar-toggle:hover {
    background-color: #34495e;
  }

  .navbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ecf0f1;
    cursor: pointer;
    transition: transform 0.2s;
  }

  .user-avatar:hover {
    transform: scale(1.05);
  }

  .dropdown {
    position: relative;
  }

  .dropdown-trigger {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    border-radius: 4px;
    padding: 4px 8px;
    transition: background-color 0.3s;
  }

  .dropdown-trigger:hover {
    background-color: #34495e;
  }

  .dropdown-toggle {
    cursor: pointer;
    display: inline-block;
    color: #ecf0f1;
    text-decoration: none;
    font-size: 14px;
    padding: 0;
    border-radius: 0;
    transition: none;
  }

  .dropdown-toggle:hover {
    background-color: #34495e;
  }

  .dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: #2c3e50;
    min-width: 150px;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    list-style: none;
    padding: 8px 0;
    margin: 0;
    display: none;
    z-index: 99999;
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
  }

  .dropdown:hover .dropdown-menu {
    display: block;
    opacity: 1;
  }

  .dropdown-item {
    padding: 8px 16px;
    color: #ecf0f1;
    text-decoration: none;
    display: block;
    font-size: 14px;
  }

  .dropdown-item:hover {
    background-color: #34495e;
  }

  .dropdown-divider {
    height: 1px;
    background-color: #eee;
    margin: 8px 0;
  }

  .sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    z-index: 10000;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, visibility 0.3s;
  }

  .sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
  }

  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 260px;
    height: 100%;
    background-color: #2c3e50;
    z-index: 10001;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    display: flex;
    flex-direction: column;
    box-shadow: 4px 0 12px rgba(0, 0, 0, 0.2);
  }

  .sidebar.active {
    transform: translateX(0);
  }

  .sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #34495e;
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 60px;
    box-sizing: border-box;
  }

  .sidebar-title {
    font-size: 16px;
    font-weight: bold;
    color: #ecf0f1;
    white-space: nowrap;
  }

  .sidebar-close {
    background: none;
    border: none;
    color: #95a5a6;
    font-size: 22px;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    border-radius: 4px;
    transition: color 0.2s, background-color 0.2s;
    line-height: 1;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .sidebar-close:hover {
    color: #ecf0f1;
    background-color: #34495e;
  }

  .sidebar-nav {
    padding: 12px 0;
    flex: 1;
  }

  .sidebar-link {
    display: block;
    color: #ecf0f1;
    text-decoration: none;
    font-size: 15px;
    padding: 12px 24px;
    transition: background-color 0.2s;
  }

  .sidebar-link:hover {
    background-color: #34495e;
  }

  .sidebar-link.admin-link {
    color: #e67e22;
  }

  .sidebar-link.admin-link:hover {
    background-color: #34495e;
  }

  .sidebar-divider {
    height: 1px;
    background-color: #34495e;
    margin: 8px 20px;
  }

  .sidebar-section-title {
    color: #95a5a6;
    font-size: 12px;
    font-weight: bold;
    padding: 16px 24px 4px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .page-content {
    margin-top: 80px;
  }

  @media (max-width: 768px) {
    .sidebar {
      width: 260px;
    }

    .navbar-content {
      width: 100%;
      max-width: 100%;
      padding: 0 16px;
    }

    .navbar-logo {
      font-size: 16px;
    }

    .sidebar-toggle {
      font-size: 13px;
      padding: 6px 10px;
    }

    .dropdown-menu {
      min-width: 140px;
      right: -8px;
    }

    .dropdown-item {
      padding: 6px 12px;
      font-size: 13px;
    }

    .dropdown-trigger {
      gap: 8px;
    }

    .dropdown-toggle {
      font-size: 13px;
    }

    .user-avatar {
      width: 30px;
      height: 30px;
    }

    .sidebar-link {
      padding: 10px 20px;
      font-size: 14px;
    }

    .page-content {
      margin-top: 70px;
    }
  }

  @media (max-width: 480px) {
    .navbar {
      height: 52px;
    }

    .sidebar-toggle-outer {
      height: 52px;
      left: 10px;
    }

    .navbar-content {
      padding: 0 12px;
    }

    .navbar-logo {
      font-size: 15px;
    }

    .sidebar-toggle {
      font-size: 12px;
      padding: 5px 8px;
    }

    .user-avatar {
      width: 28px;
      height: 28px;
    }

    .dropdown-toggle {
      font-size: 12px;
    }

    .dropdown-trigger {
      gap: 6px;
      padding: 4px 6px;
    }

    .page-content {
      margin-top: 62px;
    }

    .sidebar-header {
      padding: 14px 16px;
      min-height: 52px;
    }
  }
</style>
<link rel="stylesheet" href="/static/css/message.css">
<script src="/static/js/message.js"></script>

<?php if (isset($_SESSION['username'])): ?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <span class="sidebar-title">菜单</span>
    <button class="sidebar-close" id="sidebarClose">&times;</button>
  </div>
  <div class="sidebar-nav">
    <a href="/home/users" class="sidebar-link">用户列表</a>
    <a href="/activity/index" class="sidebar-link">活动</a>
    <div class="sidebar-section-title">建议与反馈</div>
    <a href="https://codeberg.org/cat_girl/website_of_class_26/issues" class="sidebar-link" target="_blank">Codeberg Issues</a>
    <a href="https://github.com/CatChenHenry/website_of_class_26/issues" class="sidebar-link" target="_blank">GitHub Issues</a>
<?php if (isManager($_SESSION['permissions'])): ?>
        <div class="sidebar-section-title">管理功能</div>
        <a href="/admin/index" class="sidebar-link admin-link">管理员控制台</a>
      <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="navbar">
  <?php if (isset($_SESSION['username'])): ?>
  <div class="sidebar-toggle-outer">
    <button class="sidebar-toggle" id="sidebarToggle">&#9776; 菜单</button>
  </div>
  <?php endif; ?>

  <div class="navbar-content">
    <div class="navbar-left">
      <a href="/home" class="navbar-logo">26班网站</a>
    </div>

    <div class="navbar-right">
      <?php if (isset($_SESSION['username'])): ?>
        <div class="dropdown">
          <div class="dropdown-trigger">
            <img src="<?php echo htmlspecialchars($avatar); ?>" class="user-avatar" alt="<?php echo htmlspecialchars($_SESSION['username']); ?>的头像" onerror="this.src='/static/avatars/default.jpg'">
            <a class="dropdown-toggle"><?php echo htmlspecialchars($_SESSION['username']); ?> ▼</a>
          </div>
          <ul class="dropdown-menu">
            <li><a href="/user/avatar" class="dropdown-item">修改头像</a></li>
            <li><a href="/user/profile" class="dropdown-item">编辑资料</a></li>
            <li><a href="/user/changepwd" class="dropdown-item">修改密码</a></li>
            <li class="dropdown-divider"></li>
            <li>
              <form id="logoutForm" method="POST" action="/user/logout" style="display:inline;margin:0;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
              </form>
              <button onclick="confirmLogout()" class="dropdown-item" style="color: #e74c3c; background:none; border:none; cursor:pointer; padding:8px 16px; font-size:14px; width:100%; text-align:left;">退出登录</button>
            </li>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
<?php if (isset($_SESSION['flash_message'])): ?>
(function() {
  var fm = <?php echo json_encode($_SESSION['flash_message']); ?>;
  <?php unset($_SESSION['flash_message']); ?>
  setTimeout(function() { showToast(fm.text, fm.type); }, 300);
})();
<?php endif; ?>
function confirmLogout() {
  showConfirmModal({
    message: '确定要退出登录吗？',
    confirmText: '退出',
    confirmClass: '',
    onConfirm: function() {
      document.getElementById('logoutForm').submit();
    }
  });
}

(function() {
  var sidebar = document.getElementById('sidebar');
  var overlay = document.getElementById('sidebarOverlay');
  var toggle = document.getElementById('sidebarToggle');
  var closeBtn = document.getElementById('sidebarClose');

  function openSidebar() {
    sidebar.classList.add('active');
    overlay.classList.add('active');
  }

  function closeSidebar() {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
  }

  toggle.addEventListener('click', openSidebar);
  closeBtn.addEventListener('click', closeSidebar);
  overlay.addEventListener('click', closeSidebar);

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSidebar();
  });
})();
</script>
