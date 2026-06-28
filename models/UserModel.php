<?php
require_once ROOT_DIR . '/utils/DB.php';

class UserModel
{
	public static function login($username, $password)
	{
		$sql = "SELECT stu_no, name, password, permissions, signature, homepage, avatar, email, stu_class FROM users WHERE name = ? LIMIT 1";
		$stmt = DB::query($sql, [$username]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($user) {
			$valid = password_verify($password, $user['password']);
			if ($valid) {
				unset($user['password']);
				return $user;
			}
		} else {
			password_verify($password, '$2y$10$dummydummydummydummydummydummydummydummydummydummy');
		}
		return false;
	}

	public static function getAllUsers($orderBy = 'stu_no', $orderDir = 'ASC')
	{
		$allowed = ['stu_no', 'score'];
		if (!in_array($orderBy, $allowed)) $orderBy = 'stu_no';
		$orderDir = strtoupper($orderDir);
		if ($orderDir !== 'ASC' && $orderDir !== 'DESC') $orderDir = 'ASC';

		// 管理员 / 测试用户（学号为负数）置顶
		$prefix = "CASE WHEN permissions IN('admin','administrator','teacher') THEN 0 WHEN CAST(stu_no AS SIGNED)<0 THEN 0 ELSE 1 END, ";

		// 强制数值排序
		$field = ($orderBy === 'stu_no') ? 'CAST(stu_no AS SIGNED)' : 'score';
		// 按分数排序时，同分按学号升序
		$tiebreaker = ($orderBy === 'score') ? ', CAST(stu_no AS SIGNED) ASC' : '';

		$sql = "SELECT stu_no, name, email, permissions, stu_class, signature, homepage, avatar, score FROM users ORDER BY $prefix$field $orderDir$tiebreaker";
		return DB::query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function getUserByStuNo($stuNo)
	{
		$stuNo = (int) $stuNo;
		$sql = "SELECT stu_no, name, email, permissions, stu_class, signature, homepage, avatar, score FROM users WHERE stu_no = ?";
		$stmt = DB::query($sql, [$stuNo]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public static function createUser($data)
	{
		$sql = "INSERT INTO users (stu_no, name, email, password, permissions, stu_class, score) VALUES (?, ?, ?, ?, ?, ?, ?)";
		$stmt = DB::query($sql, [$data['stu_no'], $data['name'], $data['email'], $data['password'], $data['permissions'], $data['stu_class'], $data['score'] ?? 0]);
		return $stmt->rowCount() > 0;
	}

	public static function updateUser($stuNo, $data)
	{
		$stuNo = (int) $stuNo;
		$sql = "UPDATE users SET name = ?, email = ?, permissions = ?, stu_class = ?, score = ? WHERE stu_no = ?";
		$stmt = DB::query($sql, [$data['name'], $data['email'], $data['permissions'], $data['stu_class'], $data['score'], $stuNo]);
		return $stmt->rowCount() >= 0;
	}

	public static function deleteUser($stuNo)
	{
		$stuNo = (int) $stuNo;
		$sql = "DELETE FROM users WHERE stu_no = ?";
		$stmt = DB::query($sql, [$stuNo]);
		return $stmt->rowCount() > 0;
	}

	public static function changepwd($stuNo, $password)
	{
		$stuNo = (int) $stuNo;
		$sql = "UPDATE users SET password = ? WHERE stu_no = ?";
		$hashPwd = password_hash($password, PASSWORD_DEFAULT);
		$stmt = DB::query($sql, [$hashPwd, $stuNo]);
		return $stmt->rowCount() >= 0;
	}

	public static function updateAvatar($stuNo, $avatarPath)
	{
		$stuNo = (int) $stuNo;
		$sql = "UPDATE users SET avatar = ? WHERE stu_no = ?";
		$stmt = DB::query($sql, [$avatarPath, $stuNo]);
		return $stmt->rowCount() >= 0;
	}

	public static function getUserByName($username)
	{
		$sql = "SELECT stu_no, name, email, permissions, stu_class, signature, homepage, avatar, score FROM users WHERE name = ?";
		$stmt = DB::query($sql, [$username]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public static function updateProfile($stuNo, $signature, $homepage)
	{
		$stuNo = (int) $stuNo;
		$sql = "UPDATE users SET signature = ?, homepage = ? WHERE stu_no = ?";
		$stmt = DB::query($sql, [$signature, $homepage, $stuNo]);
		return $stmt->rowCount() >= 0;
	}
}
