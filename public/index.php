<?php
define('ROOT_DIR', dirname(__DIR__));

date_default_timezone_set('Asia/Shanghai');

$envFile = ROOT_DIR . '/.env';
if (file_exists($envFile)) {
	$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line === '' || str_starts_with($line, '#')) continue;
		if (strpos($line, '=') === false) continue;
		$key = trim(explode('=', $line, 2)[0]);
		if (str_starts_with($key, 'DB_')) {
			putenv($line);
		}
	}
}

require_once ROOT_DIR . '/utils/functions.php';
require_once ROOT_DIR . '/utils/DB.php';
require_once ROOT_DIR . '/models/UserModel.php';
require_once ROOT_DIR . '/models/ActivityModel.php';
require_once ROOT_DIR . '/models/CommentModel.php';
require_once ROOT_DIR . '/models/VideoModel.php';

try {
	$checkScore = DB::query("SHOW COLUMNS FROM users LIKE 'score'");
	if ($checkScore->rowCount() === 0) {
		DB::query("ALTER TABLE users ADD COLUMN score INT NOT NULL DEFAULT 0 AFTER stu_class");
	}
} catch (Exception $e) {
	error_log("数据库迁移失败：" . $e->getMessage());
}

try {
	$checkContent = DB::query("SHOW COLUMNS FROM activities LIKE 'content'");
	if ($checkContent->rowCount() === 0) {
		DB::query("ALTER TABLE activities ADD COLUMN content text DEFAULT NULL AFTER activity_time");
	}
} catch (Exception $e) {
	error_log("活动表content列迁移失败：" . $e->getMessage());
}

try {
	$checkComments = DB::query("SHOW TABLES LIKE 'comments'");
	if ($checkComments->rowCount() === 0) {
		DB::query("CREATE TABLE IF NOT EXISTS `comments` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `activity_id` int(11) NOT NULL,
		  `stu_no` int(11) NOT NULL,
		  `username` varchar(255) NOT NULL,
		  `avatar` varchar(500) DEFAULT NULL,
		  `content` text NOT NULL,
		  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `idx_activity_id` (`activity_id`),
		  KEY `idx_stu_no` (`stu_no`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}
} catch (Exception $e) {
	error_log("评论表迁移失败：" . $e->getMessage());
}

// 创建说说（博客）表
try {
	$checkPosts = DB::query("SHOW TABLES LIKE 'posts'");
	if ($checkPosts->rowCount() === 0) {
		DB::query("CREATE TABLE IF NOT EXISTS `posts` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `stu_no` int(11) NOT NULL,
		  `username` varchar(255) NOT NULL,
		  `avatar` varchar(500) DEFAULT NULL,
		  `content` text NOT NULL,
		  `images` text DEFAULT NULL,
		  `location` varchar(255) DEFAULT NULL,
		  `like_count` int(11) DEFAULT 0,
		  `comment_count` int(11) DEFAULT 0,
		  `share_count` int(11) DEFAULT 0,
		  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `idx_stu_no` (`stu_no`),
		  KEY `idx_created_at` (`created_at`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}
} catch (Exception $e) {
	error_log("说说表迁移失败：" . $e->getMessage());
}

// 创建留言板表
try {
	$checkGuestbook = DB::query("SHOW TABLES LIKE 'guestbook'");
	if ($checkGuestbook->rowCount() === 0) {
		DB::query("CREATE TABLE IF NOT EXISTS `guestbook` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `target_stu_no` int(11) NOT NULL,
		  `visitor_stu_no` int(11) NOT NULL,
		  `visitor_name` varchar(255) NOT NULL,
		  `visitor_avatar` varchar(500) DEFAULT NULL,
		  `message` text NOT NULL,
		  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `idx_target_stu_no` (`target_stu_no`),
		  KEY `idx_created_at` (`created_at`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}
} catch (Exception $e) {
	error_log("留言板表迁移失败：" . $e->getMessage());
}

// 创建相册表
try {
	$checkAlbums = DB::query("SHOW TABLES LIKE 'albums'");
	if ($checkAlbums->rowCount() === 0) {
		DB::query("CREATE TABLE IF NOT EXISTS `albums` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `stu_no` int(11) NOT NULL,
		  `album_name` varchar(255) NOT NULL,
		  `cover_image` varchar(500) DEFAULT NULL,
		  `description` text DEFAULT NULL,
		  `photo_count` int(11) DEFAULT 0,
		  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `idx_stu_no` (`stu_no`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}
} catch (Exception $e) {
	error_log("相册表迁移失败：" . $e->getMessage());
}

// 创建照片表
try {
	$checkPhotos = DB::query("SHOW TABLES LIKE 'photos'");
	if ($checkPhotos->rowCount() === 0) {
		DB::query("CREATE TABLE IF NOT EXISTS `photos` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `album_id` int(11) NOT NULL,
		  `stu_no` int(11) NOT NULL,
		  `image_url` varchar(500) NOT NULL,
		  `thumbnail_url` varchar(500) DEFAULT NULL,
		  `description` text DEFAULT NULL,
		  `like_count` int(11) DEFAULT 0,
		  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `idx_album_id` (`album_id`),
		  KEY `idx_stu_no` (`stu_no`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}
} catch (Exception $e) {
	error_log("照片表迁移失败：" . $e->getMessage());
}

// 照片表添加 sort_order 字段
try {
	$checkSortOrder = DB::query("SHOW COLUMNS FROM photos LIKE 'sort_order'");
	if ($checkSortOrder->rowCount() === 0) {
		DB::query("ALTER TABLE photos ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER description");
	}
} catch (Exception $e) {
	error_log("照片表sort_order迁移失败：" . $e->getMessage());
}

// 创建说说评论表
try {
	$checkPostComments = DB::query("SHOW TABLES LIKE 'post_comments'");
	if ($checkPostComments->rowCount() === 0) {
		DB::query("CREATE TABLE IF NOT EXISTS `post_comments` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `post_id` int(11) NOT NULL,
		  `stu_no` int(11) NOT NULL,
		  `username` varchar(255) NOT NULL,
		  `avatar` varchar(500) DEFAULT NULL,
		  `content` text NOT NULL,
		  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `idx_post_id` (`post_id`),
		  KEY `idx_stu_no` (`stu_no`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}
} catch (Exception $e) {
	error_log("说说评论表迁移失败：" . $e->getMessage());
}

// 创建说说点赞表
try {
	$checkPostLikes = DB::query("SHOW TABLES LIKE 'post_likes'");
	if ($checkPostLikes->rowCount() === 0) {
		DB::query("CREATE TABLE IF NOT EXISTS `post_likes` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `post_id` int(11) NOT NULL,
		  `stu_no` int(11) NOT NULL,
		  `username` varchar(255) NOT NULL,
		  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `uniq_post_user` (`post_id`, `stu_no`),
		  KEY `idx_post_id` (`post_id`),
		  KEY `idx_stu_no` (`stu_no`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}
} catch (Exception $e) {
	error_log("说说点赞表迁移失败：" . $e->getMessage());
}

// 创建视频表
try {
	$checkVideos = DB::query("SHOW TABLES LIKE 'videos'");
	if ($checkVideos->rowCount() === 0) {
		DB::query("CREATE TABLE IF NOT EXISTS `videos` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `episode` int(11) NOT NULL DEFAULT 1,
		  `title` varchar(255) NOT NULL,
		  `description` text DEFAULT NULL,
		  `video_url` varchar(1000) NOT NULL,
		  `thumbnail` varchar(500) DEFAULT NULL,
		  `duration` varchar(20) DEFAULT NULL,
		  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `idx_episode` (`episode`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
	}
} catch (Exception $e) {
	error_log("视频表迁移失败：" . $e->getMessage());
}

if (php_sapi_name() === 'cli-server') {
	$sessionDir = ROOT_DIR . '/tmp/sessions';
	if (!is_dir($sessionDir)) {
		mkdir($sessionDir, 0700, true);
	}
	session_save_path($sessionDir);
}

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_lifetime', 30 * 24 * 3600);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
	ini_set('session.cookie_secure', 1);
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 0');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
	header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

session_start();

// 生成CSRF令牌（如果不存在）
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

$whiteList = [
	'/user/login',
	'/user/doLogin',
	'/static/css/',
	'/static/js/',
	'/static/banners/',
	'/static/fonts/',
	'/static/avatars/',
	'/static/videos/'
];

$needLogin = true;
foreach ($whiteList as $whitePath) {
	if ($path === $whitePath || str_starts_with($path, $whitePath . '/')) {
		$needLogin = false;
		break;
	}
}

if ($needLogin && !isset($_SESSION['username'])) {
	if ($path !== '/user/login') {
		become401page_login();
	}
}

$avatar = '/static/avatars/default.jpg';
if (isset($_SESSION['username'])) {
	try {
		$navbarUser = UserModel::getUserByName($_SESSION['username']);
		if (!empty($navbarUser['avatar'])) {
			$avatar = $navbarUser['avatar'];
		}
	} catch (Exception $e) {
	}
}

$routes = [
	'home' => ['index', 'users'],
	'user' => ['login', 'doLogin', 'logout', 'changepwd', 'doChangepwd', 'avatar', 'doAvatar', 'profile', 'ChangeProfile', 'homepage', 
			   'getPosts', 'getPostCount', 'createPost', 'deletePost', 'likePost', 'unlikePost', 'createPostComment', 'getPostComments', 'deletePostComment',
			   'getGuestbook', 'createGuestbookMessage', 'deleteGuestbookMessage',
			   'getAlbums', 'createAlbum', 'deleteAlbum', 'getRecentPhotos', 'getAlbumPhotos', 'uploadPhoto', 'deletePhoto', 'reorderPhotos', 'setCoverPhoto'],
	'admin' => ['index', 'createUser', 'storeUser', 'editUser', 'updateUser', 'deleteUser', 'batchDeleteUsers', 'importUsers', 'doImportUsers', 'downloadTemplate'],
	'activity' => ['index', 'show', 'create', 'store', 'edit', 'update', 'delete', 'addComment', 'deleteComment'],
	// 'video' => ['index', 'watch', 'create', 'store', 'edit', 'update', 'delete'], // 暂时禁用
];

$route = explode('/', trim($path, '/'));
$controller = isset($route[0]) && $route[0] ? $route[0] : 'home';
$action = isset($route[1]) && $route[1] ? $route[1] : 'index';

if (!isset($routes[$controller]) || !in_array($action, $routes[$controller])) {
	become404page();
}

$controllerFile = ROOT_DIR . '/controllers/' . ucfirst($controller) . 'Controller.php';
if (file_exists($controllerFile)) {
	require_once $controllerFile;
	$controllerClass = ucfirst($controller) . 'Controller';
	$obj = new $controllerClass();
	$obj->$action();
} else {
	become404page();
}
