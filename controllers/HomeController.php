<?php
class HomeController
{
	public function index()
	{
		$recentActivities = ActivityModel::getRecentActivities(6);
		require ROOT_DIR . '/views/home/index.php';
	}

	public function users()
	{
		$orderBy = $_GET['sort'] ?? 'stu_no';
		$orderDir = $_GET['dir'] ?? 'ASC';
		$users = UserModel::getAllUsers($orderBy, $orderDir);
		$topScorers = UserModel::getAllUsers('score', 'DESC');
		$filtered = array_filter($topScorers, function ($u) {
			return $u['stu_no'] > 0 && !isManager($u['permissions']) && ($u['score'] ?? 0) > 0;
		});
		$topStuNos = array_slice(array_map(function ($u) {
			return $u['stu_no'];
		}, array_values($filtered)), 0, 3);
		require ROOT_DIR . '/views/home/users.php';
	}
}
