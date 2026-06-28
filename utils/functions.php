<?php
function become404page()
{
	http_response_code(404);
	$x404ViewPath = ROOT_DIR . '/views/error/404.php';
	if (file_exists($x404ViewPath)) {
		require $x404ViewPath;
	} else {
		echo '<!DOCTYPE html>
              <html>
              <head><meta charset="UTF-8"><title>404 页面不存在</title></head>
              <body style="text-align:center;margin-top:100px;">
                  <h1>404 Not Found</h1>
                  <p>页面不存在</p>
                  <p>3秒后自动返回首页</p>
                  <script>
                  	setTimeout(() => {
                  		window.location.href = "/home";
                  	}, 3000);
                  </script>
              </body>
              </html>';
	}
	exit;
}

function become403page()
{
	http_response_code(403);
	$x403ViewPath = ROOT_DIR . '/views/error/403.php';
	if (file_exists($x403ViewPath)) {
		require $x403ViewPath;
	} else {
		echo '<!DOCTYPE html>
		      <html>
		      <head><meta charset="UTF-8"><title>403 权限不足</title></head>
		      <body style="text-align: center;margin-top:100px;">
		      		<h1>403 权限不足</h1>
		      		<p>你无权访问此页面</p>
		      		<p>3秒后自动返回首页</p>
		      		<script>
		      			setTimeout(() => {
		      				window.location.href = "/home";
		      			}, 3000);
		      		</script>
		      </body>
		      </html>';
	}
	exit;
}

function become401page_login()
{
	http_response_code(401);
	$x401ViewPath = ROOT_DIR . '/views/error/401_login.php';
	if (file_exists($x401ViewPath)) {
		require $x401ViewPath;
	} else {
		echo '<!DOCTYPE html>
		      <html>
		      <head><meta charset="UTF-8"><title>401 未登录</title></head>
		      <body style="text-align: center;margin-top:100px;">
		      		<h1>401 未登录</h1>
		      		<p>请先登录！</p>
		      		<p>3秒后自动返回登录页面</p>
		      		<script>
		      		setTimeout(() => {
		      			window.location.href = "/user/login";
		      		}, 3000);
		      		</script>
		      </body>
		      </html>';
	}
	exit;
}

function generateCsrfToken()
{
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['csrf_token'];
}

function csrfField()
{
	$token = generateCsrfToken();
	return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

function isAjaxRequest()
{
	return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function isManager(string $permissions): bool
{
	return in_array($permissions, ['admin', 'administrator'], true);
}

function canManageActivity(string $permissions): bool
{
	return in_array($permissions, ['admin', 'administrator', 'teacher'], true);
}

function verifyCsrfToken()
{
	// 检测是否因 post_max_size 超出导致 POST 数据被截断
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
		$contentLen = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
		error_log('POST data empty: CONTENT_LENGTH=' . $contentLen);
		http_response_code(413);
		if (isAjaxRequest()) {
			header('Content-Type: application/json');
			echo json_encode(['error' => '上传文件过大，超过服务器限制']);
		} else {
			echo '<!DOCTYPE html>
			      <html>
			      <head><meta charset="UTF-8"><title>413 文件过大</title></head>
			      <body style="text-align:center;margin-top:100px;">
			          <h1>413 文件过大</h1>
			          <p>上传的文件超过了服务器限制（最大500MB）</p>
			          <p>3秒后自动返回上一页</p>
			          <script>
			              setTimeout(() => { history.back(); }, 3000);
			          </script>
			      </body>
			      </html>';
		}
		exit;
	}

	if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
		error_log('CSRF token validation failed: session token=' . ($_SESSION['csrf_token'] ?? 'none') . ', posted token=' . ($_POST['csrf_token'] ?? 'none') . ', isAjax=' . (isAjaxRequest() ? 'true' : 'false') . ', REQUEST_METHOD=' . ($_SERVER['REQUEST_METHOD'] ?? ''));
		http_response_code(403);
		if (isAjaxRequest()) {
			header('Content-Type: application/json');
			echo json_encode(['error' => 'CSRF Token 无效，请重新提交']);
		} else {
			echo '<!DOCTYPE html>
			      <html>
			      <head><meta charset="UTF-8"><title>403 CSRF验证失败</title></head>
			      <body style="text-align:center;margin-top:100px;">
			          <h1>403 请求验证失败</h1>
			          <p>CSRF Token 无效，请重新提交</p>
			          <p>3秒后自动返回首页</p>
			          <script>
			              setTimeout(() => {
			                  window.location.href = "/home";
			              }, 3000);
			          </script>
			      </body>
			      </html>';
		}
		exit;
	}
	// 验证成功，保持令牌不变以保证会话一致性
	// $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function sanitizeHtml(string $html): string
{
	$allowedTags = '<b><i><u><s><em><strong><small><sub><sup><br><p><span><a><img><ul><ol><li><h1><h2><h3><h4><h5><h6><hr><pre><code><blockquote><table><thead><tbody><tr><th><td><div><del><ins>';
	$clean = strip_tags($html, $allowedTags);
	$clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$clean = strip_tags($clean, $allowedTags);
	$clean = preg_replace('#<(\w+)/(\w)#i', '<$1 $2', $clean);
	$clean = preg_replace('/\s+on\w+\s*=\s*"[^"]*"/i', '', $clean);
	$clean = preg_replace("/\s+on\w+\s*=\s*'[^']*'/i", '', $clean);
	$clean = preg_replace('/\s+on\w+\s*=\s*[^\s>]+/i', '', $clean);
	$clean = preg_replace('/\s+style\s*=\s*"[^"]*"/i', '', $clean);
	$clean = preg_replace("/\s+style\s*=\s*'[^']*'/i", '', $clean);
	$clean = preg_replace('/\s+style\s*=\s*[^\s>]+/i', '', $clean);
	$clean = preg_replace('/href\s*=\s*["\']?\s*(javascript|vbscript|data)\s*:/i', 'href="#"', $clean);
	$clean = preg_replace('/src\s*=\s*["\']?\s*(javascript|vbscript|data)\s*:/i', 'src="#"', $clean);
	$clean = preg_replace('/action\s*=\s*["\']?\s*(javascript|vbscript|data)\s*:/i', 'action="#"', $clean);
	$allowedAttrs = ['href', 'src', 'alt', 'title', 'width', 'height', 'colspan', 'rowspan', 'class', 'target'];
	$clean = preg_replace_callback(
		'/<(\w+)((?:\s+[^>]*)?)>/i',
		function ($matches) use ($allowedAttrs) {
			$tag = $matches[1];
			$attrStr = $matches[2];
			if (trim($attrStr) === '') {
				return '<' . $tag . '>';
			}
			preg_match_all('/(\w+)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', $attrStr, $attrMatches, PREG_SET_ORDER);
			$kept = [];
			foreach ($attrMatches as $attr) {
				$attrName = strtolower($attr[1]);
				if (in_array($attrName, $allowedAttrs)) {
					$val = html_entity_decode(trim($attr[2], '"\''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
					if (preg_match('/^\s*(javascript|vbscript|data)\s*:/i', $val)) {
						continue;
					}
					$kept[] = $attr[0];
				}
			}
			$keptStr = implode(' ', $kept);
			return '<' . $tag . ($keptStr ? ' ' . $keptStr : '') . '>';
		},
		$clean
	);
	return $clean;
}

function checkLoginAttempts(string $username, string $ip): bool
{
	$maxAttempts = 5;
	$lockoutTime = 300;
	$attemptFile = ROOT_DIR . '/tmp/login_attempts';
	$attempts = [];
	$fp = fopen($attemptFile, 'c+');
	if (!flock($fp, LOCK_EX)) {
		fclose($fp);
		return true;
	}
	$size = filesize($attemptFile);
	$data = $size > 0 ? fread($fp, $size) : '';
	$attempts = json_decode($data, true) ?: [];
	$now = time();
	foreach ($attempts as $key => $record) {
		if ($now - $record['time'] > $lockoutTime) {
			unset($attempts[$key]);
		}
	}
	$userFailCount = 0;
	$ipFailCount = 0;
	foreach ($attempts as $record) {
		if (!$record['success']) {
			if ($record['username'] === $username) $userFailCount++;
			if ($record['ip'] === $ip) $ipFailCount++;
		}
	}
	ftruncate($fp, 0);
	rewind($fp);
	fwrite($fp, json_encode(array_values($attempts)));
	flock($fp, LOCK_UN);
	fclose($fp);
	return $userFailCount < $maxAttempts && $ipFailCount < $maxAttempts;
}

function recordLoginAttempt(string $username, string $ip, bool $success): void
{
	$attemptFile = ROOT_DIR . '/tmp/login_attempts';
	$fp = fopen($attemptFile, 'c+');
	if (!flock($fp, LOCK_EX)) {
		fclose($fp);
		return;
	}
	$size = filesize($attemptFile);
	$data = $size > 0 ? fread($fp, $size) : '';
	$attempts = json_decode($data, true) ?: [];
	$attempts[] = ['username' => $username, 'ip' => $ip, 'time' => time(), 'success' => $success];
	if (count($attempts) > 100) {
		$attempts = array_slice($attempts, -100);
	}
	ftruncate($fp, 0);
	rewind($fp);
	fwrite($fp, json_encode($attempts));
	flock($fp, LOCK_UN);
	fclose($fp);
}

function checkChangepwdAttempts(int $stuNo): bool
{
	$maxAttempts = 5;
	$lockoutTime = 300;
	$attemptFile = ROOT_DIR . '/tmp/changepwd_attempts';
	$attempts = [];
	$fp = fopen($attemptFile, 'c+');
	if (!flock($fp, LOCK_EX)) {
		fclose($fp);
		return true;
	}
	$size = filesize($attemptFile);
	$data = $size > 0 ? fread($fp, $size) : '';
	$attempts = json_decode($data, true) ?: [];
	$now = time();
	foreach ($attempts as $key => $record) {
		if ($now - $record['time'] > $lockoutTime) {
			unset($attempts[$key]);
		}
	}
	$failCount = 0;
	foreach ($attempts as $record) {
		if ($record['stu_no'] === $stuNo && !$record['success']) {
			$failCount++;
		}
	}
	ftruncate($fp, 0);
	rewind($fp);
	fwrite($fp, json_encode(array_values($attempts)));
	flock($fp, LOCK_UN);
	fclose($fp);
	return $failCount < $maxAttempts;
}

function recordChangepwdAttempt(int $stuNo, bool $success): void
{
	$attemptFile = ROOT_DIR . '/tmp/changepwd_attempts';
	$fp = fopen($attemptFile, 'c+');
	if (!flock($fp, LOCK_EX)) {
		fclose($fp);
		return;
	}
	$size = filesize($attemptFile);
	$data = $size > 0 ? fread($fp, $size) : '';
	$attempts = json_decode($data, true) ?: [];
	$attempts[] = ['stu_no' => $stuNo, 'time' => time(), 'success' => $success];
	if (count($attempts) > 100) {
		$attempts = array_slice($attempts, -100);
	}
	ftruncate($fp, 0);
	rewind($fp);
	fwrite($fp, json_encode($attempts));
	flock($fp, LOCK_UN);
	fclose($fp);
}

function showMessage(string $type, string $message, string $redirect, int $delay = 2000): void
{
	$escapedMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
	$escapedRedirect = htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8');
	$delayMs = (int) $delay;
	echo <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body>
	<link rel="stylesheet" href="/static/css/message.css">
	<script src="/static/js/message.js"></script>
	<script>
		showToast("{$escapedMessage}", "{$type}");
		setTimeout(function() {
			window.location.href = "{$escapedRedirect}";
		}, {$delayMs});
	</script>
</body>
</html>
HTML;
	exit;
}
