<?php
require_once ROOT_DIR . '/models/UserModel.php';
require_once ROOT_DIR . '/models/PostModel.php';
require_once ROOT_DIR . '/models/GuestbookModel.php';
require_once ROOT_DIR . '/models/AlbumModel.php';
require_once ROOT_DIR . '/models/PhotoModel.php';
require_once ROOT_DIR . '/models/PostCommentModel.php';

class UserController
{
	public function login()
	{
		$title = "26班网站 - 登录";
		require ROOT_DIR . '/views/user/login.php';
	}

	public function doLogin()
	{
		if ($_SERVER["REQUEST_METHOD"] !== "POST") {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '请求方法不正确！'];
			header("Location: /user/login");
			exit;
		}
		verifyCsrfToken();
		if (!isset($_POST["username"]) || !isset($_POST["password"]) || trim($_POST["username"]) === '' || trim($_POST["password"]) === '') {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '用户名或密码不能为空！'];
			header("Location: /user/login");
			exit;
		}
		$username = trim($_POST["username"]);
		$password = $_POST["password"];
		$clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
		if (!checkLoginAttempts($username, $clientIp)) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '登录失败次数过多，请5分钟后再试！'];
			header("Location: /user/login");
			exit;
		}
		try {
			$user = UserModel::login($username, $password);
			if ($user) {
				recordLoginAttempt($username, $clientIp, true);
				session_regenerate_id(true);
				$_SESSION['id'] = $user['stu_no'];
				$_SESSION['username'] = $user['name'];
				$_SESSION['permissions'] = $user['permissions'];
				$_SESSION['signature'] = $user['signature'];
				$_SESSION['homepage'] = $user['homepage'];
				$_SESSION['flash_message'] = ['type' => 'success', 'text' => '登录成功！'];
				header("Location: /home");
				exit;
			} else {
				recordLoginAttempt($username, $clientIp, false);
				$_SESSION['flash_message'] = ['type' => 'error', 'text' => '登录失败！'];
				header("Location: /user/login");
				exit;
			}
		} catch (Exception $e) {
			error_log("登录异常：" . $e->getMessage());
			die("系统错误，请稍后重试。<br><a href='/user/login'>返回</a>");
		}
	}

	public function logout()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '无效的请求！'];
			header("Location: /home");
			exit;
		}
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '无效的请求！'];
			header("Location: /home");
			exit;
		}
		$_SESSION = [];
		session_destroy();
		header("Location: /user/login");
		exit;
	}

	public function changepwd()
	{
		$title = "更新密码";
		require ROOT_DIR . '/views/user/changepwd.php';
	}

	public function doChangepwd()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '请求方法不正确！'];
			header("Location: /user/changepwd");
			exit;
		}
		verifyCsrfToken();
		$StuNo = $_SESSION['id'];
		$username = $_SESSION['username'];
		if (!checkChangepwdAttempts($StuNo)) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '密码修改失败次数过多，请5分钟后再试！'];
			header("Location: /user/changepwd");
			exit;
		}
		if (!isset($_POST['oldpwd']) || !isset($_POST['newpwd']) || !isset($_POST['repeatpwd'])) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '请填写完整信息！'];
			header("Location: /user/changepwd");
			exit;
		}
		$od = $_POST['oldpwd'];
		$nw = $_POST['newpwd'];
		$rp = $_POST['repeatpwd'];
		if ($nw !== $rp) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '新密码与重复的新密码不一致！'];
			header("Location: /user/changepwd");
			exit;
		}
		if ($od === $nw) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '新密码不能与旧密码相同！'];
			header("Location: /user/changepwd");
			exit;
		}
		if (strlen($nw) < 6) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '新密码长度不能少于6位！'];
			header("Location: /user/changepwd");
			exit;
		}
		try {
			$user = UserModel::login($username, $od);
			if ($user) {
				recordChangepwdAttempt($StuNo, true);
				try {
					$miao = UserModel::changepwd($StuNo, $nw);
					if ($miao) {
						$_SESSION['flash_message'] = ['type' => 'success', 'text' => '更改成功！'];
						header("Location: /home");
						exit;
					} else {
						$_SESSION['flash_message'] = ['type' => 'error', 'text' => '更改失败！'];
						header("Location: /user/changepwd");
						exit;
					}
				} catch (Exception $e) {
					error_log("修改密码异常：" . $e->getMessage());
					die("系统错误，请稍后重试。<br><a href='/user/changepwd'>返回</a>");
				}
			} else {
				recordChangepwdAttempt($StuNo, false);
				$_SESSION['flash_message'] = ['type' => 'error', 'text' => '原密码错误！'];
				header("Location: /user/changepwd");
				exit;
			}
		} catch (Exception $e) {
			error_log("修改密码异常：" . $e->getMessage());
			die("系统错误，请稍后重试。<br><a href='/user/changepwd'>返回</a>");
		}
	}

	public function avatar()
	{
		$user = UserModel::getUserByStuNo($_SESSION['id']);
		$avatar = $user['avatar'] ?? '/static/avatars/default.jpg';
		require ROOT_DIR . '/views/user/avatar.php';
	}

	public function doAvatar()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '请求方法不正确！'];
			header("Location: /user/avatar");
			exit;
		}
		verifyCsrfToken();
		$stuNo = $_SESSION['id'];
		$username = $_SESSION['username'];
		if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '文件上传失败！'];
			header("Location: /user/avatar");
			exit;
		}
		$file = $_FILES['avatar'];
		$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
		$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
		$maxSize = 2 * 1024 * 1024;
		if ($file['size'] > $maxSize) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '图片大小不能超过2MB！'];
			header("Location: /user/avatar");
			exit;
		}
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if (!in_array($ext, $allowedExtensions)) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '仅支持JPG/PNG/GIF格式！'];
			header("Location: /user/avatar");
			exit;
		}
		$fileType = mime_content_type($file['tmp_name']);
		if (!in_array($fileType, $allowedMimeTypes)) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '仅支持JPG/PNG/GIF格式！'];
			header("Location: /user/avatar");
			exit;
		}
		$expectedMimeMap = [
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif'
		];
		if (isset($expectedMimeMap[$ext]) && $expectedMimeMap[$ext] !== $fileType) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '文件类型与扩展名不匹配！'];
			header("Location: /user/avatar");
			exit;
		}
		$imageInfo = @getimagesize($file['tmp_name']);
		if ($imageInfo === false) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '文件不是有效的图片！'];
			header("Location: /user/avatar");
			exit;
		}
		$safeName = preg_replace('/[^a-zA-Z0-9_\x{4e00}-\x{9fff}]/u', '', $username);
		$newFileName = $safeName . '_' . uniqid() . '.' . $ext;
		$uploadPath = ROOT_DIR . '/public/static/avatars/' . $newFileName;
		$avatarPath = '/static/avatars/' . $newFileName;
		if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '头像保存失败！上传目录可能没有写入权限。'];
			header("Location: /user/avatar");
			exit;
		}
		$oldUser = UserModel::getUserByStuNo($stuNo);
		$oldAvatar = $oldUser['avatar'] ?? '';
		$updateResult = UserModel::updateAvatar($stuNo, $avatarPath);
		if ($updateResult) {
			if (!empty($oldAvatar) && $oldAvatar !== $avatarPath && $oldAvatar !== '/static/avatars/default.jpg') {
				$oldAvatarPath = ROOT_DIR . '/public' . $oldAvatar;
				$realBase = realpath(ROOT_DIR . '/public/static/avatars');
				$realPath = realpath($oldAvatarPath);
				if ($realBase && $realPath && str_starts_with($realPath, $realBase) && basename($realPath) !== 'default.jpg' && file_exists($realPath)) {
					@unlink($realPath);
				}
			}
			$_SESSION['flash_message'] = ['type' => 'success', 'text' => '头像上传成功！'];
			header("Location: /user/avatar");
			exit;
		} else {
			@unlink($uploadPath);
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '头像上传失败，请重试！'];
			header("Location: /user/avatar");
			exit;
		}
	}

	public function profile()
	{
		$profileUser = UserModel::getUserByStuNo($_SESSION['id']);
		$signature_content = $profileUser['signature'] ?? '';
		$homepage_content = $profileUser['homepage'] ?? '';
		require ROOT_DIR . '/views/user/profile.php';
	}

	public function homepage()
	{
		if (!isset($_GET['stu_no']) || empty($_GET['stu_no'])) {
			become404page();
		}
		$user1 = UserModel::getUserByStuNo((int) $_GET['stu_no']);
		if (!$user1) {
			become404page();
		}
		require ROOT_DIR . '/views/user/homepage.php';
	}

	public function ChangeProfile()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '请求方法不正确！'];
			header("Location: /user/profile");
			exit;
		}
		verifyCsrfToken();
		if (!isset($_POST['signature']) || !isset($_POST['homepage'])) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '请填写完整信息！'];
			header("Location: /user/profile");
			exit;
		}
		$signature = sanitizeHtml(trim($_POST['signature']));
		$sigPlainText = strip_tags($signature);
		if (mb_strlen($sigPlainText, 'utf-8') > 15) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '个性签名纯文本内容不能超过15个字符！'];
			header("Location: /user/profile");
			exit;
		}
		$homepage = sanitizeHtml(mb_substr($_POST['homepage'], 0, 10000, 'utf-8'));
		$stuNo = $_SESSION['id'];
		$miao = UserModel::updateProfile($stuNo, $signature, $homepage);
		if ($miao) {
			$_SESSION['signature'] = $signature;
			$_SESSION['homepage'] = $homepage;
			$_SESSION['flash_message'] = ['type' => 'success', 'text' => '更改成功！'];
			header("Location: /home");
			exit;
		} else {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '更改失败！'];
			header("Location: /user/profile");
			exit;
		}
	}
	
	// 获取用户动态列表 (API)
	public function getPosts()
	{
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		$stuNo = $_GET['stu_no'] ?? $_SESSION['id'];
		$limit = $_GET['limit'] ?? 20;
		$offset = $_GET['offset'] ?? 0;
		
		try {
			$posts = PostModel::getPostsByUser($stuNo, $limit, $offset);
			header('Content-Type: application/json');
			echo json_encode(['success' => true, 'posts' => $posts]);
		} catch (Exception $e) {
			error_log('ERROR getPosts: ' . $e->getMessage() . ' for stuNo=' . $stuNo);
			header('Content-Type: application/json');
			echo json_encode(['error' => '获取动态失败: ' . $e->getMessage()]);
		}
	}

	// 获取用户动态数量 (API)
	public function getPostCount()
	{
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		$stuNo = $_GET['stu_no'] ?? $_SESSION['id'];
		
		try {
			$count = PostModel::getPostCountByUser($stuNo);
			header('Content-Type: application/json');
			echo json_encode(['success' => true, 'count' => $count]);
		} catch (Exception $e) {
			error_log('ERROR getPostCount: ' . $e->getMessage() . ' for stuNo=' . $stuNo);
			header('Content-Type: application/json');
			echo json_encode(['error' => '获取动态数量失败: ' . $e->getMessage()]);
		}
	}

	// 发布动态 (API)
	public function createPost()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		verifyCsrfToken();
		
		$content = trim($_POST['content'] ?? '');
		$images = $_POST['images'] ?? null;
		$location = $_POST['location'] ?? null;
		
		if (empty($content)) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '内容不能为空']);
			exit;
		}
		
		try {
			$postId = PostModel::createPost(
				$_SESSION['id'],
				$_SESSION['username'],
				$content,
				$images,
				$location
			);
			if ($postId) {
				header('Content-Type: application/json');
				echo json_encode(['success' => true, 'post_id' => $postId]);
			} else {
				header('Content-Type: application/json');
				echo json_encode(['error' => '发布失败']);
			}
		} catch (Exception $e) {
			error_log('ERROR createPost: ' . $e->getMessage() . ' for session_id=' . ($_SESSION['id'] ?? 'null'));
			header('Content-Type: application/json');
			echo json_encode(['error' => '发布失败: ' . $e->getMessage()]);
		}
	}
	
	// 删除动态 (API)
	public function deletePost()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		verifyCsrfToken();
		
		$postId = $_POST['post_id'] ?? 0;
		
		if ($postId <= 0) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误']);
			exit;
		}
		
		try {
			$isAdmin = isManager($_SESSION['permissions'] ?? '');
			$success = PostModel::deletePost($postId, $isAdmin ? null : $_SESSION['id']);
			
			if ($success) {
				header('Content-Type: application/json');
				echo json_encode(['success' => true]);
			} else {
				header('Content-Type: application/json');
				echo json_encode(['error' => '删除失败或无权删除']);
			}
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '删除失败: ' . $e->getMessage()]);
		}
	}
	
	// 获取留言列表 (API)
	public function getGuestbook()
	{
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		$targetStuNo = $_GET['stu_no'] ?? $_SESSION['id'];
		$limit = $_GET['limit'] ?? 20;
		$offset = $_GET['offset'] ?? 0;
		
		try {
			$messages = GuestbookModel::getMessagesByUser($targetStuNo, $limit, $offset);
			header('Content-Type: application/json');
			echo json_encode(['success' => true, 'messages' => $messages]);
		} catch (Exception $e) {
			error_log('ERROR getGuestbook: ' . $e->getMessage() . ' for targetStuNo=' . $targetStuNo);
			header('Content-Type: application/json');
			echo json_encode(['error' => '获取留言失败: ' . $e->getMessage()]);
		}
	}
	
	// 发布留言 (API)
	public function createGuestbookMessage()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		verifyCsrfToken();
		
		$targetStuNo = $_POST['target_stu_no'] ?? 0;
		$message = trim($_POST['message'] ?? '');
		
		if ($targetStuNo == 0) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误: target_stu_no 不能为0']);
			exit;
		}
		
		if (empty($message)) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '留言内容不能为空']);
			exit;
		}
		
		try {
			$messageId = GuestbookModel::createMessage(
				$targetStuNo,
				$_SESSION['id'],
				$_SESSION['username'],
				$message
			);
			
			if ($messageId) {
				header('Content-Type: application/json');
				echo json_encode(['success' => true, 'message_id' => $messageId]);
			} else {
				header('Content-Type: application/json');
				echo json_encode(['error' => '留言失败']);
			}
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '留言失败: ' . $e->getMessage()]);
		}
	}
	
	// 删除留言 (API)
	public function deleteGuestbookMessage()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		verifyCsrfToken();
		
		$messageId = $_POST['message_id'] ?? 0;
		
		if ($messageId <= 0) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误']);
			exit;
		}
		
		try {
			$isAdmin = isManager($_SESSION['permissions'] ?? '');
			$success = GuestbookModel::deleteMessage($messageId, $_SESSION['id'], $isAdmin);
			
			if ($success) {
				header('Content-Type: application/json');
				echo json_encode(['success' => true]);
			} else {
				header('Content-Type: application/json');
				echo json_encode(['error' => '删除失败或无权删除']);
			}
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '删除失败: ' . $e->getMessage()]);
		}
	}
	
	// 获取相册列表 (API)
	public function getAlbums()
	{
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		$stuNo = $_GET['stu_no'] ?? $_SESSION['id'];
		$limit = $_GET['limit'] ?? 10;
		$offset = $_GET['offset'] ?? 0;
		
		try {
			$albums = AlbumModel::getAlbumsByUser($stuNo, $limit, $offset);
			header('Content-Type: application/json');
			echo json_encode(['success' => true, 'albums' => $albums]);
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '获取相册失败: ' . $e->getMessage()]);
		}
	}
	
	// 创建相册 (API)
	public function createAlbum()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		verifyCsrfToken();
		
		$albumName = trim($_POST['album_name'] ?? '');
		if (empty($albumName)) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '相册名称不能为空']);
			exit;
		}
		
		// 检查同名相册
		$existing = AlbumModel::getAlbumsByUser($_SESSION['id'], 100);
		foreach ($existing as $a) {
			if ($a['album_name'] === $albumName) {
				header('Content-Type: application/json');
				echo json_encode(['error' => '已存在同名相册']);
				exit;
			}
		}
		
		try {
			$albumId = AlbumModel::createAlbum($_SESSION['id'], $albumName);
			if ($albumId) {
				header('Content-Type: application/json');
				echo json_encode(['success' => true, 'album_id' => $albumId]);
			} else {
				echo json_encode(['error' => '创建失败']);
			}
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '创建失败: ' . $e->getMessage()]);
		}
	}
	
	// 删除相册 (API)
	public function deleteAlbum()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		verifyCsrfToken();
		
		$albumId = (int)($_POST['album_id'] ?? 0);
		if ($albumId <= 0) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误']);
			exit;
		}
		
		try {
			$album = AlbumModel::getAlbumById($albumId);
			if (!$album) {
				echo json_encode(['error' => '相册不存在']);
				exit;
			}
			
			// 权限检查
			$isOwner = (int)$album['stu_no'] === (int)$_SESSION['id'];
			$isAdmin = isManager($_SESSION['permissions'] ?? '');
			if (!$isOwner && !$isAdmin) {
				echo json_encode(['error' => '无权操作']);
				exit;
			}
			
			// 删除相册内所有照片文件
			$photos = PhotoModel::getPhotosByAlbum($albumId);
			foreach ($photos as $photo) {
				$path = ROOT_DIR . '/public' . $photo['image_url'];
				if (file_exists($path)) unlink($path);
			}
			
			// 删除相册目录
			$albumDir = ROOT_DIR . '/public/static/albums/' . $albumId;
			if (is_dir($albumDir)) {
				array_map('unlink', glob("$albumDir/*.*") ?: []);
				rmdir($albumDir);
			}
			
			$success = AlbumModel::deleteAlbum($albumId);
			if ($success) {
				header('Content-Type: application/json');
				echo json_encode(['success' => true]);
			} else {
				echo json_encode(['error' => '删除失败']);
			}
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '删除失败: ' . $e->getMessage()]);
		}
	}
	
	// 获取最近照片 (API)
	public function getRecentPhotos()
	{
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		$stuNo = $_GET['stu_no'] ?? $_SESSION['id'];
		$limit = $_GET['limit'] ?? 12;
		
		try {
			$photos = PhotoModel::getRecentPhotosByUser($stuNo, $limit);
			header('Content-Type: application/json');
			echo json_encode(['success' => true, 'photos' => $photos]);
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '获取照片失败: ' . $e->getMessage()]);
		}
	}
	
	// 获取相册照片列表 (API)
	public function getAlbumPhotos()
	{
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		$albumId = (int)($_GET['album_id'] ?? 0);
		if ($albumId <= 0) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误']);
			exit;
		}
		
		try {
			$album = AlbumModel::getAlbumById($albumId);
			$photos = PhotoModel::getPhotosByAlbum($albumId);
			header('Content-Type: application/json');
			echo json_encode(['success' => true, 'album' => $album, 'photos' => $photos]);
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '获取照片失败: ' . $e->getMessage()]);
		}
	}
	
	// 上传照片 (API)
	public function uploadPhoto()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		// 手动验证 CSRF（带 exit）
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => 'CSRF 验证失败，请刷新页面后重试']);
			exit;
		}
		
		$albumId = (int)($_POST['album_id'] ?? 0);
		if ($albumId <= 0) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误']);
			exit;
		}
		
		// 权限检查：只有相册主人可以上传
		$album = AlbumModel::getAlbumById($albumId);
		if (!$album || (int)$album['stu_no'] !== (int)$_SESSION['id']) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '无权操作']);
			exit;
		}
		
		if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
			$errCode = isset($_FILES['photo']) ? $_FILES['photo']['error'] : 99;
			$errMap = [1=>'文件过大（超过php.ini限制）',2=>'文件过大（超过表单限制）',3=>'部分上传',4=>'未选择文件',6=>'临时目录不存在',7=>'磁盘写入失败',8=>'PHP扩展阻止上传'];
			$msg = $errMap[$errCode] ?? "上传错误(code:$errCode)";
			header('Content-Type: application/json');
			echo json_encode(['error' => $msg]);
			exit;
		}
		
		$file = $_FILES['photo'];
		$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
		if (!in_array($file['type'], $allowedTypes)) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '仅支持 JPG/PNG/GIF/WebP 格式']);
			exit;
		}
		
		$maxSize = 10 * 1024 * 1024; // 10MB
		if ($file['size'] > $maxSize) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '图片大小不能超过10MB']);
			exit;
		}
		
		try {
			$uploadDir = ROOT_DIR . '/public/static/albums/' . $albumId;
			if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
			
			$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
			$filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
			$destPath = $uploadDir . '/' . $filename;
			
			if (!move_uploaded_file($file['tmp_name'], $destPath)) {
				echo json_encode(['error' => '文件保存失败']);
				exit;
			}
			
			$imageUrl = '/static/albums/' . $albumId . '/' . $filename;

			// 生成缩略图 (300px)
			$thumbUrl = null;
			if (extension_loaded('gd')) {
				$srcImage = null;
				switch ($file['type']) {
					case 'image/jpeg': $srcImage = @imagecreatefromjpeg($destPath); break;
					case 'image/png':  $srcImage = @imagecreatefrompng($destPath); break;
					case 'image/gif':  $srcImage = @imagecreatefromgif($destPath); break;
					case 'image/webp': $srcImage = @imagecreatefromwebp($destPath); break;
				}
				if ($srcImage) {
					$srcW = imagesx($srcImage);
					$srcH = imagesy($srcImage);
					$thumbW = 300;
					$thumbH = (int)($srcH * $thumbW / $srcW);
					$thumb = imagecreatetruecolor($thumbW, $thumbH);
					imagecopyresampled($thumb, $srcImage, 0, 0, 0, 0, $thumbW, $thumbH, $srcW, $srcH);
					$thumbFilename = 'thumb_' . $filename;
					$thumbPath = $uploadDir . '/' . $thumbFilename;
					$ext = strtolower($ext);
					if ($ext === 'png') {
						imagepng($thumb, $thumbPath, 7);
					} elseif ($ext === 'webp') {
						imagewebp($thumb, $thumbPath, 80);
					} else {
						imagejpeg($thumb, $thumbPath, 75);
					}
					imagedestroy($thumb);
					imagedestroy($srcImage);
					$thumbUrl = '/static/albums/' . $albumId . '/' . $thumbFilename;
				}
			}
			
			$photoId = PhotoModel::createPhoto($albumId, $_SESSION['id'], $imageUrl, $thumbUrl);
			
			if ($photoId) {
				// 仅当相册无封面时自动设为封面（用缩略图加速加载）
				if (empty($album['cover_image'])) {
					AlbumModel::setCoverImage($albumId, $thumbUrl ?: $imageUrl);
				}
				// 更新照片计数
				$count = count(PhotoModel::getPhotosByAlbum($albumId));
				AlbumModel::updatePhotoCount($albumId, $count);
				
				header('Content-Type: application/json');
				echo json_encode(['success' => true, 'photo' => [
					'id' => $photoId,
					'image_url' => $imageUrl,
					'thumbnail_url' => $thumbUrl,
					'created_at' => date('Y-m-d H:i:s')
				]]);
			} else {
				echo json_encode(['error' => '数据库写入失败']);
			}
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '上传失败: ' . $e->getMessage()]);
		}
	}
	
	// 删除照片 (API)
	public function deletePhoto()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		verifyCsrfToken();
		
		$photoId = (int)($_POST['photo_id'] ?? 0);
		if ($photoId <= 0) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误']);
			exit;
		}
		
		try {
			$photo = PhotoModel::getPhotoById($photoId);
			if (!$photo) {
				echo json_encode(['error' => '照片不存在']);
				exit;
			}
			
			// 权限检查：照片所属相册的主人或管理员
			$album = AlbumModel::getAlbumById($photo['album_id']);
			$isOwner = $album && (int)$album['stu_no'] === (int)$_SESSION['id'];
			$isAdmin = isManager($_SESSION['permissions'] ?? '');
			if (!$isOwner && !$isAdmin) {
				echo json_encode(['error' => '无权删除']);
				exit;
			}
			
			// 删除文件
			$filePath = ROOT_DIR . '/public' . $photo['image_url'];
			if (file_exists($filePath)) unlink($filePath);
			
			$success = PhotoModel::deletePhoto($photoId);
			if ($success) {
				// 如果删除的是封面，自动更新为新封面
				$album = AlbumModel::getAlbumById($photo['album_id']);
				if ($album && $album['cover_image'] === $photo['image_url']) {
					$firstPhoto = PhotoModel::getFirstPhotoByAlbum($photo['album_id']);
					AlbumModel::setCoverImage($photo['album_id'], $firstPhoto ? $firstPhoto['image_url'] : '');
				}
				// 更新照片计数
				$count = count(PhotoModel::getPhotosByAlbum($photo['album_id']));
				AlbumModel::updatePhotoCount($photo['album_id'], $count);
				
				header('Content-Type: application/json');
				echo json_encode(['success' => true]);
			} else {
				echo json_encode(['error' => '删除失败']);
			}
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '删除失败: ' . $e->getMessage()]);
		}
	}

	// 调整照片排序 (API)
	public function reorderPhotos()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => 'CSRF验证失败']);
			exit;
		}

		$orderedIds = json_decode($_POST['ordered_ids'] ?? '[]', true);
		if (empty($orderedIds) || !is_array($orderedIds)) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误']);
			exit;
		}

		// 权限：检查第一张照片所属相册的主人
		$firstPhoto = PhotoModel::getPhotoById((int)$orderedIds[0]);
		if (!$firstPhoto) {
			echo json_encode(['error' => '照片不存在']);
			exit;
		}
		$album = AlbumModel::getAlbumById($firstPhoto['album_id']);
		if (!$album || (int)$album['stu_no'] !== (int)$_SESSION['id']) {
			echo json_encode(['error' => '无权操作']);
			exit;
		}

		try {
			PhotoModel::batchUpdateOrder($orderedIds);
			header('Content-Type: application/json');
			echo json_encode(['success' => true]);
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '排序失败: ' . $e->getMessage()]);
		}
	}

	// 设置相册封面 (API)
	public function setCoverPhoto()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => 'CSRF验证失败']);
			exit;
		}

		$photoId = (int)($_POST['photo_id'] ?? 0);
		if ($photoId <= 0) {
			echo json_encode(['error' => '参数错误']);
			exit;
		}

		$photo = PhotoModel::getPhotoById($photoId);
		if (!$photo) {
			echo json_encode(['error' => '照片不存在']);
			exit;
		}

		$album = AlbumModel::getAlbumById($photo['album_id']);
		if (!$album || (int)$album['stu_no'] !== (int)$_SESSION['id']) {
			echo json_encode(['error' => '无权操作']);
			exit;
		}

		try {
			// 优先用缩略图作为封面以加速相册列表加载
			$coverUrl = $photo['thumbnail_url'] ?: $photo['image_url'];
			AlbumModel::setCoverImage($photo['album_id'], $coverUrl);
			header('Content-Type: application/json');
			echo json_encode(['success' => true, 'cover_url' => $coverUrl]);
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '设置失败: ' . $e->getMessage()]);
		}
	}
	
	// 点赞动态 (API)
	public function likePost()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		verifyCsrfToken();
		
		$postId = $_POST['post_id'] ?? 0;
		
		if ($postId <= 0) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误: post_id=' . $postId]);
			exit;
		}
		
		try {
			$stuNo = $_SESSION['id'] ?? 0;
			$username = $_SESSION['username'] ?? '';
			$success = PostModel::likePost($postId, $stuNo, $username);
			
			if ($success) {
				header('Content-Type: application/json');
				echo json_encode(['success' => true]);
			} else {
				// 检查是否已点赞
				if ($stuNo > 0 && PostModel::hasLiked($postId, $stuNo)) {
					header('Content-Type: application/json');
					echo json_encode(['error' => '您已经点过赞了']);
				} else {
					header('Content-Type: application/json');
					echo json_encode(['error' => '点赞失败']);
				}
			}
		} catch (Exception $e) {
			error_log('ERROR likePost: ' . $e->getMessage() . ' for post_id=' . $postId . ', stu_no=' . ($_SESSION['id'] ?? 'null'));
			header('Content-Type: application/json');
			echo json_encode(['error' => '点赞失败: ' . $e->getMessage()]);
		}
	}
	
	// 取消点赞动态 (API)
	public function unlikePost()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		verifyCsrfToken();
		
		$postId = $_POST['post_id'] ?? 0;
		
		if ($postId <= 0) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误']);
			exit;
		}
		
		try {
			$stuNo = $_SESSION['id'] ?? 0;
			$success = PostModel::unlikePost($postId, $stuNo);
			
			if ($success) {
				header('Content-Type: application/json');
				echo json_encode(['success' => true]);
			} else {
				header('Content-Type: application/json');
				echo json_encode(['error' => '取消点赞失败']);
			}
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '取消点赞失败: ' . $e->getMessage()]);
		}
	}
	
	// 获取动态评论 (API)
	public function getPostComments()
	{
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		$postId = $_GET['post_id'] ?? 0;
		$limit = $_GET['limit'] ?? 50;
		$offset = $_GET['offset'] ?? 0;
		
		if ($postId <= 0) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误']);
			exit;
		}
		
		try {
			$comments = PostCommentModel::getCommentsByPost($postId, $limit, $offset);
			header('Content-Type: application/json');
			echo json_encode(['success' => true, 'comments' => $comments]);
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '获取评论失败: ' . $e->getMessage()]);
		}
	}
	
	// 发布动态评论 (API)
	public function createPostComment()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		verifyCsrfToken();
		
		$postId = $_POST['post_id'] ?? 0;
		$content = trim($_POST['content'] ?? '');
		
		if ($postId <= 0) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误: post_id=' . $postId]);
			exit;
		}
		
		if (empty($content)) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '评论内容不能为空']);
			exit;
		}
		
		try {
			$commentId = PostCommentModel::createComment(
				$postId,
				$_SESSION['id'],
				$_SESSION['username'],
				$content
			);
			
			// 严格检查：$commentId 必须为数字且大于0
			if ($commentId !== false && $commentId > 0) {
				header('Content-Type: application/json');
				echo json_encode(['success' => true, 'comment_id' => (int)$commentId]);
			} else {
				header('Content-Type: application/json');
				echo json_encode(['error' => '评论失败']);
			}
		} catch (Exception $e) {
			error_log('ERROR createPostComment: ' . $e->getMessage() . ' for post_id=' . $postId);
			header('Content-Type: application/json');
			echo json_encode(['error' => '评论失败: ' . $e->getMessage()]);
		}
	}
	
	// 删除动态评论 (API)
	public function deletePostComment()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Content-Type: application/json');
			echo json_encode(['error' => '请求方法不正确']);
			exit;
		}
		
		if (!isset($_SESSION['username'])) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '未登录']);
			exit;
		}
		
		verifyCsrfToken();
		
		$commentId = $_POST['comment_id'] ?? 0;
		
		if ($commentId <= 0) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '参数错误']);
			exit;
		}
		
		try {
			$isAdmin = isManager($_SESSION['permissions'] ?? '');
			$success = PostCommentModel::deleteComment($commentId, $isAdmin ? null : $_SESSION['id']);
			
			if ($success) {
				header('Content-Type: application/json');
				echo json_encode(['success' => true]);
			} else {
				header('Content-Type: application/json');
				echo json_encode(['error' => '删除失败或无权删除']);
			}
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '删除失败: ' . $e->getMessage()]);
		}
	}
}
