<?php
require_once ROOT_DIR . '/models/UserModel.php';

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
}
