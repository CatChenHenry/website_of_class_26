<?php
define('ROOT_DIR', dirname(__DIR__));

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

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

$whiteList = [
	'/user/login',
	'/user/doLogin',
	'/static/css/',
	'/static/js/',
	'/static/banners/',
	'/static/fonts/',
	'/static/avatars/'
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
	'user' => ['login', 'doLogin', 'logout', 'changepwd', 'doChangepwd', 'avatar', 'doAvatar', 'profile', 'ChangeProfile', 'homepage'],
	'admin' => ['index', 'createUser', 'storeUser', 'editUser', 'updateUser', 'deleteUser', 'batchDeleteUsers', 'importUsers', 'doImportUsers', 'downloadTemplate'],
	'activity' => ['index', 'show', 'create', 'store', 'edit', 'update', 'delete', 'addComment', 'deleteComment'],
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
