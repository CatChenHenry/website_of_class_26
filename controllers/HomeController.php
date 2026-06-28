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
		$sort = $_GET['sort'] ?? 'stu_no';
		$dir  = $_GET['dir']  ?? ($sort === 'score' ? 'DESC' : 'ASC');

		$users = UserModel::getAllUsers($sort, $dir);

		// TopScorer：排除管理员/测试用户，取分数最高的前 3 人
		$allByScore = UserModel::getAllUsers('score', 'DESC');
		$topStuNos = [];
		foreach ($allByScore as $u) {
			if (!isManager($u['permissions']) && $u['stu_no'] > 0 && ($u['score'] ?? 0) > 0) {
				$topStuNos[] = $u['stu_no'];
				if (count($topStuNos) >= 3) break;
			}
		}

		require ROOT_DIR . '/views/home/users.php';
	}
}
