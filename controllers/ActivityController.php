<?php
class ActivityController
{
	public function index()
	{
		$activities = ActivityModel::getAllActivities();
		require ROOT_DIR . '/views/activity/index.php';
	}

	public function show()
	{
		$id = (int) ($_GET['id'] ?? 0);
		if (empty($id)) {
			become404page();
		}
		$activity = ActivityModel::getActivityById($id);
		if (!$activity) {
			become404page();
		}
		$comments = CommentModel::getCommentsByActivityId($id);
		require ROOT_DIR . '/views/activity/show.php';
	}

	public function create()
	{
		if (!isset($_SESSION['username']) || !canManageActivity($_SESSION['permissions'])) {
			become403page();
		}
		require ROOT_DIR . '/views/activity/create.php';
	}

	public function store()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			become404page();
		}
		if (!isset($_SESSION['username']) || !canManageActivity($_SESSION['permissions'])) {
			become403page();
		}
		verifyCsrfToken();
		$name = sanitizeHtml(trim($_POST['name'] ?? ''));
		$activityTime = trim($_POST['activity_time'] ?? '');
		$content = trim($_POST['content'] ?? '');
		if (empty($name) || empty($activityTime)) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '活动名称和时间不能为空！'];
			header("Location: /activity/create");
			exit;
		}
		if (!strtotime($activityTime)) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '活动时间格式不正确！'];
			header("Location: /activity/create");
			exit;
		}
		$data = [
			'name' => $name,
			'activity_time' => $activityTime,
			'content' => $content
		];
		$result = ActivityModel::createActivity($data);
		if ($result) {
			$_SESSION['flash_message'] = ['type' => 'success', 'text' => '活动创建成功！'];
			header("Location: /activity/index");
			exit;
		} else {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '活动创建失败！'];
			header("Location: /activity/create");
			exit;
		}
	}

	public function edit()
	{
		if (!isset($_SESSION['username']) || !canManageActivity($_SESSION['permissions'])) {
			become403page();
		}
		$id = (int) ($_GET['id'] ?? 0);
		if (empty($id)) {
			become404page();
		}
		$activity = ActivityModel::getActivityById($id);
		if (!$activity) {
			become404page();
		}
		require ROOT_DIR . '/views/activity/edit.php';
	}

	public function update()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			become404page();
		}
		if (!isset($_SESSION['username']) || !canManageActivity($_SESSION['permissions'])) {
			become403page();
		}
		verifyCsrfToken();
		$id = (int) ($_POST['id'] ?? 0);
		if (empty($id)) {
			become404page();
		}
		$activity = ActivityModel::getActivityById($id);
		if (!$activity) {
			become404page();
		}
		$name = sanitizeHtml(trim($_POST['name'] ?? ''));
		$activityTime = trim($_POST['activity_time'] ?? '');
		$content = trim($_POST['content'] ?? '');
		if (empty($name) || empty($activityTime)) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '活动名称和时间不能为空！'];
			header("Location: /activity/edit?id=" . $id);
			exit;
		}
		if (!strtotime($activityTime)) {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '活动时间格式不正确！'];
			header("Location: /activity/edit?id=" . $id);
			exit;
		}
		$data = [
			'name' => $name,
			'activity_time' => $activityTime,
			'content' => $content
		];
		$result = ActivityModel::updateActivity($id, $data);
		if ($result) {
			$_SESSION['flash_message'] = ['type' => 'success', 'text' => '活动修改成功！'];
			header("Location: /activity/show?id=" . $id);
			exit;
		} else {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '活动修改失败！'];
			header("Location: /activity/edit?id=" . $id);
			exit;
		}
	}

	public function delete()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			become404page();
		}
		if (!isset($_SESSION['username']) || !canManageActivity($_SESSION['permissions'])) {
			become403page();
		}
		verifyCsrfToken();
		$id = (int) ($_POST['id'] ?? 0);
		if (empty($id)) {
			become404page();
		}
		$result = ActivityModel::deleteActivity($id);
		if ($result) {
			CommentModel::deleteCommentsByActivityId($id);
			$_SESSION['flash_message'] = ['type' => 'success', 'text' => '活动删除成功！'];
			header("Location: /activity/index");
			exit;
		} else {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '活动删除失败！'];
			header("Location: /activity/index");
			exit;
		}
	}

	public function addComment()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			become404page();
		}
		if (!isset($_SESSION['username'])) {
			http_response_code(401);
			echo json_encode(['success' => false, 'message' => '请先登录']);
			exit;
		}
		if (!isAjaxRequest()) {
			verifyCsrfToken();
		} else {
			if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
				http_response_code(403);
				echo json_encode(['success' => false, 'message' => 'CSRF 验证失败']);
				exit;
			}
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		}

		$activityId = (int) ($_POST['activity_id'] ?? 0);
		$content = trim($_POST['content'] ?? '');

		if (empty($activityId) || empty($content)) {
			if (isAjaxRequest()) {
				header('Content-Type: application/json');
				echo json_encode(['success' => false, 'message' => '评论内容不能为空']);
				exit;
			}
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '评论内容不能为空！'];
			header("Location: /activity/show?id=" . $activityId);
			exit;
		}

		$activity = ActivityModel::getActivityById($activityId);
		if (!$activity) {
			become404page();
		}

		$avatar = '/static/avatars/default.jpg';
		$currentUser = UserModel::getUserByName($_SESSION['username']);
		if (!empty($currentUser['avatar'])) {
			$avatar = $currentUser['avatar'];
		}

		$data = [
			'activity_id' => $activityId,
			'stu_no' => $_SESSION['id'],
			'username' => $_SESSION['username'],
			'avatar' => $avatar,
			'content' => $content
		];

		$result = CommentModel::createComment($data);

		if (isAjaxRequest()) {
			header('Content-Type: application/json');
			if ($result) {
				$commentId = DB::query("SELECT LAST_INSERT_ID()")->fetchColumn();
				echo json_encode([
					'success' => true,
					'csrf_token' => $_SESSION['csrf_token'] ?? '',
					'comment' => [
						'id' => (int) $commentId,
						'username' => $_SESSION['username'],
						'avatar' => $avatar,
						'content' => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
						'created_at' => date('Y-m-d H:i:s'),
						'stu_no' => $_SESSION['id'],
						'can_delete' => true
					]
				]);
			} else {
				echo json_encode(['success' => false, 'message' => '评论失败', 'csrf_token' => $_SESSION['csrf_token'] ?? '']);
			}
			exit;
		}

		if ($result) {
			$_SESSION['flash_message'] = ['type' => 'success', 'text' => '评论成功！'];
		} else {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '评论失败！'];
		}
		header("Location: /activity/show?id=" . $activityId);
		exit;
	}

	public function deleteComment()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			become404page();
		}
		if (!isset($_SESSION['username'])) {
			http_response_code(401);
			echo json_encode(['success' => false, 'message' => '请先登录']);
			exit;
		}
		if (!isAjaxRequest()) {
			verifyCsrfToken();
		} else {
			if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
				http_response_code(403);
				echo json_encode(['success' => false, 'message' => 'CSRF 验证失败']);
				exit;
			}
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		}

		$commentId = (int) ($_POST['comment_id'] ?? 0);
		if (empty($commentId)) {
			become404page();
		}

		$comment = CommentModel::getCommentById($commentId);
		if (!$comment) {
			become404page();
		}

		$isAdmin = canManageActivity($_SESSION['permissions']);
		$isOwner = (int)$comment['stu_no'] === (int)$_SESSION['id'];

		if (!$isAdmin && !$isOwner) {
			become403page();
		}

		$result = CommentModel::deleteComment($commentId);

		if (isAjaxRequest()) {
			header('Content-Type: application/json');
			echo json_encode(['success' => $result, 'csrf_token' => $_SESSION['csrf_token'] ?? '']);
			exit;
		}

		if ($result) {
			$_SESSION['flash_message'] = ['type' => 'success', 'text' => '评论已删除'];
		} else {
			$_SESSION['flash_message'] = ['type' => 'error', 'text' => '删除评论失败'];
		}
		header("Location: /activity/show?id=" . $comment['activity_id']);
		exit;
	}
}
