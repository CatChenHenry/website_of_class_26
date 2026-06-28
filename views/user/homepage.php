<?php
/** @var array<string, mixed> $user1 */
// 获取当前登录用户信息（用于显示当前用户头像等）
$currentUser = [];
if (isset($_SESSION['username'])) {
    try {
        require_once ROOT_DIR . '/models/UserModel.php';
        $currentUser = UserModel::getUserByName($_SESSION['username']);
    } catch (Exception $e) {
        // 忽略错误
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>26班网站 - <?php echo htmlspecialchars($user1['name']); ?>的主页</title>
    <!-- 预连接 CDN，加速资源加载 -->
    <link rel="preconnect" href="https://cdn.bootcdn.net">
    <link rel="dns-prefetch" href="https://cdn.bootcdn.net">
    <!-- QQ空间样式 -->
    <link rel="stylesheet" href="/static/css/qzone.css">
    <link rel="stylesheet" href="/static/css/message.css">
    <!-- 图标字体（异步加载，不阻塞渲染） -->
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <!-- Markdown 样式 + 脚本（均使用国内 bootcdn，延迟加载） -->
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/KaTeX/0.16.21/katex.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/styles/github.min.css" crossorigin="anonymous">
    <script defer src="https://cdn.bootcdn.net/ajax/libs/marked/14.0.0/marked.min.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.bootcdn.net/ajax/libs/highlight.js/11.9.0/highlight.min.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.bootcdn.net/ajax/libs/KaTeX/0.16.21/katex.min.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.bootcdn.net/ajax/libs/dompurify/3.2.5/purify.min.js" crossorigin="anonymous"></script>
    <!-- 相册拖拽排序（独立文件，可缓存） -->
    <script defer src="/static/js/homepage-drag.js"></script>
    <style>
        /* 原有Markdown内容样式 */
        .markdown-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-top: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid #e7e7e7;
            line-height: 1.8;
        }
        
        .markdown-content h1, 
        .markdown-content h2, 
        .markdown-content h3 {
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin-top: 24px;
            margin-bottom: 16px;
        }
        
        .markdown-content p {
            margin-bottom: 16px;
        }
        
        .markdown-content pre {
            background: #f6f8fa;
            border-radius: 8px;
            padding: 16px;
            overflow-x: auto;
            margin: 16px 0;
        }
        
        .markdown-content code {
            background: #f6f8fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        
        .markdown-content blockquote {
            border-left: 4px solid #00a1d6;
            padding-left: 16px;
            color: #666;
            margin: 16px 0;
            font-style: italic;
        }
        
        .no-homepage-message {
            text-align: center;
            color: #aaa;
            padding: 30px 40px;
            font-size: 16px;
            background: white;
            border-radius: 12px;
            margin-top: 20px;
            display: inline-block;
            min-width: 180px;
            max-width: 100%;
            box-sizing: border-box;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid #e7e7e7;
        }
    </style>
    <script>
        // 全局变量
        window.currentUserStuNo = <?php echo $user1['stu_no'] ?? 0; ?>;
        window.currentUserName = <?php echo json_encode($user1['name'] ?? ''); ?>;
        window.currentUserAvatar = <?php echo json_encode($user1['avatar'] ?? '/static/avatars/default.jpg'); ?>;
        window.isOwnHomepage = <?php echo isset($_SESSION['username']) && $_SESSION['username'] === ($user1['name'] ?? '') ? 'true' : 'false'; ?>;
        window.loggedInUserStuNo = <?php echo isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0; ?>;
        window.isAdmin = <?php echo (isset($_SESSION['permissions']) && in_array($_SESSION['permissions'], ['admin', 'administrator'], true)) ? 'true' : 'false'; ?>;
    </script>
</head>

<body>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
    <?php require ROOT_DIR . '/views/common/navbar.php'; ?>
    
    <div class="qzone-container">
        <!-- 左侧边栏 - 个人资料卡 -->
        <div class="qzone-sidebar">
            <div class="qzone-profile-card">
                <div class="qzone-profile-header">
                    <img src="<?php echo htmlspecialchars($user1['avatar'] ?? '/static/avatars/default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($user1['name']); ?>" 
                         class="qzone-profile-avatar"
                         onerror="this.src='/static/avatars/default.jpg'">
                </div>
                <div class="qzone-profile-body">
                    <h2 class="qzone-profile-name"><?php echo htmlspecialchars($user1['name']); ?></h2>
                    
                    <div class="qzone-profile-signature" id="profile-signature">
                        <?php if (!empty($user1['signature'])): ?>
                            <?php echo $user1['signature']; ?>
                        <?php else: ?>
                            <span style="color: #999; font-style: italic;">这个人很懒，还没有签名~</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="qzone-profile-stats">
                        <div class="qzone-stat-item">
                            <div class="qzone-stat-value" id="post-count">0</div>
                            <div class="qzone-stat-label">动态</div>
                        </div>
                        <div class="qzone-stat-item">
                            <div class="qzone-stat-value" id="album-count">0</div>
                            <div class="qzone-stat-label">相册</div>
                        </div>
                    </div>
                    
                    <div class="qzone-profile-actions">
                        <?php if (isset($_SESSION['username']) && $_SESSION['username'] === $user1['name']): ?>
                            <a href="/user/profile" class="qzone-action-btn qzone-action-primary">
                                <i class="fas fa-edit"></i> 编辑资料
                            </a>
                            <a href="/user/avatar" class="qzone-action-btn qzone-action-secondary">
                                <i class="fas fa-camera"></i> 更换头像
                            </a>
                        <?php else: ?>
                            <button class="qzone-action-btn qzone-action-primary" onclick="sendMessage()">
                                <i class="fas fa-envelope"></i> 发送消息
                            </button>
                            <button class="qzone-action-btn qzone-action-secondary" onclick="followUser()">
                                <i class="fas fa-user-plus"></i> 关注
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- 相册预览 -->
            <div class="qzone-album-section" style="margin-top: 20px;">
                <div class="qzone-section-header">
                    <h3 class="qzone-section-title">
                        <i class="fas fa-images"></i> 相册
                    </h3>
                    <span class="qzone-section-more" style="font-size:12px;color:var(--qzone-text-light,#999);">
                        共 <span id="album-total-count">0</span> 个相册
                    </span>
                </div>
                <div class="qzone-album-grid" id="album-container">
                    <div class="qzone-loading">加载相册中...</div>
                </div>
            </div>
            
            <!-- 相册详情弹窗 -->
            <div class="album-modal-overlay" id="albumModal">
                <div class="album-modal-box">
                    <div class="album-modal-header">
                        <h3 id="album-modal-title">相册</h3>
                        <button class="album-modal-close" onclick="closeAlbumModal()">&times;</button>
                    </div>
                    <div class="album-modal-body" id="album-modal-body">
                        <div class="qzone-loading">加载中...</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 右侧主内容区 -->
        <div class="qzone-main-content">
            <!-- 动态发布框 -->
            <?php if (isset($_SESSION['username']) && $_SESSION['username'] === ($user1['name'] ?? '')): ?>
            <div class="qzone-post-box">
                <div class="qzone-post-header">
                    <img src="<?php echo htmlspecialchars($currentUser['avatar'] ?? '/static/avatars/default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($_SESSION['username']); ?>" 
                         class="qzone-post-avatar"
                         onerror="this.src='/static/avatars/default.jpg'">
                    <div class="qzone-post-input-container">
                        <textarea class="qzone-post-input" placeholder="分享新鲜事..."></textarea>
                    </div>
                </div>
                <div class="qzone-post-actions">
                    <div class="qzone-post-tools">
                        <span class="qzone-post-tip">图片、表情暂不支持</span>
                    </div>
                    <button class="qzone-post-submit">发布</button>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 动态列表 -->
            <div class="qzone-posts-list" id="posts-container">
                <!-- 动态将通过JavaScript动态加载 -->
                <div class="qzone-loading">
                    <div class="qzone-spinner"></div>
                </div>
            </div>
            
            <!-- 用户Markdown个人主页内容 -->
            <div id="homepage-markdown-content">
                <!-- Markdown内容将在这里渲染 -->
            </div>
            
            <!-- 留言板 -->
            <div class="qzone-guestbook-section">
                <div class="qzone-section-header">
                    <h3 class="qzone-section-title">
                        <i class="fas fa-book"></i> 留言板
                    </h3>
                </div>
                
                <?php if (isset($_SESSION['username'])): ?>
                <div class="qzone-guestbook-form">
                    <textarea class="qzone-guestbook-input" placeholder="给<?php echo htmlspecialchars($user1['name']); ?>留言..."></textarea>
                    <button class="qzone-guestbook-submit">发表留言</button>
                </div>
                <?php endif; ?>
                
                <div class="qzone-guestbook-list" id="guestbook-container">
                    <!-- 留言将通过JavaScript动态加载 -->
                    <div class="qzone-loading">
                        <div class="qzone-spinner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 渲染Markdown个人主页内容
        document.addEventListener('DOMContentLoaded', function() {
            const homepageText = <?php echo json_encode($user1['homepage']); ?>;
            const markdownContainer = document.getElementById('homepage-markdown-content');
            
            if (!homepageText || !homepageText.trim()) {
                markdownContainer.innerHTML = '<div class="no-homepage-message">Oh，此人似乎没有设置个人主页</div>';
            } else {
                marked.setOptions({
                    gfm: true,
                    breaks: true,
                    mangle: false
                });
                
                try {
                    let content = homepageText.replace(/\\\\/g, '\\');
                    let html = marked.parse(content);
                    let sanitizedHtml = DOMPurify.sanitize(html, { ADD_ATTR: ['class'] });
                    
                    // 创建Markdown内容容器
                    markdownContainer.innerHTML = '<div class="markdown-content" id="markdown-rendered"></div>';
                    const renderedDiv = document.getElementById('markdown-rendered');
                    renderedDiv.innerHTML = sanitizedHtml;
                    
                    // 代码高亮
                    if (window.hljs) {
                        hljs.highlightAll();
                    }
                    
                    // 处理数学公式
                    renderedDiv.innerHTML = DOMPurify.sanitize(renderedDiv.innerHTML.replace(
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
                    
                    renderedDiv.innerHTML = DOMPurify.sanitize(renderedDiv.innerHTML.replace(
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
                    console.error('Markdown渲染错误:', err);
                    markdownContainer.innerHTML = '<div class="no-homepage-message">个人主页内容渲染出错，请稍后重试</div>';
                }
            }
            
            // 统计数据
            document.getElementById('album-count').textContent = '0'; // 将由loadAlbums更新
            loadPostCount(); // 加载动态数量
            
            // 加载动态
            loadPosts();
            
            // 加载留言
            loadGuestbook();
            
            // 加载相册
            loadAlbums();
        });
        
        // 加载动态数量
        function loadPostCount() {
            fetch(`/user/getPostCount?stu_no=${window.currentUserStuNo}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('post-count').textContent = data.count;
                    } else {
                        console.error('获取动态数量失败:', data.error);
                        document.getElementById('post-count').textContent = '0';
                    }
                })
                .catch(error => {
                    console.error('获取动态数量失败:', error);
                    document.getElementById('post-count').textContent = '0';
                });
        }

        // 加载动态函数
        function loadPosts() {
            const postsContainer = document.getElementById('posts-container');
            if (!postsContainer) return;
            
            // 显示加载状态
            postsContainer.innerHTML = '<div class="qzone-loading">加载动态中...</div>';
            
            fetch(`/user/getPosts?stu_no=${window.currentUserStuNo}&limit=10`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.posts && data.posts.length > 0) {
                        renderPosts(postsContainer, data.posts);
                    } else {
                        postsContainer.innerHTML = '<div class="no-homepage-message" style="margin-top:0">暂无动态</div>';
                    }
                })
                .catch(error => {
                    console.error('加载动态失败:', error);
                    postsContainer.innerHTML = '<div class="no-homepage-message" style="margin-top:0">加载动态失败，<a href="javascript:loadPosts()">点击重试</a></div>';
                });
        }
        
        // 渲染动态
        function renderPosts(container, posts) {
            let html = '';
            
            posts.forEach(post => {
                const timeAgo = formatTimeAgo(post.created_at);
                const images = post.images ? JSON.parse(post.images) : [];
                
                html += `
                <div class="qzone-post-item" data-post-id="${post.id}">
                    <div class="qzone-post-item-header">
                        <img src="${post.avatar || post.user_avatar || '/static/avatars/default.jpg'}" 
                             alt="${post.username}" 
                             class="qzone-post-item-avatar"
                             onerror="this.src='/static/avatars/default.jpg'">
                        <div class="qzone-post-item-info">
                            <div class="qzone-post-item-author">${post.username}</div>
                            <div class="qzone-post-item-time">${timeAgo}</div>
                        </div>
                    </div>
                    <div class="qzone-post-item-content">
                        <span class="qzone-post-content-text">${escapeHtml(post.content)}</span>
                        ${post.content.length > 150 ? `<button class="qzone-post-more-btn" data-post-id="${post.id}">显示更多</button>` : ''}
                    </div>`;
                
                if (images.length > 0) {
                    html += `<div class="qzone-post-item-media">
                        <img src="${images[0]}" alt="动态图片">
                    </div>`;
                }
                
                html += `
                    <div class="qzone-post-item-stats">
                        <span>👍 ${post.like_count || 0} 喜欢</span>
                        <span>💬 ${post.comment_count || 0} 评论</span>
                        <span>🔄 ${post.share_count || 0} 分享</span>
                    </div>
                    <div class="qzone-post-item-actions">
                        <button class="qzone-post-action qzone-post-action-like" onclick="toggleLike(${post.id})" data-post-id="${post.id}" data-liked="false">
                            <i class="fas fa-thumbs-up"></i> 点赞
                        </button>
                        <button class="qzone-post-action" onclick="commentPost(${post.id})">
                            <i class="fas fa-comment"></i> 评论
                        </button>
                        <button class="qzone-post-action">
                            <i class="fas fa-share"></i> 分享
                        </button>
                        ${(window.isOwnHomepage || window.isAdmin) ? `<button class="qzone-post-action" onclick="deletePost(${post.id})" style="color: #dc3545;">
                            <i class="fas fa-trash"></i> 删除
                        </button>` : ''}
                    </div>
                    <div class="qzone-post-comments" id="comments-${post.id}" data-post-id="${post.id}">
                        <div class="qzone-comment-loading">加载评论中...</div>
                    </div>
                </div>`;
            });
            
            container.innerHTML = html;
            initPostContentToggle();
            loadCommentsForPosts();
        }
        
        // 动态内容展开/收起功能
        function initPostContentToggle() {
            document.querySelectorAll('.qzone-post-more-btn').forEach(button => {
                const postId = button.dataset.postId;
                const contentText = button.parentElement.querySelector('.qzone-post-content-text');
                if (!contentText) return;
                
                // 初始状态：如果内容超过150字符，则截断
                if (contentText.textContent.length > 150) {
                    contentText.classList.add('truncated');
                    button.textContent = '显示更多';
                }
                
                button.addEventListener('click', function() {
                    contentText.classList.toggle('truncated');
                    button.textContent = contentText.classList.contains('truncated') ? '显示更多' : '收起';
                });
            });
        }
        
        // 加载评论函数
        function loadCommentsForPosts() {
            document.querySelectorAll('.qzone-post-comments').forEach(container => {
                const postId = container.dataset.postId;
                if (!postId) {
                    console.error('评论容器缺少post-id属性', container);
                    return;
                }
                const postIdNum = parseInt(postId, 10);
                if (isNaN(postIdNum) || postIdNum <= 0) {
                    console.error('无效的post-id:', postId);
                    return;
                }
                loadComments(postIdNum, container);
            });
        }
        
        // 加载单个动态的评论
        function loadComments(postId, container) {
            if (!container || !container.innerHTML) {
                console.error('无效的评论容器', container);
                return;
            }
            
            fetch(`/user/getPostComments?post_id=${postId}&limit=5`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP错误 ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.comments && data.comments.length > 0) {
                        renderComments(container, data.comments, postId);
                    } else if (data.error) {
                        // API返回了错误信息
                        console.error(`加载评论API错误 (post ${postId}):`, data.error);
                        container.innerHTML = `<div class="qzone-comment-error">加载评论失败: ${escapeHtml(data.error)}</div>`;
                    } else {
                        // 没有评论
                        container.innerHTML = '<div class="qzone-comment-empty">暂无评论</div>';
                    }
                })
                .catch(error => {
                    console.error(`加载评论失败 (post ${postId}):`, error);
                    container.innerHTML = '<div class="qzone-comment-error">加载评论失败，请检查网络连接</div>';
                });
        }
        
        // 渲染评论列表
        function renderComments(container, comments, postId) {
            let html = '<div class="qzone-comment-list">';
            const totalComments = comments.length;
            const showAll = totalComments <= 5; // 如果评论数小于等于5，直接显示全部
            
            // 显示前5条评论（如果超过5条）
            const displayComments = showAll ? comments : comments.slice(0, 5);
            
            displayComments.forEach(comment => {
                const timeAgo = formatTimeAgo(comment.created_at);
                const canDeleteComment = (comment.stu_no == window.loggedInUserStuNo || window.isOwnHomepage || window.isAdmin);
                html += `
                    <div class="qzone-comment-item">
                        <img src="${comment.avatar || comment.user_avatar || '/static/avatars/default.jpg'}" 
                             alt="${comment.username}" 
                             class="qzone-comment-avatar"
                             onerror="this.src='/static/avatars/default.jpg'">
                        <div class="qzone-comment-content">
                            <div class="qzone-comment-author">${escapeHtml(comment.username)}</div>
                            <div class="qzone-comment-text">${escapeHtml(comment.content)}</div>
                            <div class="qzone-comment-time">
                                ${timeAgo}
                                ${canDeleteComment ? `<button class="qzone-comment-delete" onclick="deletePostComment(${comment.id}, ${postId})" title="删除评论"><i class="fas fa-trash"></i></button>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            
            // 如果评论超过5条，添加"点击展开更多"按钮
            if (!showAll && totalComments > 5) {
                html += `
                    <button class="qzone-comment-expand-btn" onclick="loadAllComments(${postId}, this)">
                        <i class="fas fa-chevron-down"></i>
                        点击展开更多评论 (${totalComments - 5}条)
                    </button>
                `;
            }
            
            container.innerHTML = html;
        }
        
        // 加载全部评论
        function loadAllComments(postId, button) {
            const container = document.getElementById(`comments-${postId}`);
            if (!container) return;
            
            // 显示加载状态
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 加载中...';
            button.disabled = true;
            
            fetch(`/user/getPostComments?post_id=${postId}&limit=50`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.comments && data.comments.length > 0) {
                        renderAllComments(container, data.comments);
                    } else {
                        container.innerHTML = '<div class="qzone-comment-empty">暂无评论</div>';
                    }
                })
                .catch(error => {
                    console.error(`加载全部评论失败 (post ${postId}):`, error);
                    container.innerHTML = '<div class="qzone-comment-error">加载失败，请重试</div>';
                });
        }
        
        // 渲染全部评论
        function renderAllComments(container, comments) {
            let html = '<div class="qzone-comment-list">';
            
            comments.forEach(comment => {
                const timeAgo = formatTimeAgo(comment.created_at);
                html += `
                    <div class="qzone-comment-item">
                        <img src="${comment.avatar || comment.user_avatar || '/static/avatars/default.jpg'}" 
                             alt="${comment.username}" 
                             class="qzone-comment-avatar"
                             onerror="this.src='/static/avatars/default.jpg'">
                        <div class="qzone-comment-content">
                            <div class="qzone-comment-author">${escapeHtml(comment.username)}</div>
                            <div class="qzone-comment-text">${escapeHtml(comment.content)}</div>
                            <div class="qzone-comment-time">${timeAgo}</div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }
        
        // 加载留言函数
        function loadGuestbook() {
            const guestbookContainer = document.getElementById('guestbook-container');
            if (!guestbookContainer) return;
            
            // 显示加载状态
            guestbookContainer.innerHTML = '<div class="qzone-loading">加载留言中...</div>';
            
            fetch(`/user/getGuestbook?stu_no=${window.currentUserStuNo}&limit=10`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages && data.messages.length > 0) {
                        renderGuestbook(guestbookContainer, data.messages);
                    } else {
                        guestbookContainer.innerHTML = '<div class="no-homepage-message" style="margin-top:0">暂无留言</div>';
                    }
                })
                .catch(error => {
                    console.error('加载留言失败:', error);
                    guestbookContainer.innerHTML = '<div class="no-homepage-message" style="margin-top:0">加载留言失败，<a href="javascript:loadGuestbook()">点击重试</a></div>';
                });
        }
        
        // 加载相册函数
        function loadAlbums() {
            const albumContainer = document.getElementById('album-container');
            if (!albumContainer) return;
            
            albumContainer.innerHTML = '<div class="qzone-loading">加载相册中...</div>';
            
            fetch(`/user/getAlbums?stu_no=${window.currentUserStuNo}&limit=6`)
                .then(response => response.json())
                .then(data => {
                    const albums = (data.success && data.albums) ? data.albums : [];
                    renderAlbums(albumContainer, albums);
                    document.getElementById('album-count').textContent = albums.length;
                    document.getElementById('album-total-count').textContent = albums.length;
                })
                .catch(error => {
                    console.error('加载相册失败:', error);
                    albumContainer.innerHTML = '<div class="no-homepage-message" style="margin-top:0">加载相册失败，<a href="javascript:loadAlbums()">点击重试</a></div>';
                });
        }
        
        // 渲染相册
        function renderAlbums(container, albums) {
            let html = '';
            
            // 创建相册卡片（仅主人可见）
            if (window.isOwnHomepage) {
                html += `
                <div class="qzone-album-create" onclick="showCreateAlbum()">
                    <span class="qzone-album-create-icon">+</span>
                    <span>创建相册</span>
                </div>`;
            }
            
            albums.forEach(album => {
                const hasCover = album.cover_image && album.cover_image.trim();
                const photoCount = album.photo_count || 0;
                const albumName = album.album_name || '未命名相册';
                const coverStyle = hasCover
                    ? `style="background-image:url(${hasCover});background-size:cover;background-position:center"`
                    : 'style="background:#fff"';
                
                html += `
                <div class="qzone-album-item" ${coverStyle} data-album-id="${album.id}" data-album-name="${escapeHtml(albumName)}">
                    ${hasCover ? '' : `<div class="qzone-album-empty"><i class="fas fa-images"></i></div>`}
                    <div class="qzone-album-overlay">${escapeHtml(albumName)}</div>
                    ${photoCount > 0 ? `<div class="qzone-album-count">${photoCount}张</div>` : ''}
                    ${window.isOwnHomepage ? `<button class="qzone-album-delete-btn" title="删除" onclick='deleteAlbum(${album.id},${JSON.stringify(albumName)})'><i class="fas fa-trash"></i></button>` : ''}
                </div>`;
            });
            container.innerHTML = html;
            
            // 事件委托：点击相册卡片打开弹窗，排除删除按钮
            container.querySelectorAll('.qzone-album-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    if (e.target.closest('.qzone-album-delete-btn')) return;
                    const id = this.dataset.albumId;
                    const name = this.dataset.albumName;
                    if (id) openAlbumModal(parseInt(id), name);
                });
            });
        }
        
        // 删除相册
        function deleteAlbum(albumId, albumName) {
            showConfirmModal({
                message: '确定删除相册「' + albumName + '」及其中所有照片吗？',
                confirmText: '确定删除',
                onConfirm: function() {
                    const formData = new FormData();
                    formData.append('album_id', albumId);
                    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
                    
                    fetch('/user/deleteAlbum', {
                        method: 'POST',
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast('相册已删除', 'success');
                            loadAlbums();
                        } else {
                            showToast(data.error || '删除失败', 'error');
                        }
                    });
                }
            });
        }
        
        // 打开相册弹窗
        function openAlbumModal(albumId, albumName) {
            document.getElementById('album-modal-title').textContent = albumName;
            document.getElementById('album-modal-body').innerHTML = '<div class="qzone-loading">加载照片中...</div>';
            document.getElementById('albumModal').classList.add('active');
            
            fetch(`/user/getAlbumPhotos?album_id=${albumId}`)
                .then(response => response.json())
                .then(data => {
                    renderAlbumPhotos(albumId, data);
                })
                .catch(() => {
                    document.getElementById('album-modal-body').innerHTML = '<div class="no-homepage-message">加载失败</div>';
                });
        }
        
        // 关闭相册弹窗
        function closeAlbumModal() {
            document.getElementById('albumModal').classList.remove('active');
        }
        
        // 当前打开的相册ID（用于拖拽排序）
        let currentAlbumId = null;

        // 渲染相册照片
        function renderAlbumPhotos(albumId, data) {
            const body = document.getElementById('album-modal-body');
            currentAlbumId = albumId;
            if (!data.success) {
                body.innerHTML = `<div class="no-homepage-message">${data.error || '加载失败'}</div>`;
                return;
            }
            
            const album = data.album;
            const photos = data.photos || [];
            const isOwner = album && album.stu_no == window.loggedInUserStuNo;
            const coverUrl = album ? album.cover_image : '';
            
            let html = '';
            
            // 上传区域 + 排序提示（仅主人可见）
            if (isOwner) {
                html += `
                <div class="album-upload-area">
                    <form id="album-upload-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="${document.querySelector('input[name=csrf_token]')?.value || ''}">
                        <input type="file" id="album-photo-input" name="photo" accept="image/*" style="display:none" onchange="handlePhotoUpload(${albumId}, this)">
                        <button type="button" class="qzone-btn qzone-btn-primary qzone-btn-sm" onclick="document.getElementById('album-photo-input').click()">
                            <i class="fas fa-cloud-upload-alt"></i> 上传照片
                        </button>
                        <span style="font-size:12px;color:var(--qzone-text-light,#999);margin-left:8px;">支持 JPG/PNG/GIF/WebP，单张≤10MB</span>
                    </form>
                    ${photos.length > 1 ? `<span style="font-size:12px;color:#888;margin-left:12px;">💡 拖拽照片可调整顺序</span>` : ''}
                </div>`;
            }
            
            // 照片网格
            if (photos.length > 0) {
                html += `<div class="album-photo-grid" id="album-photo-grid" data-album-id="${albumId}">`;
                photos.forEach((photo, index) => {
                    const isCover = photo.image_url === coverUrl;
                    html += `
                    <div class="album-photo-item${isOwner ? ' draggable' : ''}" data-photo-id="${photo.id}" data-index="${index}">
                        ${isCover ? '<div class="album-photo-cover-badge" title="当前封面">封面</div>' : ''}
                        <img src="${photo.thumbnail_url || photo.image_url}" alt="照片" loading="lazy" onerror="this.src='/static/banners/banner1.jpg'" onclick="previewPhoto('${photo.image_url}')">
                        ${isOwner ? `
                        <div class="album-photo-actions">
                            ${!isCover ? `<button class="album-photo-cover-btn" title="设为封面" onclick="setCoverPhoto(${photo.id}, '${photo.image_url}')">设为封面</button>` : ''}
                            <button class="album-photo-delete-btn" title="删除" onclick="deleteAlbumPhoto(${photo.id}, ${albumId})">删除</button>
                        </div>` : ''}
                    </div>`;
                });
                html += '</div>';
            } else {
                html += '<div class="no-homepage-message" style="margin-top:0">暂无照片</div>';
            }
            
            body.innerHTML = html;
            
            // 初始化拖拽排序
            if (isOwner && photos.length > 1) {
                initDragSort(albumId);
            }
        }
        
        // 设置封面照片
        function setCoverPhoto(photoId, imageUrl) {
            fetch('/user/setCoverPhoto', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'csrf_token=' + encodeURIComponent(document.querySelector('input[name="csrf_token"]')?.value || '') +
                      '&photo_id=' + photoId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('封面已更新', 'success');
                    // 重新渲染相册照片
                    fetch(`/user/getAlbumPhotos?album_id=${currentAlbumId}`)
                        .then(r => r.json())
                        .then(d => renderAlbumPhotos(currentAlbumId, d));
                    loadAlbums();
                } else {
                    showToast(data.error || '设置失败', 'error');
                }
            })
            .catch(() => showToast('设置失败', 'error'));
        }
        
        // 上传照片处理
        function handlePhotoUpload(albumId, input) {
            const file = input.files[0];
            if (!file) return;
            
            if (file.size > 10 * 1024 * 1024) {
                showToast('图片大小不能超过10MB', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('album_id', albumId);
            formData.append('photo', file);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
            
            // 显示上传中
            const body = document.getElementById('album-modal-body');
            body.innerHTML = '<div class="qzone-loading">上传中...</div>';
            
            fetch('/user/uploadPhoto', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('上传成功', 'success');
                    openAlbumModal(albumId, document.getElementById('album-modal-title').textContent);
                    loadAlbums(); // 刷新侧边栏
                } else {
                    showToast(data.error || '上传失败', 'error');
                    openAlbumModal(albumId, document.getElementById('album-modal-title').textContent);
                }
            })
            .catch(() => {
                showToast('上传失败', 'error');
                openAlbumModal(albumId, document.getElementById('album-modal-title').textContent);
            });
        }
        
        // 删除照片
        function deleteAlbumPhoto(photoId, albumId) {
            showConfirmModal({
                message: '确定要删除这张照片吗？',
                confirmText: '确定删除',
                onConfirm: function() {
                    const formData = new FormData();
                    formData.append('photo_id', photoId);
                    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
                    
                    fetch('/user/deletePhoto', {
                        method: 'POST',
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast('已删除', 'success');
                            openAlbumModal(albumId, document.getElementById('album-modal-title').textContent);
                            loadAlbums();
                        } else {
                            showToast(data.error || '删除失败', 'error');
                        }
                    });
                }
            });
        }
        
        // 预览大图
        function previewPhoto(url) {
            const overlay = document.createElement('div');
            overlay.className = 'photo-preview-overlay';
            overlay.innerHTML = `<img src="${url}" class="photo-preview-img">`;
            overlay.onclick = function(){ overlay.remove(); };
            document.body.appendChild(overlay);
        }
        
        // 创建相册
        function showCreateAlbum() {
            showInputModal({
                title: '创建相册',
                placeholder: '请输入相册名称',
                defaultValue: '默认相册',
                confirmText: '创建',
                onConfirm: function(name) {
                    const formData = new FormData();
                    formData.append('album_name', name);
                    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
                    
                    fetch('/user/createAlbum', {
                        method: 'POST',
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast('相册创建成功', 'success');
                            loadAlbums();
                        } else {
                            showToast(data.error || '创建失败', 'error');
                        }
                    })
                    .catch(() => showToast('创建失败', 'error'));
                }
            });
        }
        
        // 兼容 escapeHtml（如果未定义）
        if (typeof escapeHtml !== 'function') {
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        }
        
        // 渲染留言
        function renderGuestbook(container, messages) {
            let html = '';
            
            messages.forEach(message => {
                const timeAgo = formatTimeAgo(message.created_at);
                
                html += `
                <div class="qzone-guestbook-item" data-message-id="${message.id}">
                    <img src="${message.visitor_avatar || '/static/avatars/default.jpg'}" 
                         alt="${message.visitor_name}" 
                         class="qzone-guestbook-avatar"
                         onerror="this.src='/static/avatars/default.jpg'">
                    <div class="qzone-guestbook-content">
                        <div class="qzone-guestbook-header">
                            <span class="qzone-guestbook-author">${message.visitor_name}</span>
                            <span class="qzone-guestbook-time">${timeAgo}</span>
                        </div>
                        <div class="qzone-guestbook-message">${escapeHtml(message.message)}</div>
                        ${(window.isOwnHomepage || message.visitor_stu_no == window.loggedInUserStuNo || window.isAdmin) ? 
                          `<button class="qzone-post-action" onclick="deleteGuestbookMessage(${message.id})" style="color: #dc3545; margin-top:8px; padding:4px 8px; font-size:12px;">
                            <i class="fas fa-trash"></i> 删除
                          </button>` : ''}
                    </div>
                </div>`;
            });
            
            container.innerHTML = html;
        }
        
        // 工具函数：格式化时间
        function formatTimeAgo(timestamp) {
            const now = new Date();
            // 后端返回的格式为 "YYYY-MM-DD HH:MM:SS"，无时区后缀，
            // 需要明确指定东八区，否则浏览器会当作 UTC 解析，导致偏8小时
            const date = new Date(timestamp.replace(' ', 'T') + '+08:00');
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return '刚刚';
            if (diffMins < 60) return `${diffMins}分钟前`;
            if (diffHours < 24) return `${diffHours}小时前`;
            if (diffDays < 30) return `${diffDays}天前`;
            if (diffDays < 365) return `${Math.floor(diffDays / 30)}个月前`;
            return `${Math.floor(diffDays / 365)}年前`;
        }
        
        // 工具函数：HTML转义
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // 更新点赞计数
        function updateLikeCount(postId, delta) {
            const postElement = document.querySelector(`.qzone-post-item[data-post-id="${postId}"]`);
            if (!postElement) return;
            
            const likeCountSpan = postElement.querySelector('.qzone-post-item-stats span:first-child');
            if (!likeCountSpan) return;
            
            const currentText = likeCountSpan.textContent;
            const match = currentText.match(/👍 (\d+) 喜欢/);
            if (match) {
                const currentCount = parseInt(match[1]) || 0;
                const newCount = Math.max(0, currentCount + delta);
                likeCountSpan.textContent = `👍 ${newCount} 喜欢`;
            }
        }
        
        // 更新点赞按钮状态
        function updateLikeButton(button, liked) {
            if (!button) return;
            
            const icon = button.querySelector('i');
            if (icon) {
                if (liked) {
                    icon.className = 'fas fa-thumbs-up';
                    button.innerHTML = '<i class="fas fa-thumbs-up"></i> 已赞';
                    button.classList.add('active');
                    button.setAttribute('data-liked', 'true');
                } else {
                    icon.className = 'fas fa-thumbs-up';
                    button.innerHTML = '<i class="fas fa-thumbs-up"></i> 点赞';
                    button.classList.remove('active');
                    button.setAttribute('data-liked', 'false');
                }
            }
        }
        
        // 点赞/取消点赞切换
        function toggleLike(postId) {
            const button = document.querySelector(`.qzone-post-action-like[data-post-id="${postId}"]`);
            if (!button) return;
            
            const isLiked = button.getAttribute('data-liked') === 'true';
            const endpoint = isLiked ? '/user/unlikePost' : '/user/likePost';
            const action = isLiked ? '取消点赞' : '点赞';
            
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
            
            fetch(endpoint, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 更新点赞计数
                    const delta = isLiked ? -1 : +1;
                    updateLikeCount(postId, delta);
                    
                    // 更新按钮状态
                    updateLikeButton(button, !isLiked);
                    
                    showToast(`${action}成功`, 'success');
                } else {
                    // 如果已点赞，特殊处理重复点赞提示
                    if (data.error && data.error.includes('已经点过赞了')) {
                        // 确保按钮状态正确
                        updateLikeButton(button, true);
                        showToast('您已经点过赞了', 'info');
                    } else {
                        showToast(data.error || `${action}失败`, 'error');
                    }
                }
            })
            .catch(error => {
                console.error(`${action}失败:`, error);
                showToast(`${action}失败`, 'error');
            });
        }
        
        // 兼容原有likePost函数（供其他地方调用）
        function likePost(postId) {
            toggleLike(postId);
        }
        
        // 评论动态
        function commentPost(postId) {
            showCommentModal({
                placeholder: '请输入评论内容...',
                confirmText: '发表评论',
                onConfirm: function(commentText) {
                    const formData = new FormData();
                    formData.append('post_id', postId);
                    formData.append('content', commentText.trim());
                    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
                    
                    fetch('/user/createPostComment', {
                        method: 'POST',
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 更新前端评论计数
                            const postElement = document.querySelector(`.qzone-post-item[data-post-id="${postId}"]`);
                            if (postElement) {
                                const commentCountSpan = postElement.querySelector('.qzone-post-item-stats span:nth-child(2)');
                                if (commentCountSpan) {
                                    const currentText = commentCountSpan.textContent;
                                    const match = currentText.match(/💬 (\d+) 评论/);
                                    if (match) {
                                        const currentCount = parseInt(match[1]) || 0;
                                        commentCountSpan.textContent = `💬 ${currentCount + 1} 评论`;
                                    }
                                }
                            }
                            // 重新加载评论列表
                            const commentContainer = document.getElementById(`comments-${postId}`);
                            if (commentContainer) {
                                loadComments(postId, commentContainer);
                            }
                            showToast('评论成功', 'success');
                        } else {
                            showToast(data.error || '评论失败', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('评论失败:', error);
                        showToast('评论失败', 'error');
                    });
                }
            });
        }
        
        // 删除评论
        function deletePostComment(commentId, postId) {
            showConfirmModal({
                message: '确定要删除这条评论吗？',
                confirmText: '确定删除',
                confirmClass: 'btn-confirm-primary',
                onConfirm: function() {
                    const formData = new FormData();
                    formData.append('comment_id', commentId);
                    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
                    
                    fetch('/user/deletePostComment', {
                        method: 'POST',
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('评论已删除', 'success');
                            // 重新加载评论列表，更新计数
                            const commentContainer = document.getElementById(`comments-${postId}`);
                            if (commentContainer) {
                                loadComments(postId, commentContainer);
                            }
                            // 更新评论计数
                            const postElement = document.querySelector(`.qzone-post-item[data-post-id="${postId}"]`);
                            if (postElement) {
                                const commentCountSpan = postElement.querySelector('.qzone-post-item-stats span:nth-child(2)');
                                if (commentCountSpan) {
                                    const currentText = commentCountSpan.textContent;
                                    const match = currentText.match(/💬 (\d+) 评论/);
                                    if (match) {
                                        const currentCount = parseInt(match[1]) || 1;
                                        const newCount = Math.max(0, currentCount - 1);
                                        commentCountSpan.textContent = `💬 ${newCount} 评论`;
                                    }
                                }
                            }
                        } else {
                            showToast(data.error || '删除失败', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('删除评论失败:', error);
                        showToast('删除评论失败', 'error');
                    });
                }
            });
        }
        
        // 删除动态
        function deletePost(postId) {
            showConfirmModal({
                message: '确定要删除这条动态吗？',
                confirmText: '确定删除',
                confirmClass: 'btn-confirm-primary',
                onConfirm: function() {
                    const formData = new FormData();
                    formData.append('post_id', postId);
                    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
                    
                    fetch('/user/deletePost', {
                        method: 'POST',
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('删除成功', 'success');
                            document.querySelector(`.qzone-post-item[data-post-id="${postId}"]`)?.remove();
                            // 更新动态数量统计
                            loadPostCount();
                            
                            // 检查是否还有动态，如果没有则显示"暂无动态"
                            const postsContainer = document.getElementById('posts-container');
                            if (postsContainer && postsContainer.querySelectorAll('.qzone-post-item').length === 0) {
                                postsContainer.innerHTML = '<div class="no-homepage-message" style="margin-top:0">暂无动态</div>';
                            }
                        } else {
                            showToast(data.error || '删除失败', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('删除失败:', error);
                        showToast('删除失败', 'error');
                    });
                },
                onCancel: function() {
                    // 用户取消，不做任何操作
                }
            });
        }
        
        // 删除留言
        function deleteGuestbookMessage(messageId) {
            showConfirmModal({
                message: '确定要删除这条留言吗？',
                confirmText: '确定删除',
                confirmClass: 'btn-confirm-primary',
                onConfirm: function() {
                    const formData = new FormData();
                    formData.append('message_id', messageId);
                    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
                    
                    fetch('/user/deleteGuestbookMessage', {
                        method: 'POST',
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('删除成功', 'success');
                            document.querySelector(`.qzone-guestbook-item[data-message-id="${messageId}"]`)?.remove();
                            
                            // 检查是否还有留言，如果没有则显示"暂无留言"
                            const guestbookContainer = document.getElementById('guestbook-container');
                            if (guestbookContainer && guestbookContainer.querySelectorAll('.qzone-guestbook-item').length === 0) {
                                guestbookContainer.innerHTML = '<div class="no-homepage-message" style="margin-top:0">暂无留言</div>';
                            }
                        } else {
                            showToast(data.error || '删除失败', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('删除失败:', error);
                        showToast('删除失败', 'error');
                    });
                },
                onCancel: function() {
                    // 用户取消，不做任何操作
                }
            });
        }
        
        // 交互函数
        function sendMessage() {
            showToast('消息功能开发中...', 'info');
        }
        
        function followUser() {
            showToast('关注功能开发中...', 'info');
        }
        
        // 动态发布功能
        document.querySelector('.qzone-post-submit')?.addEventListener('click', function() {
            const textarea = document.querySelector('.qzone-post-input');
            const content = textarea.value.trim();
            
            if (!content) {
                showToast('请输入内容', 'warning');
                return;
            }
            
            const formData = new FormData();
            formData.append('content', content);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
            
            fetch('/user/createPost', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('发布成功！', 'success');
                    textarea.value = '';
                    // 重新加载动态
                    loadPosts();
                    // 更新动态数量统计
                    loadPostCount();
                } else {
                    showToast(data.error || '发布失败', 'error');
                }
            })
            .catch(error => {
                console.error('发布失败:', error);
                showToast('发布失败', 'error');
            });
        });
        
        // 动态发布工具按钮功能
        document.querySelectorAll('.qzone-post-tool').forEach(button => {
            button.addEventListener('click', function() {
                const toolText = this.textContent.trim();
                showToast(`${toolText}功能开发中...`, 'info');
            });
        });
        
        // 动态输入框字符计数
        const postTextarea = document.querySelector('.qzone-post-input');
        if (postTextarea) {
            // 创建字符计数显示
            const charCounter = document.createElement('div');
            charCounter.className = 'qzone-char-counter';
            postTextarea.parentNode.appendChild(charCounter);
            
            function updateCharCounter() {
                const length = postTextarea.value.length;
                charCounter.textContent = `${length}/500`;
                // 更新CSS类
                charCounter.classList.remove('warning', 'error');
                if (length > 500) {
                    charCounter.classList.add('error');
                } else if (length > 400) {
                    charCounter.classList.add('warning');
                }
            }
            
            postTextarea.addEventListener('input', updateCharCounter);
            updateCharCounter(); // 初始化
        }
        
        // 留言提交功能
        document.querySelector('.qzone-guestbook-submit')?.addEventListener('click', function() {
            const textarea = document.querySelector('.qzone-guestbook-input');
            const content = textarea.value.trim();
            
            if (!content) {
                showToast('请输入留言内容', 'warning');
                return;
            }
            
            const formData = new FormData();
            formData.append('target_stu_no', window.currentUserStuNo);
            formData.append('message', content);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
            
            fetch('/user/createGuestbookMessage', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('留言发表成功！', 'success');
                    textarea.value = '';
                    // 重新加载留言
                    loadGuestbook();
                } else {
                    showToast(data.error || '留言失败', 'error');
                }
            })
            .catch(error => {
                console.error('留言失败:', error);
                showToast('留言失败', 'error');
            });
        });
        
        // 点赞功能
        document.querySelectorAll('.qzone-post-action').forEach(button => {
            if (button.textContent.includes('点赞')) {
                button.addEventListener('click', function() {
                    const isActive = this.classList.contains('active');
                    if (isActive) {
                        this.classList.remove('active');
                        this.innerHTML = '<i class="fas fa-thumbs-up"></i> 点赞';
                        showToast('取消点赞', 'info');
                    } else {
                        this.classList.add('active');
                        this.innerHTML = '<i class="fas fa-thumbs-up"></i> 已赞';
                        showToast('点赞成功！', 'success');
                    }
                });
            }
        });
    </script>
    <!-- 全局提示框脚本（与管理员控制台一致） -->
    <script src="/static/js/message.js"></script>
</body>
</html>